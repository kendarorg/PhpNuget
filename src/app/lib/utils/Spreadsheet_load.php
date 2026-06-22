<?php

class Spreadsheet
{
    /* =========================
     * STORAGE
     * ========================= */
    public $onCellCallback=null;
    private $evalGuard = 10;
    /**
     * Raw (non-formula) cells store scalar values.
     * Formula cells store ['ast' => <AST>] so they are never re-parsed.
     */
    public array $cells = [];

    private array $cellStyles = [];
    private array $rowStyles  = [];

    private array $dependencies = [];
    private array $dependents   = [];

    /**
     * Maps label name → Excel column letter, e.g. ['price' => 'B', 'name' => 'A'].
     * Populated by import() from the $headers array.
     */
    public array $labelToCol = [];

    /* Parser state – only used during setCell / shiftFormula */
    private array $tokens = [];
    private int   $pos    = 0;
    private ?string $lastToken = null;

    /* =========================
     * PUBLIC API
     * ========================= */

    /* =========================
     * LABEL RESOLUTION
     * ========================= */

    /**
     * Resolve any label-based coordinate to a pure Excel coordinate.
     *
     * Accepted input forms:
     *   `price`3   →  B3        (backtick-label + row number = cell ref)
     *   `price`    →  B         (backtick-label alone        = column letter)
     *   .price3    →  B3        (dot-label + row number      = cell ref)
     *   .price     →  B         (dot-label alone             = column letter)
     *   B3         →  B3        (plain Excel coord, unchanged)
     *   B          →  B         (plain column letter, unchanged)
     */
    private function resolveCoord(string $ref): string
    {
        // Backtick form:  `labelName`<optional digits>
        if (preg_match('/^`([^`]+)`(\d*)$/', $ref, $m)) {
            $col = $this->labelToCol[$m[1]]
                ?? throw new \InvalidArgumentException("Unknown column label: '{$m[1]}'");
            return $m[2] !== '' ? $col . $m[2] : $col;
        }

        // Dot form:  .labelName<optional digits>
        // Label chars are letters/underscore only; digits that follow are the row number.
        if (preg_match('/^\.([A-Za-z_][A-Za-z_]*)(\d*)$/', $ref, $m)) {
            $col = $this->labelToCol[$m[1]]
                ?? throw new \InvalidArgumentException("Unknown column label: '{$m[1]}'");
            return $m[2] !== '' ? $col . $m[2] : $col;
        }

        // Already a plain Excel coord / column letter — return as-is
        return $ref;
    }

    /**
     * Resolve a key that names a column (no row number).
     * Accepts `label`, .label, or a plain column letter like 'B'.
     */
    private function resolveColKey(string $key): string
    {
        if(isset($this->labelToCol[$key])){
            return $this->labelToCol[$key];
        }
        return $this->resolveCoord($key);   // row part will be absent → returns col letter
    }

    public function setCell(string $coord, $value): void
    {
        $coord = $this->resolveCoord($coord);
        [$c, $r] = $this->splitCoord($coord);
        if($this->onCellCallback){
            $value = ($this->onCellCallback)($r,$c,$value);
        }

        if (is_string($value) && str_starts_with($value, '<f>')) {
            // Parse once, store as AST
            $ast = $this->parseFormula($this->extractFormula($value));

            $this->cells[$coord] = ['ast' => $ast];

            // Rebuild dependency graph for this cell
            $deps = $this->collectDeps($ast);
            $this->dependencies[$coord] = $deps;
            foreach ($deps as $d) {
                $this->dependents[$d][] = $coord;
                $this->dependents[$d]   = array_unique($this->dependents[$d]);
            }
        } else {
            if (is_string($value)) {
                // Strip and apply any inline <style …> wrapper
                if (preg_match('/^<style ([^>]*)>(.*)<\/style>$/s', $value, $m)) {

                    $row = $this->getRow($coord);
                    $styles = array_merge(
                        $this->rowStyles[$row]??[] ,$this->parseStyleString($m[1])
                    );

                    $this->cellStyles[$coord] = $styles;
                    $value = $m[2];

                    if (is_string($value) && str_starts_with($value, '<f>')) {
                        // Parse once, store as AST
                        $ast = $this->parseFormula($this->extractFormula($value));

                        $this->cells[$coord] = ['ast' => $ast];

                        // Rebuild dependency graph for this cell
                        $deps = $this->collectDeps($ast);
                        $this->dependencies[$coord] = $deps;
                        foreach ($deps as $d) {
                            $this->dependents[$d][] = $coord;
                            $this->dependents[$d]   = array_unique($this->dependents[$d]);
                        }
                    }else {
                        $this->cells[$coord] = $value;
                    }
                    return;
                }
            }

            $this->cells[$coord] = $value;
            $this->extractStyle($coord, $value);
        }
    }

    public function getCell(string $coord)
    {
        $coord = $this->resolveCoord($coord);
        $value = $this->cells[$coord] ?? null;

        if (is_array($value) && isset($value['ast'])) {
            return $this->eval($value['ast']);
        }

        return $this->stripTags($value);
    }

