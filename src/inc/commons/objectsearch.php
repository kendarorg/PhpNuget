<?php
class Operator
{
	public $Type;
	public $Value;
	public $Id;
	public $Children = array();
	public function toString()
	{
		$t = strtolower($this->Type);
		
		if($t=="string"){
			return "'".$this->Value."'";
		}
		$r = $this->Value;
		if($t=="function"){
			$r .="(";
		}
		for($i=0;$i<sizeof($this->Children);$i++){
			$c = $this->Children[$i];
			$r.=$c->toString();
			if(($i+1)<sizeof($this->Children) && $t=="function"){
				$r.=",";
			}
			
		}
		if($t=="function"){
			$r .=")";
		}
		return $r;
	}
}

class IdentifyResult
{
	public $List;
	public $Next;
}
class SortClause
{
	public $Field;
	public $Type;
	public $Asc;
}

class ParserException extends Exception
{

}



function BuildBool($value)
{
	$o = new Operator();
	$o->Type = "boolean";
	$o->Value = false;
	if($value==true || $value>=1 || $value=="true"){
		$o->Value = true;
	}
	return $o;
}

class ObjectSearch
{
	private $keywords = array(
		"eq","neq","ne","gt","lt","gte","lte",
		"substringof",
		"orderby","desc","asc",
		"groupby",
		"or","and",
		"true","false",
		"null");
	private $binaryOperator = array("eq","neq","ne","gt","lt","gte","lte");
	private $logicalOperator = array("or","and");
	private $trueFalse = array("true","false");
	private $orderBy = array("orderby","desc","asc");
	private $groupBy = array("groupby");
	private $fields = array();
	private $functions = array(
		"tolower","toupper",
		"startswith","endswith",
		"substringof",
		"doAnd","doOr");
	protected $externalTypes = null;
	protected $parseResult = null;
	private $_fieldTypes = array();
	
	private $separators = array("(",")"," ","\t"," ",",");
	/*
		Version number are treated with a orderBy comparer starting with "V"
		eq,neq,ne,gt,lt,gte,lte,substringof
		eq FieldName 'data'/'DateTime'
		eq FieldName true/false
		like FieldName what 
		orderBy desc/asc FieldName
		* 
	*/
	
	public function _extraValidation($item){return true;}
	
	function _isMatching($object)
	{
	}
	
	function _reorder($array)
	{
	}
	
	function Filter($objectArray)
	{
		$result = array();
		for($i;$i< sizeof($objectArray);$i++){
			if($this->_isMatching($objectArray[$i])){
				$result[] = $objectArray[$i];
			}
		}
		return $this->_reorder($result);
	}
	
	function _isStartString($char)
	{
		return $char=="'" || $char=="\"";
	}
	function _isSpace($char)
	{
		return $char=="," ||$char==" " || $char=="\t" || $char=="\r" || $char=="\n" || $char=="\f";
	}
	function _isSeparator($char)
	{
		return in_array($char,$this->separators);
	}
	function _isFunction($operator)
	{
		return in_array(strtolower($operator),$this->functions);
	}
	function _isBinary($operator)
	{
		return in_array(strtolower($operator),$this->binaryOperator);
	}
	function _isLogical($operator)
	{
		return in_array(strtolower($operator),$this->logicalOperator);
	}
	function _isBoolean($operator)
	{
		return in_array(strtolower($operator),$this->trueFalse);
	}
	function _isOrderBy($operator)
	{
		return in_array(strtolower($operator),$this->orderBy);
	}
	function _isGroupBy($operator)
	{
		return in_array(strtolower($operator),$this->groupBy);
	}
	function _isString($operator)
	{
		if($operator[0]==$operator[sizeof($operator)-1]){
			return $this->_isStartString($operator[0]);
		}
		return false;
	}
	function _isNumeric($operator)
	{
		//return is_numeric($operator);
        return preg_match("/^-?([0-9])+([\.|,]([0-9])*)?$/i",$operator);
	}
	function _isField($operator)
	{
		return in_array(strtolower($operator),$this->fields);
	}
	
