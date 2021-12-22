<?php

namespace lib\db;

use lib\db\parser\IdentifyResult;
use lib\db\parser\InternalTypeBuilder;
use lib\db\parser\Operator;
use lib\db\parser\SortClause;
use ReflectionClass;

class QueryParser
{
    protected $externalTypes = null;
    private $keywords = array(
        "eq", "neq", "ne", "gt", "lt", "gte", "lte",
        "substringof",
        "orderby", "desc", "asc",
        "groupby",
        "or", "and",
        "true", "false",
        "null");
    private $binaryOperator = array("eq", "neq", "ne", "gt", "lt", "gte", "lte");
    private $logicalOperator = array("or", "and");
    private $trueFalse = array("true", "false");
    private $orderBy = array("orderby", "desc", "asc");
    private $groupBy = array("groupby");
    private $functions = array(
        "tolower", "toupper",
        "startswith", "endswith",
        "substringof",
        "doAnd", "doOr");
    private $separators = array("(", ")", " ", "\t", " ", ",");

    /*
        Version number are treated with a orderBy comparer starting with "V"
        eq,neq,ne,gt,lt,gte,lte,substringof
        eq FieldName 'data'/'DateTime'
        eq FieldName true/false
        like FieldName what
        orderBy desc/asc FieldName
        *
    */
    /**
     * @var array|mixed
     */
    private mixed $parseResult;
    private $_types;
    private array $fields;
    private array $fieldsMatch;

    function _isStartString($char)
    {
        return $char == "'" || $char == "\"";
    }

    function _isSpace($char)
    {
        return $char == "," || $char == " " || $char == "\t" || $char == "\r" || $char == "\n" || $char == "\f";
    }

    function _isSeparator($char)
    {
        return in_array($char, $this->separators);
    }

    function _isFunction($operator)
    {
        return in_array(strtolower($operator), $this->functions);
    }

    function _isBinary($operator)
    {
        return in_array(strtolower($operator), $this->binaryOperator);
    }

    function _isLogical($operator)
    {
        return in_array(strtolower($operator), $this->logicalOperator);
    }

    function _isBoolean($operator)
    {
        return in_array(strtolower($operator), $this->trueFalse);
    }

    function _isOrderBy($operator)
    {
        return in_array(strtolower($operator), $this->orderBy);
    }

    function _isGroupBy($operator)
    {
        return in_array(strtolower($operator), $this->groupBy);
    }

    function _isString($operator)
    {
        if (is_array($operator)) {
            if ($operator[0] == $operator[sizeof($operator) - 1]) {
                return $this->_isStartString($operator[0]);
            }
        } else if (is_string($operator)) {
            if ($operator[0] == $operator[strlen($operator) - 1]) {
                return $this->_isStartString($operator[0]);
            }
        }

        return false;
    }

    function _isNumeric($operator)
    {
        //return is_numeric($operator);
        return preg_match("/^-?([0-9])+([\.|,]([0-9])*)?$/i", $operator);
    }

    function _isField($operator)
    {
        return in_array(strtolower($operator), $this->fields);
    }

    function tokenize($queryString)
    {
        $splitted = array();
        $inString = false;
        $current = "";
        for ($i = 0; $i < strlen($queryString); $i++) {
            $c = $queryString[$i];
            if (!$inString && $this->_isStartString($c)) {
                $current = $c;
                $inString = true;
            } else if ($inString && $c != $current[0]) {
                $current .= $c;
            } else if ($inString && $c == $current[0]) {
                $inString = false;
                $current .= $c;
                $splitted[] = $current;
                $current = "";
            } else if ($this->_isSeparator($c)) {
                if ($this->_isSpace($c)) {
                    if (strlen($current) > 0) {
                        $splitted[] = $current;
                        $current = "";
                    }
                } else {
                    if (strlen($current) > 0) {
                        $splitted[] = $current;
                        $current = "";
                    }
                    $splitted[] = $c;
                    $current = "";
                }
            } else {
                $current .= $c;
            }
        }
        if (strlen($current) > 0) {
            $splitted[] = $current;
            $current = "";
        }
        return $splitted;
    }