    public function getStyle(string $coord): array
    {
        $coord = $this->resolveCoord($coord);
        return $this->cellStyles[$coord]
            ?? $this->rowStyles[$this->getRow($coord)]
            ?? [];
    }

    public function setRowStyle(string $coord, $style): void
    {
        if(!is_array($style)) {
            $style = [$style];
        }
        $coord = $this->resolveCoord($coord);
        $this->rowStyles[$this->getRow($coord)] = array_merge(
            $this->rowStyles[$this->getRow($coord)] ?? [],
            $style
        );
    }

    /**
     * Set style properties for a single cell directly,
     * without embedding tags in the cell value.
     *
     * Example:
     *   $sheet->setCellStyle('B2', ['bold' => true, 'color' => '#ff0000']);
     */
    public function setCellStyle(string $coord, $style,$force=false): void
    {
        if(!is_array($style)) {
            $style = [$style];
        }
        $coord = $this->resolveCoord($coord);
        $this->cellStyles[$coord] = array_merge(
            $this->cellStyles[$coord] ?? [],
            $style
        );
        if($force){
            $this->cellStyles[$coord] =$style;
        }
    }


    /* =========================
     * ROW INSERTION
     * ========================= */

    /**
     * Insert $count blank rows after $afterRow, then optionally populate them.
     *
     * $data is a list of rows; each row is a map of column-letter => value.
     * The first entry in $data fills row ($afterRow + 1), the second fills
     * ($afterRow + 2), and so on.
     *
     * Example – insert 2 rows after row 3, pre-filled:
     *
     *   $sheet->addRows(3, 2, [
     *       ['A' => 'Alice', 'B' => 42,        'C' => '<f>A4+B4</f>'],
     *       ['A' => 'Bob',   'B' => '<f>B4*2</f>'],
     *   ]);
     */
    public function addRows(?int $afterRow, int $count = 1, array $data = [])
    {
        // null -> append after the last occupied row
        if ($afterRow === null) {
            $afterRow = $this->lastRow();
        }

        // Columns present in the reference row (immediately above insertion point).
        // Used to fill any column not explicitly supplied in new rows with null.
        $referenceColumns = $this->columnsInRow($afterRow);

        $newCells     = [];
        $newStyles    = [];
        $newRowStyles = [];

        // ---- Shift existing cells / styles that live below the insertion point ----
        foreach ($this->cells as $coord => $stored) {
            [$col, $row] = $this->splitCoord($coord);

            if ($row > $afterRow) {
                $row += $count;
            }

            $newCoord = $col . $row;

            // Shift cell references inside stored ASTs
            if (is_array($stored) && isset($stored['ast'])) {
                $newCells[$newCoord] = ['ast' => $this->shiftAst($stored['ast'], $afterRow, $count)];
            } else {
                $newCells[$newCoord] = $this->shiftFormula($stored, $afterRow, $count);
            }

            if (isset($this->cellStyles[$coord])) {
                $newStyles[$newCoord] = $this->cellStyles[$coord];
            }
        }

        foreach ($this->rowStyles as $row => $style) {
            $newRowStyles[$row > $afterRow ? $row + $count : $row] = $style;
        }

        $this->cells      = $newCells;
        $this->cellStyles = $newStyles;
        $this->rowStyles  = $newRowStyles;

        $addedRowsLevels=[];

        // ---- Populate the freshly inserted rows ----
        foreach ($data as $i => $rowData) {
            $targetRow = $afterRow + 1 + $i;

            // Resolve any label / dot keys to plain column letters
            $resolved = [];
            foreach ($rowData as $col => $value) {
                $resolved[$this->resolveColKey((string)$col)] = $value;
            }

            // Fill every reference-row column not explicitly supplied with null
            foreach ($referenceColumns as $col) {
                if (!array_key_exists($col, $resolved)) {
                    $resolved[$col] = null;
                }
            }

            foreach ($resolved as $col => $value) {
                $this->setCell(strtoupper($col) . $targetRow, $value);
            }
        }

        $this->rebuildDependencies();
        return $afterRow;
    }

    /**
     * Return the highest row number currently occupied, or 0 if empty.
     */
    public function lastRow(): int
    {
        $max = 0;
        foreach (array_keys($this->cells) as $coord) {
            [, $row] = $this->splitCoord($coord);
            if ($row > $max) {
                $max = $row;
            }
        }
        return $max;
    }

    /**
     * Return the column letters that have a cell in $row.
     */
    private function columnsInRow(int $row): array
    {
        $cols = [];
        foreach (array_keys($this->cells) as $coord) {
            [$col, $r] = $this->splitCoord($coord);
            if ($r === $row) {
                $cols[] = $col;
            }
        }
        return array_unique($cols);
    }

    /* =========================
     * FORMULA PARSER (AST)
     * ========================= */

    private function parseFormula(string $expr)
    {
        $this->tokens    = $this->tokenize($expr);
        $this->pos       = 0;
        $this->lastToken = null;

        return $this->parseConcat();
    }

    private function tokenize(string $expr): array
    {
        preg_match_all(
            '/`[^`]+`\d*|\d+\.?\d*|[A-Z]+[0-9]+|[A-Z]+|\<=|\>=|<>|=|<|>|\+|\-|\*|\/|\%|\&|\(|\)|\,|:/',
            $expr,
            $m
        );

        return $m[0];
    }

