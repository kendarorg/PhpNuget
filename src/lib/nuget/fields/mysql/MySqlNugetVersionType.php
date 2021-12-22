<?php

namespace lib\nuget\fields\file;

use lib\db\parser\InternalTypeBuilder;
use lib\db\parser\Operator;
use lib\db\utils\SpecialFieldType;
use lib\utils\StringUtils;
use function contains;

class MySqlNugetVersionType extends SpecialFieldType
{
    public function isComposite(){
        $args = func_get_args();
        if(is_array($args[0])){
            $args = $args[0];
        }
        foreach ($args as $arg){
            $id = strtolower($arg->Id);
            if( "version"==$id) {
                return true;
            }
        }
        return false;
    }

    public function isExternal()
    {
        return $this->_isVersion(func_get_args()[0]);
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

        /* WHY THE HECK THIS //TODO
         * if($name == "substringof"){
            if($tl=="field" && strtolower($params[0]->Value)=="dependencies"){
                return true;
            }else if($tr=="field" && strtolower($params[1]->Value)=="dependencies"){
                return true;
            }
        }*/
        return false;
    }

    private function _isVersion($token)
    {
        if(!is_string($token))return false;
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

    public function dolt($args)
    {
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = "SEMVER_LT(".$lv.",".$rv.")";
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    public function dogte($args)
    {
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = "SEMVER_GTE(".$lv.",".$rv.")";
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    public function dogt($args)
    {
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = "(SEMVER_GTE(".$lv.",".$rv.") AND ".$lv."!=".$rv.")";
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    public function dolte($args)
    {
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = "(SEMVER_LT(".$lv.",".$rv.") OR ".$lv."=".$rv.")";
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    public function substringof($args)
    {
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = $rv." LIKE '%".trim($lv,"'")."%'";
    }

    public function doeq($args){
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = $lv."=".$rv;
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    public function doneq($args){
        $l=$args[0];
        $r=$args[1];
        $lv = ($l->Type=="fieldinstance")?$l->Id:$l->Value;
        $rv = ($r->Type=="fieldinstance")?$r->Id:$r->Value;
        $v = $lv."!=".$rv;
        return InternalTypeBuilder::buildItem($v,"query","id");
    }
}