	function _tokenize($queryString)
	{
		$splitted = array();
		$inString = false;
		$current = "";
		for($i=0;$i< strlen($queryString);$i++){
			$c = $queryString[$i];
			if(!$inString && $this->_isStartString($c)){
				$current = $c;
				$inString = true;
			}else if($inString && $c!=$current[0]){
				$current .= $c;
			}else if($inString && $c==$current[0]){
				$inString = false;
				$current .= $c;
				$splitted[] = $current;
				$current = "";
			}else if($this->_isSeparator($c)){
				if($this->_isSpace($c)){
					if(strlen($current)>0){
						$splitted[] = $current;
						$current = "";
					}
				}else{
					if(strlen($current)>0){
						$splitted[] = $current;
						$current = "";
					}
					$splitted[] = $c;
					$current = "";
				}
			}else{
				$current .= $c;
			}
		}
		if(strlen($current)>0){
			$splitted[] = $current;
			$current = "";
		}
		return $splitted;
	}
	
	function _identify($splitted,$i){
		$temp = array();
		$prev = null;
		$result = new IdentifyResult();
		$isGroup = false;
		for(;$i< sizeof($splitted);$i++){
			$s = $splitted[$i];
			if($this->_isField($s)){
				$o = new Operator();
				$o->Type = "field";
				$o->Value = $s;
				$temp[] = $o;
			}else if($this->_isString($s)){
				$o = new Operator();
				$o->Type = "string";
				$o->Value = substr($s,1,strlen($s)-2);
				$temp[] = $o;
			}else if($this->_isBoolean($s)){
				$o = new Operator();
				$o->Type = "boolean";
				$o->Value = $s=="true";
				$temp[] = $o;
			}else if($this->_isBinary($s)){
				$o = new Operator();
				$o->Type = "binary";
				$o->Value = $s;
				$temp[] = $o;
			}else if($this->_isLogical($s)){
				$o = new Operator();
				$o->Type = "logical";
				$o->Value = $s;
				$temp[] = $o;
			}else if($this->_isOrderBy($s)){
				
				$o = new Operator();
				$o->Type = "orderby";
				$o->Value = $s;
				$temp[] = $o;
			}else if($this->_isGroupBy($s)){
			
				$o = new Operator();
				$o->Type = "groupby";
				$o->Value = $s;
				$temp[] = $o;
			}else if($this->_isFunction($s)){
				$o = new Operator();
				$o->Type = "function";
				$o->Value = $s;
				$temp[] = $o;
			}else if($s=="("){
				$o = new Operator();
				$i++;
				if($prev!=null && ($prev->Type=="function" || $prev->Type=="method")){
					$o = $prev;
				}else{
					$o->Type = "group";
					$o->Value = $s;
					$temp[] = $o;
				}
				$tempIdentify = $this->_identify($splitted,$i);
				
				$o->Children = $tempIdentify->List;
				$i = $tempIdentify->Next;
			}else if($s==")"){
				$result->Next = $i;
				$result->List = $temp;
				return $result;
			}else if($this->_isNumeric($s)){
				$o = new Operator();
				$o->Type = "number";
				$o->Value = $s+0;
				$temp[] = $o;
			}else if($this->externalTypes!=null && $this->externalTypes->IsExternal($s)){
				$o = $this->externalTypes->BuildToken($s);
				if($o==null){
					throw new ParserException("Token '".$s."' not supported by external provider");
				}
				$temp[] = $o;
			}else if(strtolower($s)=="null"){
				$o = new Operator();
				$o->Type = "string";
				$o->Value = "";
				$temp[] = $o;
			}else{
				throw new ParserException("Token '".$s."' not supported parsing");
			}
			$prev = $temp[sizeof($temp)-1];
		}
		$result->Next = $i;
		$result->List = $temp;
		return $result;
	}
	
	function _initializeFields($fieldNames)
	{
		$this->fields = array();
		for($i=0;$i<sizeof($fieldNames);$i++){
			$this->fields[] = strtolower($fieldNames[$i]);
		}
	}
	