    /* =========================
     * PRECEDENCE PARSER
     * ========================= */

    private function parseConcat()
    {
        $node = $this->parseCompare();

        while ($this->match('&')) {
            $node = [
                'type'  => 'concat',
                'left'  => $node,
                'right' => $this->parseCompare(),
            ];
        }

        return $node;
    }

    private function parseCompare()
    {
        $node = $this->parseAddSub();

        while ($this->isCompare($this->peek())) {
            $op   = $this->consume();
            $node = [
                'type'  => 'compare',
                'op'    => $op,
                'left'  => $node,
                'right' => $this->parseAddSub(),
            ];
        }

        return $node;
    }

    private function parseAddSub()
    {
        $node = $this->parseUnary();

        while ($this->peek() === '+' || $this->peek() === '-') {
            $op   = $this->consume();
            $node = [
                'type'  => 'binary',
                'op'    => $op,
                'left'  => $node,
                'right' => $this->parseUnary(),
            ];
        }

        return $node;
    }

    private function parseMulDiv()
    {
        $node = $this->parsePrimary();

        while ($this->peek() === '*' || $this->peek() === '/' || $this->peek() === '%') {
            $op   = $this->consume();
            $node = [
                'type'  => 'binary',
                'op'    => $op,
                'left'  => $node,
                'right' => $this->parseUnary(),
            ];
        }

        return $node;
    }

    private function expandRange(string $from, string $to): array
    {
        [$colFrom, $rowFrom] = $this->splitCoord($from);
        [$colTo,   $rowTo  ] = $this->splitCoord($to);

        $c1 = $this->colToIndex($colFrom);
        $c2 = $this->colToIndex($colTo);
        if ($c1 > $c2) [$c1, $c2] = [$c2, $c1];
        if ($rowFrom > $rowTo) [$rowFrom, $rowTo] = [$rowTo, $rowFrom];

        $cells = [];
        for ($c = $c1; $c <= $c2; $c++) {
            for ($r = $rowFrom; $r <= $rowTo; $r++) {
                $cells[] = $this->indexToCol($c) . $r;
            }
        }
        return $cells;
    }

    private function parsePrimary()
    {
        $t = $this->peek();

        if ($this->match('(')) {
            $node = $this->parseConcat();
            $this->consume(')');
            return $node;
        }

        // Backtick label ref: `price`3  or  `price`  (column-only, rare in formulas)
        if ($t !== null && str_starts_with($t, '`')) {
            $token    = $this->consume();
            $resolved = $this->resolveCoord($token);   // → e.g. B3 or B
            // If it resolved to a full cell coord, emit a cell node
            if (preg_match('/^[A-Z]+[0-9]+$/', $resolved)) {
                return ['type' => 'cell', 'ref' => $resolved];
            }
            // Column-only label used bare in a formula — treat as raw string
            return ['type' => 'raw', 'value' => $resolved];
        }

        if (is_numeric($t)) {
            return ['type' => 'num', 'value' => $this->consume() + 0];
        }

        /*if ($this->isCell($t)) {
            return ['type' => 'cell', 'ref' => $this->consume()];
        }*/

        if ($this->isCell($t)) {
            $ref  = $this->consume();
            if ($this->peek() === ':') {          // range  C1:C5
                $this->consume();                 // eat ':'
                $ref2 = $this->consume();         // end ref
                return ['type' => 'range', 'from' => $ref, 'to' => $ref2];
            }
            return ['type' => 'cell', 'ref' => $ref];
        }

        if ($this->isFunction($t)) {
            return $this->parseFunction();
        }

        return ['type' => 'raw', 'value' => $this->consume()];
    }

    private function parseFunction()
    {
        $name = strtoupper($this->consume());
        $this->consume('(');

        $args = [];

        if ($this->peek() !== ')') {
            do {
                $args[] = $this->parseConcat();
            } while ($this->match(','));
        }

        $this->consume(')');

        return [
            'type' => 'func',
            'name' => $name,
            'args' => $args,
        ];
    }

    /* =========================
     * EVALUATION
     * ========================= */


    private function eval($n)
    {
        if($this->evalGuard>0){
            $this->evalGuard--;
        }else{
            throw new Exception("Recursion Limit Reached");
        }
        $result = match ($n['type']) {
            'num'     => $n['value'],
            'raw'     => $n['value'],
            'cell'    => $this->getCell($n['ref']) ?? 0,
            'concat'  => $this->eval($n['left']) . $this->eval($n['right']),
            'binary'  => $this->evalBinary($n),
            'compare' => $this->evalCompare($n),
            'func'    => $this->evalFunc($n),
            'range' => array_map(
                fn($ref) => $this->getCell($ref) ?? 0,
                $this->expandRange($n['from'], $n['to'])
            ),
            'unary' => $n['op'] === '-' ? -($this->eval($n['operand'])) : $this->eval($n['operand']),
            default   => null,
        };
        $this->evalGuard++;

        return $result;
    }

