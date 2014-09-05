<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");
//http://localhost:8020/phpnuget/api/packages/?Query=substringof%28%27CoroutineCache%27,Dependencies%29
class PhpNugetExternalTypes
{
	public function IsExternal($token)
	{
		return $this->_isVersion($token);
	}
	public function BuildToken($token)
	{
		$o = null;
		if($this->_isVersion($token)){
			$o = new Operator();
			$o->Type = "version";
			$o->Value = trim($token,"'\"");
		}
		
		return $o;
	}
	
	private function _isVersion($token)
	{
		$result = explode(".",$token);
		$size = sizeof($result);
		if($size<=1 && $size >5) return false;
		
		for($i=0;$i<($size-1);$i++){
			if(!$this->_isInteger($result[$i])){
				return false;
			}
		}
		$last = $result[$size-1];
		if($size==4 && $this->_isInteger($last)){
			return true;
		}else if($size==5){
			return true;
		}else if(!$this->_isInteger($last)){
			return false;
		}
		return true;
	}
	
	public function CanHandle($name,$params)
	{
		
		if(sizeof($params)!=2) return false;
		
		$tl = strtolower($params[0]->Type);
		$tr = strtolower($params[1]->Type);
		
		if($name == "dogt" ||$name == "dogte" ||$name == "dolt" ||$name == "dolte"){
			if($tl!=$tr) return false;
			if($tl=="version") return true;
		}
		
		if($name == "substringof"){
			if($tl=="field" && strtolower($params[0]->Value)=="dependencies"){
				return true;
			}else if($tr=="field" && strtolower($params[1]->Value)=="dependencies"){
				return true;
			}
		}
		return false;
	}
	
	
	function _isInteger($operator)
	{
		return preg_match('/^([0-9])+$/i',$operator);
	}
	// _compare(a,b) <0   => a<b
	// _compare(a,b) >0   => a>b
	// _compare(a,b) =0   => a=b
	function _compare($l,$r)
	{
		$aVersion = explode(".",str_replace("-","",strtolower($l)));
		$bVersion = explode(".",str_replace("-","",strtolower($r)));
		for($i=0;$i<sizeof($aVersion) && $i<sizeof($bVersion);$i++){
			$aCur = $aVersion[$i];
			$bCur = $bVersion[$i];
			if($this->_isInteger($aCur)&&$this->_isInteger($bCur)){
				$res = $aVersion[$i]-$bVersion[$i];
				if($res!=0) return $res; 
			}else if(!$this->_isInteger($aCur)&&$this->_isInteger($bCur)){
				return 1;
			}else if($this->_isInteger($aCur)&&!$this->_isInteger($bCur)){
				return -1;
			}else{
				$aCur = PhpNugetObjectSearch::$letterVersion[$aCur];
				$bCur = PhpNugetObjectSearch::$letterVersion[$bCur];
				$res = $aCur -$bCur;
				if($res!=0) return $res; 
			}
		}
		return 0;
	}
	
	public function dogt($args)
	{
		$l=$args[0];
		$r=$args[1];
		if($l==null && $r!=null) return BuildBool(false);
		if($r==null && $l!=null) return BuildBool(true);
		return BuildBool($this->_compare($l->Value,$r->Value)>0);
	}
	
	public function dogte($args)
	{
		$l=$args[0];
		$r=$args[1];
		if($l->Value == $r->Value) return BuildBool(true);
		return dogt($args);
	}
	
	public function dolt($args)
	{
		$l=$args[0];
		$r=$args[1];
		
		if($l==null && $r!=null) return BuildBool(true);
		if($r==null && $l!=null) return BuildBool(false);
		return BuildBool($this->_compare($l->Value,$r->Value)<0);
	}
	
	public function dolte($args)
	{
		$l=$args[0];
		$r=$args[1];
		if($l->Value == $r->Value) return BuildBool(true);
		return dolt($args);
	}
	
	public function substringof($args)
	{
		$l=null;
		$r=null;
		
		if(is_array($args[1]->Value)){
			$l = $args[0]->Value;
			$r = serialize($args[1]->Value);
		}else if(is_array($args[0]->Value)){
			$r = serialize($args[0]->Value);
			$l = $args[0]->Value;
		}else {
			return BuildBool(false);
		}
		
		return contains($l,$r);
	}
	/*
	public function BuildValue($id,$value)
	{
		echo $value;
		$fo = new Operator();
		$fo->Type = "fieldinstance";
		$fo->Value = $value;
		$fo->Id = $id;
		return $fo;
	}*/
}

class PhpNugetObjectSearch extends ObjectSearch
{
	static $letterVersion = array("prealpha"=>0,"alpha"=>1,"beta"=>2,"releasecandidate"=>3);
	public static function IsPreRelease($version)
	{
		$version = strtolower($version);
		foreach(PhpNugetObjectSearch::$letterVersion as $key){
			if(ends_with($version,$key)){
				return true;
			}
		}
		return false;
	}
	public function Parse($queryString,$fieldNames,$externalTypes = null)
	{
		return parent::Parse($queryString,$fieldNames,new PhpNugetExternalTypes());
	}
	
}

?>