    function _identify($splitted, $i)
    {
        $temp = array();
        $prev = null;
        $result = new IdentifyResult();
        $isGroup = false;
        for (; $i < sizeof($splitted); $i++) {
            $s = $splitted[$i];
            if($this->_isField($s)){
                $o = new Operator();
                $o->Type = "field";
                //Fix for Chocolatey dirty search
                if(strcmp($s,"id")==0){
                    $s="Id";
                }
                $o->Value = $s;
                $temp[] = $o;
            }else
            if ($this->_isString($s)) {
                $o = new Operator();
                $o->Type = "string";
                $o->Value = substr($s, 1, strlen($s) - 2);
                $temp[] = $o;
            } else if ($this->_isBoolean($s)) {
                $o = new Operator();
                $o->Type = "boolean";
                $o->Value = $s == "true";
                $temp[] = $o;
            } else if ($this->_isBinary($s)) {
                $o = new Operator();
                $o->Type = "binary";
                $o->Value = $s;
                $temp[] = $o;
            } else if ($this->_isLogical($s)) {
                $o = new Operator();
                $o->Type = "logical";
                $o->Value = $s;
                $temp[] = $o;
            } else if ($this->_isOrderBy($s)) {

                $o = new Operator();
                $o->Type = "orderby";
                $o->Value = $s;
                $temp[] = $o;
            } else if ($this->_isGroupBy($s)) {

                $o = new Operator();
                $o->Type = "groupby";
                $o->Value = $s;
                $temp[] = $o;
            } else if ($this->_isFunction($s)) {
                $o = new Operator();
                $o->Type = "function";
                $o->Value = $s;
                $temp[] = $o;
            } else if ($s == "(") {
                $o = new Operator();
                $i++;
                if ($prev != null && ($prev->Type == "function" || $prev->Type == "method")) {
                    $o = $prev;
                } else {
                    $o->Type = "group";
                    $o->Value = $s;
                    $temp[] = $o;
                }
                $tempIdentify = $this->_identify($splitted, $i);

                $o->Children = $tempIdentify->List;
                $i = $tempIdentify->Next;
            } else if ($s == ")") {
                $result->Next = $i;
                $result->List = $temp;
                return $result;
            } else if ($this->_isNumeric($s)) {
                $o = new Operator();
                $o->Type = "number";
                $o->Value = $s + 0;
                $temp[] = $o;
            /*} else if (($extId =$this->isExternalType($s))>=0) {
                $o = $this->externalTypes[$extId]->buildToken($s);
                if ($o == null) {
                    throw new Exception("Token '" . $s . "' not supported by external provider");
                }
                $temp[] = $o;
            */} else if (strtolower($s) == "null") {
                $o = new Operator();
                $o->Type = "string";
                $o->Value = "";
                $temp[] = $o;
            } else {
                $o = new Operator();
                $o->Type = "mixed";
                $o->Value = $s;
                $temp[] = $o;
            }
            $prev = $temp[sizeof($temp) - 1];
        }
        $result->Next = $i;
        $result->List = $temp;
        return $result;
    }