    private function evalBinary($n)
    {
        $a = $this->eval($n['left']);
        $b = $this->eval($n['right']);

        return match ($n['op']) {
            '+' => $a + $b,
            '-' => $a - $b,
            '*' => $a * $b,
            '/' => $b != 0 ? $a / $b : null,
            '%' => $b != 0 ? $a % $b : null,
        };
    }

    private function evalCompare($n)
    {
        $a = $this->eval($n['left']);
        $b = $this->eval($n['right']);

        return match ($n['op']) {
            '='  => $a == $b,
            '<>' => $a != $b,
            '>'  => $a > $b,
            '<'  => $a < $b,
            '>=' => $a >= $b,
            '<=' => $a <= $b,
        };
    }

    private function parseUnary()
    {
        if ($this->peek() === '-') {
            $this->consume();
            return ['type' => 'unary', 'op' => '-', 'operand' => $this->parseMulDiv()];
        }
        if ($this->peek() === '+') { $this->consume(); }
        return $this->parseMulDiv();
    }

    private function custom_array_sum(array $arr): float|int
    {
        $sum = 0;

        foreach ($arr as $value) {
            // optionally ensure numeric values only
            if (is_numeric($value)) {
                $sum += $value;
            }
        }

        return $sum;
    }
    private function evalFunc($n)
    {
        // Evaluate each arg; range nodes produce arrays — flatten everything
        $vals = [];
        foreach ($n['args'] as $arg) {
            $v = $this->eval($arg);
            if (is_array($v)) {
                array_push($vals, ...$v);   // flatten range
            } else {
                $vals[] = $v;
            }
        }

        return match ($n['name']) {
            'SUM'     => $this->custom_array_sum($vals),
            'MIN'     => min($vals),
            'MAX'     => max($vals),
            'AVERAGE' => count($vals) ? $this->custom_array_sum($vals) / count($vals) : 0,
            'COUNT'   => count($vals),

            'ROUND'   => isset($vals[1]) ? round($vals[0], (int)$vals[1]) : round($vals[0]),
            'POWER'   => pow($vals[0] ?? 0, $vals[1] ?? 1),

            'CEILING' => ceil($vals[0] ?? 0),
            'FLOOR'   => floor($vals[0] ?? 0),

            'DIV'     => $vals[1] != 0 ? $vals[0] / $vals[1]              : null,
            'QUOTIENT'    => $vals[1] != 0 ? intdiv((int)$vals[0], (int)$vals[1]) : null,
            'MOD'     => $vals[1] != 0 ? $vals[0] % $vals[1]              : null,

            'IF'      => $vals[0] ? $vals[1] : ($vals[2] ?? null),

            default   => null,
        };
    }

    /* =========================
     * DEPENDENCY SYSTEM
     * ========================= */

    private function collectDeps($node): array
    {
        $deps = [];

        if ($node['type'] === 'range') {
            foreach ($this->expandRange($node['from'], $node['to']) as $ref) {
                $deps[] = $ref;
            }
        }
        if ($node['type'] === 'cell') {
            $deps[] = $node['ref'];
        }

        foreach ($node as $v) {
            if (is_array($v)&& isset($v['type'])) {
                $deps = array_merge($deps, $this->collectDeps($v));
            }
        }

        return array_unique($deps);
    }

    /**
     * Rebuild the full dependency / dependents maps from the stored ASTs.
     * Called after every structural change (addRows, etc.).
     */
    private function rebuildDependencies(): void
    {
        $this->dependencies = [];
        $this->dependents   = [];

        foreach ($this->cells as $coord => $stored) {
            if (!is_array($stored) || !isset($stored['ast'])) {
                continue;
            }

            $deps = $this->collectDeps($stored['ast']);
            $this->dependencies[$coord] = $deps;

            foreach ($deps as $d) {
                $this->dependents[$d][] = $coord;
                $this->dependents[$d]   = array_unique($this->dependents[$d]);
            }
        }
    }

    /* =========================
     * AST REFERENCE SHIFTING
     * (used instead of regex on serialised strings)
     * ========================= */

    /**
     * Walk an AST and increment every row reference that sits below $afterRow
     * by $offset. Returns a new AST (arrays are value types in PHP so this is
     * already a copy).
     */
    private function shiftAst(array $node, int $afterRow, int $offset): array
    {
        if ($node['type'] === 'range') {
            foreach (['from', 'to'] as $key) {
                [$col, $row] = $this->splitCoord($node[$key]);
                if ($row > $afterRow) {
                    $node[$key] = $col . ($row + $offset);
                }
            }
            return $node;
        }
        if ($node['type'] === 'cell') {
            [$col, $row] = $this->splitCoord($node['ref']);

            if ($row > $afterRow) {
                $node['ref'] = $col . ($row + $offset);
            }

            return $node;
        }

        foreach ($node as $key => $child) {
            if (is_array($child)&& isset($child['type'])) {
                $node[$key] = $this->shiftAst($child, $afterRow, $offset);
            }
        }
        return $node;
    }

    /* =========================
     * LEGACY STRING SHIFTING
     * (kept for plain-string formula values that bypass setCell)
     * ========================= */

    private function shiftFormula($value, int $afterRow, int $offset)
    {
        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback('/([A-Z]+)([0-9]+)/', function ($m) use ($afterRow, $offset) {
            $col = $m[1];
            $row = (int)$m[2];

            return $col . ($row > $afterRow ? $row + $offset : $row);
        }, $value);
    }

