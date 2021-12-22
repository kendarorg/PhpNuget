<?php

namespace lib\db\mysql;

use lib\db\Executor;
use lib\db\parser\InternalTypeBuilder;

class MySqlDbExecutor extends Executor
{


    public function doGroupBy($subject)
    {
        if(sizeof($this->_groupClause)==0) return $subject;
        $result = array();
        $keys = array();
        $counters = array();
        $countersIndex = array();

        foreach($subject as $item){
            $k = "";
            for($i=0;$i<sizeof($this->_groupClause);$i++){
                $fld = $this->_groupClause[$i];
                $k.="#".$item->$fld;
            }
            if(!array_key_exists($k,$keys)){
                $keys[$k] = true;
                $result[]=$item;
                $counters[]=1;
                $countersIndex[$k]=sizeof($counters)-1;
            }else{
                $counters[$countersIndex[$k]]++;
            }
        }

        for($i=0;$i<sizeof($result);$i++){
            $result[$i]->count = $counters[$i];
        }

        return $result;
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
            $row = $so->Field;//$this->fieldsMatch[];
            $asc = $so->Asc;
            $type = $this->_types[strtolower($row)];
            $realRow = $this->fieldsMatch[strtolower($row)];

            $res = $this->_cmp($f->$realRow,$s->$realRow,$asc,$type);
            if($res>0){

                //if($print)echo $f->Title." ".$f->Version.">".$s->Title." ".$s->Version."\r\n";
                return $asc?1:-1;
            }else if($res<0){
                //throw new \Exception("AA");
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
            if($this->externalTypes[$sId]->dolt($arg)->Value) return -1;
            if($this->externalTypes[$sId]->dogt($arg)->Value) return 1;
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






    public function doSort(&$subject)
    {

        if(sizeof($this->_sortClause)==0) return $subject;

        //throw new \Exception(json_encode($subject));
        usort($subject, array($this, "_doSort"));


        return $subject;
    }

    protected function executeFunctionInt($name, $params)
    {
        $vals = array();
        foreach ($params as $param){
            if($param->Type=="fieldinstance"){
                $vals[]=$param->Id;
            }else{
                $vals[]=$param->Value;
            }
        }
        switch($name){
            case("doand"):
                $result =  "(".join(" and ",$vals).")";
                break;
            case("door"):
                $result =  "(".join(" or ",$vals).")";
                break;
            case("doeq"):
                $result =  $vals[0]."=".$vals[1];
                break;
            case("doneq"):
                $result =  $vals[0]."<>".$vals[1];
                break;
            case("dogte"):
                $result =  $vals[0].">=".$vals[1];
                break;
            case("dogt"):
                $result =  $vals[0].">".$vals[1];
                break;
            case("dolt"):
                $result =  $vals[0]."<".$vals[1];
                break;
            case("dolte"):
                $result =  $vals[0]."<=".$vals[1];
                break;
            case("tolower"):
                $result =  "LOWER(".$vals[0].")";
                break;
            case("toupper"):
                $result =  "UPPER(".$vals[0].")";
                break;
            case("startswith"):
                $result =  $vals[1]." LIKE '%".trim($vals[0],"'")."'";
                break;
            case("endswith"):
                $result =  $vals[1]." LIKE '".trim($vals[0],"'")."%'";
                break;
            case("substringof"):
                $result =  $vals[1]." LIKE '%".trim($vals[0],"'")."%'";
                break;
            default:
                throw new Exception("Missing operator: ".$name);
        }
        return InternalTypeBuilder::buildItem($result,"query","id");
    }

    protected function makeString($parseTreeItem)
    {
        $v = $parseTreeItem->Value;
        return InternalTypeBuilder::buildItem("'".$v."'","query","id");
    }

    protected function makeNumber($parseTreeItem)
    {
        $v = $parseTreeItem->Value;
        return InternalTypeBuilder::buildItem($v,"query","id");
    }

    protected function makeBoolean($parseTreeItem)
    {
        $v = $parseTreeItem->Value;
        $v=  ($v==false)?"false":"true";

        return InternalTypeBuilder::buildItem($v,"query","id");
    }
}