    public function parse($queryString,$objet,$externalTypes=null)
    {
        $this->externalTypes = $externalTypes;
        $this->fields = array();
        $this->fieldsMatch = array();
        $this->_types = array();
        $ref = new ReflectionClass($objet);
        foreach (get_class_vars(get_class($objet)) as $key=>$value){
            $this->fields[] = strtolower($key);
            $this->fieldsMatch[strtolower($key)]=$key;
            $this->_types[strtolower($key)] = $this->typeFromComment($ref,$key);
        }

        $splitted = $this->tokenize($queryString);
        $notOperator = array();
        $operator = array();
        foreach ($splitted as $spl) {
            if ($this->_isOrderBy($spl) || $this->_isGroupBy($spl)) {
                $notOperator[] = $spl;
            } else if (sizeof($notOperator) > 0) {
                $notOperator[] = $spl;
            } else {
                $operator[] = $spl;
            }
        }
        $operatorIdentified = $this->_identify($operator, 0)->List;
        $notOperatorIdentified = $this->_identify($notOperator, 0)->List;
        @$this->_storeOrderByClause($notOperatorIdentified);
        $this->parseResult = @$this->_reorderLogicalOperators($operatorIdentified);
        //zzzvar_dump($this->parseResult);die();
        return $this->parseResult;
    }

    function _storeOrderByClause($identified)
    {
        $result = array();
        $obClause = array();
        $gbClause = array();
        $isOrderBy = false;
        $isGroupBy = false;
        for ($i = 0; $i < sizeof($identified); $i++) {
            $val = $identified[$i];
            $t = strtolower($val->Type);
            $v = strtolower($val->Value);

            if ($t == "groupby" && $v == "groupby") {
                $i++;
                for (; $i < sizeof($identified); $i++) {
                    $val = $identified[$i];
                    $t = strtolower($val->Type);
                    $v = strtolower($val->Value);
                    if ($t == "orderby" && $v == "orderby") {
                        $i--;
                        break;
                    } else {
                        $gbClause[] = $identified[$i];
                    }
                }
                continue;
            }
            if ($t == "orderby" && $v == "orderby") {
                $i++;
                for (; $i < sizeof($identified); $i++) {
                    $val = $identified[$i];
                    $t = strtolower($val->Type);
                    $v = strtolower($val->Value);
                    if ($t == "groupby" && $v == "groupby") {
                        $i--;
                        break;
                    } else {
                        $obClause[] = $identified[$i];
                    }
                }
                continue;
            }
            $result[] = $val;
        }

        if (sizeof($obClause) > 0) {
            $this->_buildSorter($obClause);
        }

        if (sizeof($gbClause) > 0) {
            $this->_buildGrouper($gbClause);
        }
        return $result;
    }

    var $_groupClause = array();

    function _buildGrouper($ob)
    {
        $this->_groupClause = array();
        for ($i = 0; $i < sizeof($ob); $i++) {
            $val = $ob[$i];
            $t = strtolower($val->Type);
            $v = $val->Value;
            if ($t == "field") {
                $this->_groupClause[] = $v;
            }

        }
    }

    var $_sortClause = array();

    function _buildSorter($ob)
    {
        $this->_sortClause = array();
        for ($i = 0; $i < sizeof($ob); $i++) {
            $val = $ob[$i];
            $t = strtolower($val->Type);
            $v = $val->Value;
            if ($t == "field") {
                if (($i + 1) == sizeof($ob)) {
                    $sc = new SortClause();
                    $sc->Field = $v;
                    $sc->Type = $t;
                    $sc->Asc = true;
                    $this->_sortClause[] = $sc;
                    return;
                }
                $next = $ob[$i + 1];
                $nt = strtolower($next->Type);
                $nv = strtolower($next->Value);
                if ($nv == "asc") {
                    $sc = new SortClause();
                    $sc->Field = $v;
                    $sc->Type = $t;
                    $sc->Asc = true;
                    $this->_sortClause[] = $sc;
                    $i++;
                } else if ($nv == "desc") {
                    $sc = new SortClause();
                    $sc->Field = $v;
                    $sc->Asc = false;
                    $sc->Type = $t;
                    $this->_sortClause[] = $sc;
                    $i++;
                } else {
                    $sc = new SortClause();
                    $sc->Field = $v;
                    $sc->Type = $t;
                    $sc->Asc = true;
                    $this->_sortClause[] = $sc;
                }
            }

        }
    }