    /* =========================
     * STYLE EXTRACTION (tag-based, legacy)
     * ========================= */

    private function extractStyle(string $coord, $value): void
    {
        if (!is_string($value)) {
            return;
        }

        // <s> tag signals a style marker – extend this as needed
        if (str_contains($value, '<s>')) {
            $row = $this->getRow($coord);
            $this->rowStyles[$row] = array_merge(
                $this->rowStyles[$row] ?? [],
                ['tagged' => true]
            );
        }
    }

    /* =========================
     * TOKEN / PARSER HELPERS
     * ========================= */

    private function peek(): ?string
    {
        return $this->tokens[$this->pos] ?? null;
    }

    private function consume(?string $expected = null): ?string
    {
        $t = $this->tokens[$this->pos++] ?? null;
        $this->lastToken = $t;
        return $t;
    }

    private function match(string $v): bool
    {
        if ($this->peek() === $v) {
            $this->pos++;
            $this->lastToken = $v;
            return true;
        }

        return false;
    }

    private function isCell($t): bool
    {
        return (bool) preg_match('/^[A-Z]+[0-9]+$/', (string)$t);
    }

    private function isFunction($t): bool
    {
        return $t !== null && (bool) preg_match('/^[A-Z]+$/', $t);
    }

    private function isCompare($t): bool
    {
        return in_array($t, ['=', '<>', '>', '<', '>=', '<='], true);
    }

    private function extractFormula(string $v): string
    {
        return preg_replace('/<\/?f>/', '', $v);
    }

    private function stripTags($v)
    {
        return is_string($v) ? preg_replace('/<\/?[fs]>/', '', $v) : $v;
    }

    private function splitCoord(string $c): array
    {
        if (!preg_match('/^([A-Z]+)([0-9]+)$/', $c, $m)) {
            throw new \InvalidArgumentException("Invalid cell coordinate: '$c'");
        }
        return [$m[1], (int)$m[2]];
    }

    private function getRow(string $c): int
    {
        preg_match('/[A-Z]+([0-9]+)/', $c, $m);
        return (int)($m[1] ?? 0);
    }

    private function array_is_list_local(array $arr): bool
    {
        $nextKey = 0;
        foreach ($arr as $key => $_) {
            if ($key !== $nextKey++) {
                return false;
            }
        }
        return true;
    }

    /* =========================
     * IMPORT
     * ========================= */

    /**
     * Populate the spreadsheet from a 2-D array, optionally starting at a
     * given anchor coordinate (default: A1).
     *
     * Each outer entry is a row; each inner entry is a cell value. Values
     * follow the same conventions as setCell():
     *   – Plain scalar          →  stored as-is
     *   – "<f>…</f>" string     →  parsed and stored as AST
     *   – "<style …>…</style>"  →  style is extracted, inner value is stored
     *
     * By default the import REPLACES the entire spreadsheet. Pass
     * $merge = true to overlay the data on top of what is already there
     * (existing cells outside the imported range are kept).
     *
     * Columns are assigned alphabetically from the anchor column; rows are
     * numbered sequentially from the anchor row. Integer-indexed rows AND
     * associative rows (keyed by column letter) are both accepted.
     *
     * Examples:
     *
     *   // Replace everything, data starts at A1
     *   $sheet->import([
     *       ['Name',  'Score',        'Grade'],
     *       ['Alice', 95,             '<f>IF(B2>=90,A,B)</f>'],
     *       ['Bob',   '<f>B2-10</f>', '<f>IF(B3>=90,A,B)</f>'],
     *   ], ['A', 'B', 'C']);
     *
     *   // Merge a 2-row block starting at C5
     *   $sheet->import(
     *       [['x', 'y'], [1, 2]],
     *       headers: ['C', 'D'],
     *       startCoord: 'C5',
     *       merge: true
     *   );
     *
     *   // Associative columns
     *   $sheet->import([
     *       ['A' => 'Name', 'C' => 'Score'],   // B is intentionally skipped
     *   ], ['A', 'C']);
     */
    public function import(
        array  $data,
        array  $headers,
        string $startCoord = 'A1',
        bool   $merge      = false
    ): void {
        if (!$merge) {
            $this->cells        = [];
            $this->cellStyles   = [];
            $this->rowStyles    = [];
            $this->dependencies = [];
            $this->dependents   = [];
        }

        [$anchorCol, $anchorRow] = $this->splitCoord(
        // Normalise: if startCoord has no row number, default to row 1
            preg_match('/^[A-Z]+$/', $startCoord) ? $startCoord . '1' : $startCoord
        );

        $anchorColIndex = $this->colToIndex($anchorCol); // e.g. A=1, B=2, AA=27

        // Build / extend the label → column-letter map from the headers array.
        // Headers are the logical names; their position relative to anchorCol
        // determines which Excel column letter they map to.
        if ($this->array_is_list_local($headers)) {
            foreach ($headers as $colOffset => $label) {
                $colLetter = $this->indexToCol($anchorColIndex + $colOffset);
                $this->labelToCol[$label] = $colLetter;
            }
        }else{
            $colOffset = 0;
            foreach ($headers as $label => $useless) {
                $colLetter = $this->indexToCol($anchorColIndex + $colOffset);
                $this->labelToCol[$label] = $colLetter;
                $colOffset++;
            }
        }

        foreach (array_values($data) as $rowOffset => $rowData) {
            $currentRow = $anchorRow + $rowOffset;

            if ($this->array_is_list_local($rowData)) {
                // Integer-indexed: assign columns left-to-right from the anchor
                foreach ($headers as $colOffset => $colLetter) {
                    $value = $rowData[$colOffset] ?? null;
                    $col   = $this->indexToCol($anchorColIndex + $colOffset);
                    $coord = $col . $currentRow;
                    $this->importCell($coord, $value, $currentRow);
                }
            } else {
                $colOffset =0;
                // Associative: keys are column letters (e.g. 'A', 'BC')
                foreach ($headers as $colLetter => $unused) {
                    $value = $rowData[$colLetter] ?? null;
                    $col   = $this->indexToCol($anchorColIndex + $colOffset);
                    $coord = $col . $currentRow;
                    $this->importCell($coord, $value, $currentRow);
                    $colOffset++;
                }
            }
        }

        $this->rebuildDependencies();

    }

