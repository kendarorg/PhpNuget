<?php

namespace lib\db\mysql;

use lib\db\Executor;
use lib\db\parser\InternalTypeBuilder;

class MySqlDbExecutor extends Executor
{

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

    public function doSort(&$subject)
    {

        if(sizeof($this->_sortClause)==0) return $subject;

        $print = false;

        $result = array();
        for($i=0;$i<sizeof($this->_sortClause);$i++){
            $so = $this->_sortClause[$i];
            $row = $so->Field;//$this->fieldsMatch[];
            foreach ($this->fieldsMatch as $real){
                if(strtolower($real)==strtolower($so->Field)){
                    $row = $real;
                }
            }
            $how = $so->Asc?"ASC":"DESC";
            $result[] = $row." ".$how;
        }


        return join(",",$result);
    }


    public function doGroupBy($subject)
    {
        if(sizeof($this->_groupClause)==0) return "";

        $print = false;

        $result = array();
        for($i=0;$i<sizeof($this->_groupClause);$i++){
            $so = $this->_groupClause[$i];
            foreach ($this->fieldsMatch as $real){
                if(strtolower($real)==strtolower($so)){
                    $row = $real;
                }
            }
            $result[] = $row;
        }


        return join(",",$result);
    }
}