    function _reorderLogicalOperators($identified, $parent = null)
    {
        $result = array();
        for ($i = 0; $i < sizeof($identified); $i++) {
            $o = $identified[$i];
            $t = strtolower($o->Type);
            $v = $o->Value;
            if ($t == "function") {
                $o->Children = $this->_reorderLogicalOperators($o->Children, $o);
            } else if ($t == "group") {
                $temp = $this->_reorderLogicalOperators($o->Children, $o);

                if (sizeof($temp) == 1) {
                    $o = $temp[0];
                } else {
                    $o->Children = $temp;
                }
            }
            $result[] = $o;
        }
        $identified = $this->_subRenderLogicalOperators($result, "eq", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "neq", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "ne", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "gt", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "gte", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "lt", 2);
        $identified = $this->_subRenderLogicalOperators($identified, "lte", 2);

        $identified = $this->_subRenderLogicalOperators($identified, "and", -1);
        $identified = $this->_subRenderLogicalOperators($identified, "or", -1);


        return $identified;
    }

    function _subRenderLogicalOperators($identified,$operator,$numerosity)
    {
        $andResult = array();
        $founded =0;
        for($i=0;$i< sizeof($identified);$i++){
            $o = $identified[$i];
            $v = strtolower($o->Value);
            if($v==$operator){
                $founded++;
            }
        }
        if($founded == 0){
            return $identified;
        }

        //Group all and
        for($i=0;$i< sizeof($identified);$i++){
            $o = $identified[$i];

            $v = strtolower($o->Value);
            $t = strtolower($o->Type);
            if($v==$operator && $t!="string" && $t!="mixed"){
                //Seek last logical operator
                $popped = array_pop($andResult);
                $lastEnd = $i;
                if($numerosity==-1){
                    for($k=$i;$k< sizeof($identified);$k+=2){
                        $lastValue = strtolower($o->Value);
                        if($lastValue!=$v){
                            $lastEnd = $k-1;
                            break;
                        }
                    }
                }else if($numerosity==2){
                    $lastEnd = $i;

                }else if($numerosity==1){
                    $andResult[] = $popped;
                    $lastEnd = $i;
                }
                $and = new Operator();
                $and->Type = "function";
                $and->Value = "do".$operator;
                if($numerosity!=1){
                    $and->Children[]=$popped;
                }
                for($j=$i+1;$j<=($lastEnd+1);$j+=2){
                    $and->Children[] = $identified[$j];
                }
                $i=$lastEnd+1;
                $andResult[] = $and;
            }else{
                $andResult[] = $o;
            }
        }
        return $andResult;
    }

    /*
    private function isExternalType($s)
    {
        if($this->externalTypes!=null) {
            for ($i = 0; $i < sizeof($this->externalTypes); $i++) {
                if ($this->externalTypes[$i]->isExternal($s)) {
                    return $i;
                }
            }
        }
        return -1;
    }*/







    public function hasGroupBy(){
        return sizeof($this->_groupClause)>0;
    }

    public function hasOrderBy(){
        return sizeof($this->_sortClause)>0;
    }

    /**
     * @param ReflectionClass $ref
     * @param string $key
     * @return void
     * @throws \ReflectionException
     */
    private function typeFromComment($ref, $key)
    {
        $output_array = array();
        $property = $ref->getProperty($key);
        $comment=$property->getDocComment();
        if($comment!=null && strlen($comment)>0) {
            $result = preg_match('/@var\s([a-zA-Z0-9\[\]]+)/im', $comment, $output_array);
            if ($result == 1 && $result != false) {
                return $output_array[1];
            }
        }
        return "string";
    }

    public function setupExecutor(Executor $executor)
    {
        $executor->initialize($this->parseResult,$this->externalTypes,$this->_groupClause,$this->groupBy,
            $this->_sortClause,$this->_types,$this->fieldsMatch);
        return $executor;
    }
}