    /**
     * Handle a single cell during import.
     * Unwraps "<style …>…</style>" wrappers before handing off to setCell().
     */
    private function importCell(string $coord, $value, int $row): void
    {
        if (is_string($value)) {
            // Strip and apply any inline <style …> wrapper
            if (preg_match('/^<style ([^>]*)>(.*)<\/style>$/s', $value, $m)) {
                $this->cellStyles[$coord] = $this->parseStyleString($m[1]);
                $value = $m[2];
            }
        }

        $this->setCell($coord, $value);
    }

    /**
     * Parse a style attribute string back into an associative array.
     * Inverse of styleToString().
     * e.g. 'bold=1;color=#f00'  →  ['bold' => true, 'color' => '#f00']
     */
    private function parseStyleString(string $style): array
    {
        $result = [];

        return explode(';', $style);
        foreach (explode(';', $style) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            [$k, $v]    = explode('=', $part, 2) + [1 => ''];
            $result[$k] = match ($v) {
                '1'     => true,
                '0'     => false,
                default => $v,
            };
        }

        return $result;
    }

    /**
     * Convert a column letter to a 1-based integer index.
     * A=1, B=2, …, Z=26, AA=27, AB=28, …
     */
    public function colToIndex(string $col): int
    {
        $index = 0;
        foreach (str_split(strtoupper($col)) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }
        return $index;
    }

    /**
     * Convert a 1-based integer index back to a column letter.
     * 1=A, 2=B, …, 26=Z, 27=AA, …
     */
    public function indexToCol(int $index): string
    {
        $col = '';
        while ($index > 0) {
            $index--;
            $col    = chr(ord('A') + ($index % 26)) . $col;
            $index  = intdiv($index, 26);
        }
        return $col;
    }

    /* =========================
     * EXPORT
     * ========================= */



    private function convertCssToXls($styleString,$data) {
        $styleResult = [];

        foreach (explode(';', $styleString) as $rule) {
            $rule = trim($rule);
            if ($rule === '') continue;

            [$key, $value] = explode(':', $rule, 2);
            $key = trim($key);
            $value = trim($value);
            if(strtolower($key) =="background-color"){
                $styleResult[]='bgcolor="'.$value.'"';
            }else if(strtolower($key) =="color"){
                $styleResult[]='color="'.$value.'"';
            }else if(strtolower($key) =="font-weight"){
                if(strtolower($value) =="bold"){
                    $data = "<b>$data</b>";
                }else if(strtolower($value) =="italic"){
                    $data = "<i>$data</i>";
                }else if(strtolower($value) =="underline"){
                    $data = "<u>$data</u>";
                }
            }
        }

        if(count($styleResult)>0){
            $data = "<style ".join(" ",$styleResult).">".$data."</style>";
        }

        return $data;
    }

