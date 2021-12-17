<?php

namespace lib\nuget;

use lib\db\parser\Operator;
use lib\db\SpecialFieldType;
use lib\utils\StringUtils;

class NugetVersionType extends SpecialFieldType
{
    public function isExternal($token)
    {
        return $this->_isVersion($token);
    }

    public function buildToken($token)
    {
        $o = null;
        if($this->_isVersion($token)){
            $o = new Operator();
            $o->Type = "version";
            $o->Value = trim($token,"'\"");
        }
        return $o;
    }


    public function canHandle($name,$params)
    {
        if(sizeof($params)!=2) return false;

        $tl = strtolower($params[0]->Type);
        $tr = strtolower($params[1]->Type);

        if($name == "dogt" ||$name == "dogte" ||$name == "dolt" ||$name == "dolte"){
            //if($tl!=$tr) return false;
            if($tl=="version") return true;
            if($tr=="version") return true;
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

    private function _isVersion($token)
    {
        $token = trim($token,"'");
        if(strlen($token)==0)return false;

        $la= array();
        $tmp = StringUtils::indexOf($token,"-");
        if($tmp>0){
            $la[] = substr($token,0,$tmp);
            $la[] = substr($token,$tmp+1);
        }else{
            $la[] = $token;
        }

        $result = explode(".",$la[0]);
        $size = sizeof($result);
        if($size<=1 || $size >4) return false;

        for($i=0;$i<($size-1);$i++){
            if(!$this->_isInteger($result[$i])){
                return false;
            }
        }

        return true;
    }


    private function _isInteger($operator)
    {
        return preg_match('/^([0-9])+$/i',$operator);
    }
    // _compare(a,b) <0   => a<b
    // _compare(a,b) >0   => a>b
    // _compare(a,b) =0   => a=b
    private function _compare($l,$r)
    {
        $la= array();
        $tmp = indexOf($l,"-");
        if($tmp>0){
            $la[] = substr($l,0,$tmp);
            $la[] = substr($l,$tmp+1);
        }else{
            $la[] = $l;
        }
        $ra= array();
        $tmp = indexOf($r,"-");
        if($tmp>0){
            $ra[] = substr($r,0,$tmp);
            $ra[] = substr($r,$tmp+1);
        }else{
            $ra[] = $r;
        }
        $numericCompare = $this->_compareNumericVersion($la[0],$ra[0]);

        if($numericCompare!=0) return $numericCompare;

        if(sizeof($la)>sizeof($ra)){
            return -1;
        }else if(sizeof($la)<sizeof($ra)){
            return 1;
        }

        return strcasecmp ($la[1],$ra[1]);
    }

    private function _compareNumericVersion($l,$r)
    {
        $aVersion = explode(".",strtolower($l));
        $bVersion = explode(".",strtolower($r));
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
            }
        }

        if(sizeof($aVersion)<sizeof($bVersion)) return -1;
        if(sizeof($aVersion)>sizeof($bVersion)) return 1;
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
        return $this->dogt($args);
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
        return $this->dolt($args);
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