	function _isLogicalBlock($identified)
	{
		for($i=0;$i< sizeof($identified);$i++){
			$o = $identified[$i];
			if($o->Type=="logical"){
				return true;
			}
		}
		return false;
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
			if($v==$operator){
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
	
	function _reorderLogicalOperators($identified)
	{
		$result = array();
		for($i=0;$i<sizeof($identified);$i++){
			$o = $identified[$i];
			$t = strtolower($o->Type);
			if($t=="function"){
				$o->Children = $this->_reorderLogicalOperators($o->Children);
			}else if($t=="group"){
				$temp = $this->_reorderLogicalOperators($o->Children);		

				if(sizeof($temp)==1){
					$o = $temp[0];
				}else{
					$o->Children = $temp;
				}
			}
			$result[] = $o;
		}
		$identified = $this->_subRenderLogicalOperators($result,"eq",2);
		$identified = $this->_subRenderLogicalOperators($identified,"neq",2);
		$identified = $this->_subRenderLogicalOperators($identified,"ne",2);
		$identified = $this->_subRenderLogicalOperators($identified,"gt",2);
		$identified = $this->_subRenderLogicalOperators($identified,"gte",2);
		$identified = $this->_subRenderLogicalOperators($identified,"lt",2);
		$identified = $this->_subRenderLogicalOperators($identified,"lte",2);
		
		$identified = $this->_subRenderLogicalOperators($identified,"and",-1);
		$identified = $this->_subRenderLogicalOperators($identified,"or",-1);
		
		return $identified;
	}
	
	public function Parse($queryString,$subject,$externalTypes = null)
	{
		if(strlen($queryString)==0 || $queryString==null){
			$this->parseResult = null;
			return $this->parseResult;
		}
		$fieldNames = $subject;
		if(!is_array($subject)){
			$fieldNames = array_keys(get_object_vars($subject));
		}
		if(sizeof($fieldNames)==0){
			$fieldNames = array_keys(get_class_vars($subject));
		}
		$this->externalTypes = $externalTypes;
		$this->_initializeFields($fieldNames);
		$splitted = $this->_tokenize($queryString);
		
		$notOperator = array();
		$operator = array();
		foreach($splitted as $spl){
			if($this->_isOrderBy($spl) || $this->_isGroupBy($spl)){
				$notOperator[]= $spl;
			}else if(sizeof($notOperator)>0){
				$notOperator[]= $spl;
			}else{
				$operator[]= $spl;
			}
		}
		
		$operatorIdentified = $this->_identify($operator,0)->List;
		$notOperatorIdentified = $this->_identify($notOperator,0)->List;
		
		@$this->_storeOrderByClause($notOperatorIdentified);
		$this->parseResult = @$this->_reorderLogicalOperators($operatorIdentified);
		
		return $this->parseResult;
	}
	
	public function ToMySql()
	{
		
		if($this->parseResult==null || sizeof($this->parseResult)==0){
		
			return "";
		}
		$parseTreeItem = $this->parseResult[0];
		$result = @$this->_toMySql($parseTreeItem);
		
		return $result;
	}
	
	public function _toMySql($parseTreeItem)
	{
		//var_dump($parseTreeItem); echo "\r\n<br>";
		
		$result = "";
		$t = strtolower($parseTreeItem->Type);
		$v = $parseTreeItem->Value;
		$c = $parseTreeItem->Children;
		switch($t){
			case "string":
				return "'".$v."'";
			case "number":
				return $v;
			case "boolean":	
				return $v==false?"false":"true";
		}
		if($t == "function"){
			$params = array();
			for($i=0;$i<sizeof($c);$i++){
				$params[] = $this->_toMySql($c[$i]);
			}
			
			$result = $this->_toMySqlFunction($v,$params);
		}else if($t == "group"){
			$params = array();
			for($i=0;$i<sizeof($c);$i++){
				$params[] = $this->_toMySql($c[$i]);
			}
			$params[]=true;
			$result = $this->_toMySqlFunction("doeq",$params);
		}else if($t=="field"){

			$fo = new Operator();
			$fo->Type = "fieldinstance";
			$fo->Value = $subject->$v;
			$fo->Id = $v;
			return "`".$v."`";
		}else if($this->externalTypes!=null && $this->externalTypes->IsExternal($v)){
			return $parseTreeItem;
		}else{
			throw new ParserException("Token '".$t."' not supported excuting");
		}
		return $result;
	}
	
	function _toMySqlFunction($name,$params)
	{
		/*if($this->externalTypes!=null && $this->externalTypes->CanHandle($name,$params)){
			return $this->externalTypes->$name($params);
		}*/
		
		switch($name){
			case("doand"):
				return "(".join(" and ",$params).")";
			case("door"):
				return "(".join(" or ",$params).")";
			case("doeq"):
				return $params[0]."=".$params[1];
			case("doneq"):
				return $params[0]."<>".$params[1];
			case("dogte"):
				return $params[0].">=".$params[1];
			case("dogt"):
				return $params[0].">".$params[1];
			case("dolt"):
				return $params[0]."<".$params[1];
			case("dolte"):
				return $params[0]."<=".$params[1];
			case("tolower"):
				return "LOWER(".$params[0].")";
			case("toupper"):
				return "UPPER(".$params[0].")";
			case("startswith"):
				return $params[1]." LIKE '%".trim($params[0],"'")."'";
			case("endswith"):
				return $params[1]." LIKE '".trim($params[0],"'")."%'";
			case("substringof"):
				return $params[1]." LIKE '%".trim($params[0],"'")."%'";
			default:
				throw new Exception("Missing operator: ".$name);
		}
	}
	
	public function Execute($subject)
	{
		if($this->parseResult==null || sizeof($this->parseResult)==0){
			return true;
		}
		
		if(!$this->_extraValidation($subject))return false;
		$parseTreeItem = $this->parseResult[0];
		
		$result = @$this->_doExecute($parseTreeItem,$subject);
		return $result->Value;
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
		}else if($this->externalTypes!=null && $this->externalTypes->IsExternal($v)){
			return $parseTreeItem;
		}else{
			throw new ParserException("Token '".$t."' not supported excuting");
		}
		return $result;
	}
	
	
	function _executeFunction($name,$params)
	{
		if($this->externalTypes!=null && $this->externalTypes->CanHandle($name,$params)){
			return $this->externalTypes->$name($params);
		}
		return $this->$name($params);
	}
	
	function substringof($args)
	{	
		$res = BuildBool(contains(strtolower($args[0]->Value),strtolower($args[1]->Value)));
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
				return BuildBool(false);
			}
		}
		return BuildBool(true);
	}
	
	function door($args)
	{
		for($i=0;$i<sizeof($args);$i++){
			if($args[$i]->Value){
				return BuildBool(true);
			}
		}
		return BuildBool(false);
	}
	
	function tolower($args)
	{	
		return BuildItem(strtolower($args[0]->Value),$args[0]->Type,$args[0]->Id);
	}
	
	function toupper($args)
	{	
		return BuildItem(strtoupper($args[0]->Value),$args[0]->Type,$args[0]->Id);
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
		return BuildBool($this->startsWithInt($args[0]->Value,$args[1]->Value));
	}
	
	function endswith($args)
	{	
		return BuildBool($this->endsWithInt($args[0]->Value,$args[1]->Value));
	}
	
	function dosubstringof($args)
	{
		$l=$args[0];
		$r=$args[1];
		$pos = stripos($r->Value, $l->Value);
		if ($pos === false) {
			return BuildBool(false);
		} else {
			return BuildBool(true);
		} 
	}
	
	function doeq($args)
	{
		$l=$args[0];
		$r=$args[1];
		return BuildBool($l->Value == $r->Value);
	}
	
	function doneq($args)
	{
		$l=$args[0];
		$r=$args[1];
		return BuildBool($l->Value != $r->Value);
	}
	
	function done($args)
	{
		$l=$args[0];
		$r=$args[1];
		return BuildBool($l->Value != $r->Value);
	}
	
	function dogt($args)
	{
		$l=$args[0];
		$r=$args[1];
		return BuildBool($l->Value > $r->Value);
	}
	
	function dogte($args)
	{
		$l=$args[0];
		$r=$args[1];
		if($l->Value == $r->Value) return BuildBool(true);
		return BuildBool($l->Value > $r->Value);
	}
	
	function dolt($args)
	{
		$l=$args[0];
		$r=$args[1];
		return BuildBool($l->Value < $r->Value);
	}
	
	function dolte($args)
	{
		$l=$args[0];
		$r=$args[1];
		if($l->Value == $r->Value) return BuildBool(true);
		return BuildBool($l->Value < $r->Value);
	}

	function _storeOrderByClause($identified)
	{
		$result = array();
		$obClause = array();
		$gbClause = array();
		$do =true;
		for($i=0;$i<sizeof($identified) && $do;$i++){
			$val= $identified[$i];
			$t = strtolower($val->Type);
			$v = strtolower($val->Value);
			
			if($t=="orderby" && $v=="orderby"){
				$i++;
				for(;$i<sizeof($identified) && $do;$i++){
					$val= $identified[$i];
					$t = strtolower($val->Type);
					$v = strtolower($val->Value);
					if($t=="groupby" && $v=="groupby"){
						$i++;
						for(;$i<sizeof($identified);$i++){
							$gbClause[]=$identified[$i];
						}
						$do = false;
					}else{
						$obClause[]=$identified[$i];
					}
				}
				$do = false;
			}else{
				$result[]=$val;
			}
		}
		
		if(sizeof($obClause)>0){
			$this->_buildSorter($obClause);
		}
		
		if(sizeof($gbClause)>0){
			$this->_buildGrouper($gbClause);
		}
		return $result;
	}
	
	var $_groupClause = array();
	
	function _buildGrouper($ob)
	{
		$this->_groupClause = array();
		for($i=0;$i<sizeof($ob);$i++){
			$val = $ob[$i];
			$t = strtolower($val->Type);
			$v = $val->Value;
			if($t=="field"){
				$this->_groupClause[] = $v;
			}
		
		}
	}
	
	var $_sortClause = array();
	
	function _buildSorter($ob)
	{
		$this->_sortClause = array();
		for($i=0;$i<sizeof($ob);$i++){
			$val = $ob[$i];
			$t = strtolower($val->Type);
			$v = $val->Value;
			if($t=="field"){
				if(($i+1)==sizeof($ob)){
					$sc = new SortClause();
					$sc->Field = $v;
					$sc->Type = $t;
					$sc->Asc=true;
					$this->_sortClause[] = $sc;
					return;
				}
				$next = $ob[$i+1];
				$nt = strtolower($next->Type);
				$nv = strtolower($next->Value);
				if($nv=="asc"){
					$sc = new SortClause();
					$sc->Field = $v;
					$sc->Type = $t;
					$sc->Asc=true;
					$this->_sortClause[] = $sc;
					$i++;
				}else if($nv=="desc"){
					$sc = new SortClause();
					$sc->Field = $v;
					$sc->Asc=false;
					$sc->Type = $t;
					$this->_sortClause[] = $sc;
					$i++;
				}else{
					$sc = new SortClause();
					$sc->Field = $v;
					$sc->Type = $t;
					$sc->Asc=true;
					$this->_sortClause[] = $sc;
				}
			}
		
		}
	}
	private $_types;
	public function DoSort($subject,$types)
	{
		$this->_types= $types;
		
		if(sizeof($this->_sortClause)==0) return $subject;
		
		usort($subject, array($this, "_doSort"));
		return $subject;
	}
	
	public function _specialMySqlSort($type,$name,$direction)
	{
		return null;
	}
	
	public function _specialMySqlGroup($type,$name)
	{
		return null;
	}
	
	public function DoSortMySql($fieldNames,$fieldTypes)
	{
		$items = array();
		for($i=0;$i<sizeof($fieldNames);$i++){
			$items[strtolower($fieldNames[$i])]=$fieldTypes[$i];
		}
		
		if(sizeof($this->_sortClause)==0) return "";
		$toMerge = array();
		foreach($this->_sortClause as $sc){
			$special = $this->_specialMySqlSort($items[strtolower($sc->Field)],$sc->Field,$sc->Asc?"ASC":"DESC");
			if($special==null || $special==""){
				array_push($toMerge,"`".$sc->Field."` ".($sc->Asc?"ASC":"DESC"));
			}else{
				array_push($toMerge," ".$special."  ");
			}
		}
		
		return " ORDER BY ".join(" , ",$toMerge);
	}
	
	
	//ORDER BY INET_ATON(SUBSTRING_INDEX(CONCAT(versionnumber,'.0.0.0'),'.',4)), versionsuffix
	public function DoGroupByMySql($fieldNames,$fieldTypes)
	{
		$items = array();
		for($i=0;$i<sizeof($fieldNames);$i++){
			$items[strtolower($fieldNames[$i])]=$fieldTypes[$i];
		}
		if(sizeof($this->_groupClause)==0) return "";
		$toMerge = array();
		foreach($this->_groupClause as $sc){
			$special = $this->_specialMySqlGroup($items[strtolower($sc)],$sc);
			if($special==null || $special==""){
				array_push($toMerge,"`".$sc."` ");
			}else{
				array_push($toMerge," ".$special."  ");
			}
		}
		
		$res =  " GROUP BY ".join(" , ",$toMerge);
		
		return $res;
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
		if($this->externalTypes!=null && 
			($this->externalTypes->IsExternal($s)||$this->externalTypes->IsExternal($f))){
			$ft = $this->externalTypes->BuildToken($f);
			$st = $this->externalTypes->BuildToken($s);
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
	
	public function DoGroupBy($subject)
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
}

function BuildItem($value,$type,$id)
{
	$o = new Operator();
	$o->Type = strtolower($type);
	$o->Value = $value;
	$o->Id = $id;
	return $o;
}
?>