    private function custom_array_keys($array) {
        $keys = [];

        foreach ($array as $key => $value) {
            $keys[] = $key;
        }

        return $keys;
    }
    /**
     * Export the spreadsheet into a compact 2-D array.
     *
     * Sparse coordinates are normalised: the lowest used row becomes row 1,
     * the lowest used column stays as-is (only row numbers are remapped).
     * Formula cells are serialised back to "<f>…</f>" strings with their
     * cell references rewritten to match the normalised row numbers.
     * Styles are inlined as "<style …>…</style>" wrappers.
     *
     * @param array $destination  Populated by reference; each entry is a 1-D
     *                            array of formatted cell strings for that row.
     */
    public function export(&$destination): void
    {
        $rowMap = [];
        $colMap = [];

        // 1. Collect used coordinates
        foreach ($this->cells as $coord => $_) {
            [$col, $row] = $this->splitCoord($coord);
            $colMap[$col] = true;
            $rowMap[$row] = true;
        }

        $rows = array_keys($rowMap);
        $cols = array_keys($colMap);

        // Build normalised row-number map  (sparse row 5 → export row 1, etc.)
        $rowIndexMap = [];
        foreach ($rows as $i => $r) {
            $rowIndexMap[$r] = $i + 1;
        }

        $colsToLabel = [];
        foreach ($this->labelToCol as $val=>$key) {
            $colsToLabel[$key] =$val;
        }

        // 2. Build export rows
        foreach ($rows as $r) {
            $exportRow = [];

            foreach ($cols as $c) {
                $coord     = $c . $r;
                $label     = $colsToLabel[$c];
                $stored    = $this->cells[$coord] ?? '';
                $cellStyle = $this->cellStyles[$coord] ?? [];
                $rowStyle  = $this->rowStyles[$r]      ?? [];

                // Merge styles: row-level first, cell-level overrides
                $mergedStyle = array_merge($rowStyle, $cellStyle);
                $styleStr    = $mergedStyle ? $this->styleToString($mergedStyle) : '';



                $cellOut = $this->formatCellValue($stored, $rowIndexMap);

                $newRow = $this->convertCssToXls($styleStr, $cellOut);

                $exportRow[] = $newRow;
            }

            $destination[] = $exportRow;
        }
    }

    /* =========================
     * EXPORT HELPERS
     * ========================= */

    /**
     * Serialise a style array to an attribute-style string.
     * e.g. ['bold' => true, 'color' => '#f00']  →  'bold=1;color=#f00'
     */
    private function styleToString(array $style): string
    {
        $parts = [];
        if($this->array_is_list_local($style)) {
            return implode(';', $style);
        }else {
            foreach ($style as $k => $v) {
                $parts[] = $k . '=' . ($v === true ? '1' : ($v === false ? '0' : $v));
            }
        }
        return implode(';', $parts);
    }

    /**
     * Turn a stored cell value into its export string.
     *
     * – AST cells  → "<f>…serialised formula with renumbered refs…</f>"
     * – Scalar     → plain string (tags already stripped when stored)
     */
    private function formatCellValue($stored, array $rowIndexMap): string
    {
        if (is_array($stored) && isset($stored['ast'])) {
            $shifted  = $this->shiftAstRows($stored['ast'], $rowIndexMap);
            $result =$this->astToFormula($shifted);
            return '<f>' . $result . '</f>';
        }
        if(is_array($stored)) {
            return translate('NOT_APPLICABLE_ACRONYM');
        }
        return (string)$this->stripTags($stored);
    }

    /**
     * Walk an AST and remap every cell-ref row number through $rowIndexMap.
     * This is a read-only pass used only during export – the stored AST is
     * never mutated.
     */
    private function shiftAstRows(array $node, array $rowIndexMap): array
    {
        if ($node['type'] === 'range') {
            foreach (['from', 'to'] as $key) {
                [$col, $row] = $this->splitCoord($node[$key]);
                if (isset($rowIndexMap[$row])) {
                    $node[$key] = $col . $rowIndexMap[$row];
                }
            }
            return $node;
        }
        if ($node['type'] === 'cell') {
            [$col, $row] = $this->splitCoord($node['ref']);
            if (isset($rowIndexMap[$row])) {
                $node['ref'] = $col . $rowIndexMap[$row];
            }
            return $node;
        }

        foreach ($node as $key => $child) {
            if (is_array($child)&& isset($child['type'])) {
                $node[$key] = $this->shiftAstRows($child, $rowIndexMap);
            }
        }

        return $node;
    }

    /**
     * Serialise an AST back to a formula string.
     *
     * Mirrors the grammar in the parser so the output is always valid input
     * for parseFormula().
     */
    private function astToFormula(array $node): string
    {
        return match ($node['type']) {
            'range' => $node['from'] . ':' . $node['to'],
            'num'  => (string)$node['value'],
            'raw'  => (string)$node['value'],
            'cell' => $node['ref'],
            'unary' => $node['op'] . $this->astToFormula($node['operand']),
            'concat' => $this->astToFormula($node['left'])
                . '&'
                . $this->astToFormula($node['right']),

            'binary' => $this->astToFormula($node['left'])
                . $node['op']
                . $this->astToFormula($node['right']),

            'compare' => $this->astToFormula($node['left'])
                . $node['op']
                . $this->astToFormula($node['right']),

            'func' => $node['name']
                . '('
                . implode(',', array_map(fn($a) => $this->astToFormula($a), $node['args']))
                . ')',

            default => '',
        };
    }

