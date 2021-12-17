<?php

namespace lib\db;

use lib\db\parser\IdentifyResult;
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
            } else if (($extId =$this->isExternalType($s))>=0) {
                $o = $this->externalTypes[$extId]->buildToken($s);
                if ($o == null) {
                    throw new Exception("Token '" . $s . "' not supported by external provider");
                }
                $temp[] = $o;
            } else if (strtolower($s) == "null") {
                $o = new Operator();
                $o->Type = "string";
                $o->Value = "";
                $temp[] = $o;
            } else {
                throw new \Exception("Token '".$s."' not supported parsing");
                /*//Assume field
                $o = new Operator();
                $o->Type = "field";
                //Fix for Chocolatey dirty search
                if (strcmp($s, "id") == 0) {
                    $s = "Id";
                }
                $o->Value = $s;
                $temp[] = $o;*/
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
        $ref = new ReflectionClass($objet);
        foreach (get_class_vars(get_class($objet)) as $key=>$value){
            $this->fields[] = strtolower($key);
            //TODO
            $type = $this->typeFromComment($ref,$key);


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
        $do = true;
        for ($i = 0; $i < sizeof($identified) && $do; $i++) {
            $val = $identified[$i];
            $t = strtolower($val->Type);
            $v = strtolower($val->Value);

            if ($t == "orderby" && $v == "orderby") {
                $i++;
                for (; $i < sizeof($identified) && $do; $i++) {
                    $val = $identified[$i];
                    $t = strtolower($val->Type);
                    $v = strtolower($val->Value);
                    if ($t == "groupby" && $v == "groupby") {
                        $i++;
                        for (; $i < sizeof($identified); $i++) {
                            $gbClause[] = $identified[$i];
                        }
                        $do = false;
                    } else {
                        $obClause[] = $identified[$i];
                    }
                }
                $do = false;
            } else {
                $result[] = $val;
            }
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
            if($v==$operator && $t!="string" ){
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

    private function isExternalType($s)
    {
        if($this->externalTypes==null || sizeof($this->externalTypes)==0) return -1;
        for ($i=0;$i<sizeof($this->externalTypes);$i++){
            if($this->externalTypes[$i]->isExternal($s)){
                return $i;
            }
        }
        return -1;
    }

    public function execute($subject)
    {
        if($this->parseResult==null || sizeof($this->parseResult)==0){
            return true;
        }
        $parseTreeItem = $this->parseResult[0];

        $result = @$this->_doExecute($parseTreeItem,$subject);
        return $result->Value;
    }

    private function _executeFunction($name,$params)
    {
        if($this->externalTypes!=null && $this->externalTypes->canHandle($name,$params)){
            return $this->externalTypes->$name($params);
        }
        return $this->$name($params);
    }

    public function _doExecute($parseTreeItem,$subject)
    {
        $t = strtolower($parseTreeItem->Type);
        $v = $parseTreeItem->Value;
        $c = $parseTreeItem->Children;
        switch($t){
            case "string":
            case "number":
            case "boolean":
                return $parseTreeItem;
        }
        if($t == "function"){
            $params = array();
            for($i=0;$i<sizeof($c);$i++){
                $params[] = $this->_doExecute($c[$i],$subject);
            }

            $result = $this->_executeFunction($v,$params);
        }else if($t == "group"){
            $params = array();
            for($i=0;$i<sizeof($c);$i++){
                $params[] = $this->_doExecute($c[$i],$subject);
            }
            $params[]=true;
            $result = $this->_executeFunction("doeq",$params);
        }else if($t=="field"){
            $fo = new Operator();
            $fo->Type = "fieldinstance";
            $fo->Value = $subject->$v;
            $fo->Id = $v;
            return $fo;
        }else if($this->isExternalType($v)>=0){
            return $parseTreeItem;
        }else{
            throw new ParserException("Token '".$t."' not supported excuting (2)");
        }
        return $result;
    }



    function substringof($args)
    {
        $res = $this->buildBool(contains(strtolower($args[0]->Value),strtolower($args[1]->Value)));
        /*echo ($args[0]->Value)."\n";
        echo ($args[1]->Value)."\n";
        var_dump($res);
        echo "===============\n";*/
        return $res;
    }

    function doand($args)
    {
        for($i=0;$i<sizeof($args);$i++){
            if(!$args[$i]->Value){
                return $this->buildBool(false);
            }
        }
        return $this->buildBool(true);
    }

    function door($args)
    {
        for($i=0;$i<sizeof($args);$i++){
            if($args[$i]->Value){
                return $this->buildBool(true);
            }
        }
        return $this->buildBool(false);
    }

    function tolower($args)
    {
        return $this->buildItem(strtolower($args[0]->Value),$args[0]->Type,$args[0]->Id);
    }

    function toupper($args)
    {
        return $this->buildItem(strtoupper($args[0]->Value),$args[0]->Type,$args[0]->Id);
    }

    function startsWithInt($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }
    function endsWithInt($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function startswith($args)
    {
        return $this->buildBool($this->startsWithInt($args[0]->Value,$args[1]->Value));
    }

    function endswith($args)
    {
        return $this->buildBool($this->endsWithInt($args[0]->Value,$args[1]->Value));
    }

    function dosubstringof($args)
    {
        $l=$args[0];
        $r=$args[1];
        $pos = stripos($r->Value, $l->Value);
        if ($pos === false) {
            return $this->buildBool(false);
        } else {
            return $this->buildBool(true);
        }
    }

    function doeq($args)
    {
        $l=$args[0];
        $r=$args[1];

        if($l->Type=="string" || $r->Type=="string"){
            return $this->buildBool(strtolower($l->Value)==strtolower($r->Value));
        }

        return $this->buildBool($l->Value == $r->Value);
    }

    function doneq($args)
    {
        $l=$args[0];
        $r=$args[1];
        return $this->buildBool($l->Value != $r->Value);
    }

    function done($args)
    {
        $l=$args[0];
        $r=$args[1];
        return $this->buildBool($l->Value != $r->Value);
    }

    function dogt($args)
    {
        $l=$args[0];
        $r=$args[1];
        return $this->buildBool($l->Value > $r->Value);
    }

    function dogte($args)
    {
        $l=$args[0];
        $r=$args[1];
        if($l->Value == $r->Value) return $this->buildBool(true);
        return $this->buildBool($l->Value > $r->Value);
    }

    function dolt($args)
    {
        $l=$args[0];
        $r=$args[1];
        return $this->buildBool($l->Value < $r->Value);
    }

    function dolte($args)
    {
        $l=$args[0];
        $r=$args[1];
        if($l->Value == $r->Value) return $this->buildBool(true);
        return $this->buildBool($l->Value < $r->Value);
    }

    function buildBool($value)
    {
        $o = new Operator();
        $o->Type = "boolean";
        $o->Value = false;
        if($value==true || $value>=1 || $value=="true"){
            $o->Value = true;
        }
        return $o;
    }

    function buildItem($value,$type,$id)
    {
        $o = new Operator();
        $o->Type = strtolower($type);
        $o->Value = $value;
        $o->Id = $id;
        return $o;
    }

    public function doSort($subject,$types)
    {
        $this->_types= $types;

        if(sizeof($this->_sortClause)==0) return $subject;

        usort($subject, array($this, "_doSort"));
        return $subject;
    }

    /*
	The comparison function must return an integer
	less than, if the first argument is considered to be less then the second
	equal to, if the first argument is considered to be equal to the second
	greater than zero if the first argument is considered to be greater than the second.
	*/
    public function _doSort($f,$s)
    {
        $print = false;

        for($i=0;$i<sizeof($this->_sortClause);$i++){
            $so = $this->_sortClause[$i];
            $row = $so->Field;
            $asc = $so->Asc;
            $type = $this->_types[$row];

            $res = $this->_cmp($f->$row,$s->$row,$asc,$type);
            if($res>0){
                //if($print)echo $f->Title." ".$f->Version.">".$s->Title." ".$s->Version."\r\n";
                return $asc?1:-1;
            }else if($res<0){
                //if($print)echo $f->Title." ".$f->Version."<".$s->Title." ".$s->Version."\r\n";
                return $asc?-1:1;
            }
        }
        //if($print)echo $f->Title." ".$f->Version."==".$s->Title." ".$f->Version."\r\n";
        return 0;
    }

    /*
    The comparison function must return an integer
    less than, if the first argument is considered to be less then the second
    equal to, if the first argument is considered to be equal to the second
    greater than zero if the first argument is considered to be greater than the second.
    */
    public function _cmp($f,$s,$asc,$type)
    {

        if(($fId =$this->isExternalType($f))>=0 && ($sId =$this->isExternalType($s))>=0){
            $ft = $this->externalTypes[$fId]->buildToken($f);
            $st = $this->externalTypes[$sId]->buildToken($s);
            $arg = array();
            $arg[] = $ft;
            $arg[] = $st;
            if($this->externalTypes->dolt($arg)->Value)return -1;
            if($this->externalTypes->dogt($arg)->Value) return 1;
            return 0;
        }
        switch($type){
            case("boolean"):
            case("number"):
                return $f>$s;
            case("string"):
            case("date"):
                return strcasecmp($f,$s);
        }

        return 0;
    }

    public function doGroupBy($subject)
    {
        if(sizeof($this->_groupClause)==0) return $subject;
        $result = array();
        $keys = array();

        foreach($subject as $item){
            $k = "";
            for($i=0;$i<sizeof($this->_groupClause);$i++){
                $fld = $this->_groupClause[$i];
                $k.="#".$item->$fld;
            }
            if(!array_key_exists($k,$keys)){
                $keys[$k] = true;
                $result[]=$item;
            }
        }

        return $result;
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
}