    /**
     * Insert $count blank columns after $afterCol, then optionally name them.
     *
     * $afterCol  – column letter to insert after, e.g. 'B'.
     *              Pass null to append after the last occupied column.
     * $count     – how many columns to insert (default 1).
     * $names     – optional list of label names for the new columns,
     *              e.g. ['discount', 'tax'].  Names are registered in
     *              $labelToCol so formulas can reference them with backticks
     *              or dot-notation immediately after insertion.
     *
     * Example – insert 1 col after B, name it 'discount':
     *
     *   $sheet->addCol('B', 1, ['discount']);
     *
     * Example – append 2 unnamed cols at the end:
     *
     *   $sheet->addCol(null, 2);
     */
    public function addCol(?string $afterCol, int $count = 1, array $names = [])
    {
        // null → append after the last occupied column
        if ($afterCol === null) {
            $afterCol = $this->lastCol();
        }

        $afterCol      = strtoupper($this->resolveColKey($afterCol));
        $afterColIndex = $this->colToIndex($afterCol);
        $newCells     = [];
        $newStyles    = [];

        // ---- Shift every cell whose column index is above the insertion point ----
        foreach ($this->cells as $coord => $stored) {
            [$col, $row] = $this->splitCoord($coord);
            $colIndex    = $this->colToIndex($col);

            if ($colIndex > $afterColIndex) {
                $col = $this->indexToCol($colIndex + $count);
            }

            $newCoord = $col . $row;

            if (is_array($stored) && isset($stored['ast'])) {
                $newCells[$newCoord] = ['ast' => $this->shiftAstCols($stored['ast'], $afterColIndex, $count)];
            } else {
                $newCells[$newCoord] = $stored;   // plain scalars have no col refs to shift
            }

            if (isset($this->cellStyles[$coord])) {
                $newStyles[$newCoord] = $this->cellStyles[$coord];
            }
        }

        $this->cells      = $newCells;
        $this->cellStyles = $newStyles;

        // ---- Update labelToCol: shift any label that pointed past the insertion ----
        foreach ($this->labelToCol as $label => $col) {
            $idx = $this->colToIndex($col);
            if ($idx > $afterColIndex) {
                $this->labelToCol[$label] = $this->indexToCol($idx + $count);
            }
        }

        // ---- Register names for the newly inserted columns ----
        foreach ($names as $i => $name) {
            if ($name !== null && $name !== '') {
                $this->labelToCol[$name] = $this->indexToCol($afterColIndex + 1 + $i);
            }
        }

        $this->rebuildDependencies();
        return $this->lastCol();
    }

    /**
     * Return the letter of the rightmost occupied column, or 'A' if empty.
     */
    public function lastCol(): string
    {
        $max = 0;
        foreach ($this->cells as $coord=>$val) {
            [$col,] = $this->splitCoord($coord);
            $idx    = $this->colToIndex($col);
            if ($idx > $max) {
                $max = $idx;
            }
        }
        if($max==0){
            foreach ($this->labelToCol as $label=>$col) {
                $idx    = $this->colToIndex($col);
                if ($idx > $max) {
                    $max = $idx;
                }
            }
        }
        return $max > 0 ? $this->indexToCol($max) : 'A';
    }

    /**
     * Walk an AST and shift every column reference whose index is above
     * $afterColIndex by $offset. Mirrors shiftAst() which does the same for rows.
     */
    private function shiftAstCols(array $node, int $afterColIndex, int $offset): array
    {
        if ($node['type'] === 'cell') {
            [$col, $row] = $this->splitCoord($node['ref']);
            $idx = $this->colToIndex($col);
            if ($idx > $afterColIndex) {
                $node['ref'] = $this->indexToCol($idx + $offset) . $row;
            }
            return $node;
        }

        // Handle range nodes (if the range fix from earlier is applied)
        if ($node['type'] === 'range') {
            foreach (['from', 'to'] as $key) {
                [$col, $row] = $this->splitCoord($node[$key]);
                $idx = $this->colToIndex($col);
                if ($idx > $afterColIndex) {
                    $node[$key] = $this->indexToCol($idx + $offset) . $row;
                }
            }
            return $node;
        }

        foreach ($node as $key => $child) {
            if (is_array($child) && isset($child['type'])) {
                $node[$key] = $this->shiftAstCols($child, $afterColIndex, $offset);
            }
        }

        return $node;
    }

    public function exportSimpleGrid(&$destination): void
    {
        $rowMap = [];
        $colMap = [];

        // 1. Collect used coordinates
        foreach ($this->cells as $coord => $_) {
            [$col, $row] = $this->splitCoord($coord);
            $colMap[$col] = true;
            $rowMap[$row] = true;
        }

        $rows = array_keys($rowMap);
        $cols = array_keys($colMap);

        // Build normalised row-number map  (sparse row 5 → export row 1, etc.)
        $rowIndexMap = [];
        foreach ($rows as $i => $r) {
            $rowIndexMap[$r] = $i + 1;
        }

        $colsToLabel = [];
        foreach ($this->labelToCol as $val=>$key) {
            $colsToLabel[$key] =$val;
        }
        // 2. Build export rows
        foreach ($rows as $r) {
            $exportRow = [];

            foreach ($cols as $c) {

                $coord     = $c . $r;
                $label     = $colsToLabel[$c];
                $stored    = $this->cells[$coord] ?? '';
                $cellStyle = $this->cellStyles[$coord] ?? [];
                $rowStyle  = $this->rowStyles[$r]      ?? [];

                // Merge styles: row-level first, cell-level overrides
                $mergedStyle = array_merge($rowStyle, $cellStyle);
                $styleStr    = $mergedStyle ? $this->styleToString($mergedStyle) : '';

                $cellOut = $this->getCell($coord);

                $exportRow[$label] = $styleStr
                    ? "<style '{$styleStr}'>{$cellOut}</style>"
                    : $cellOut;
            }

            $destination[] = $exportRow;
        }
    }
}