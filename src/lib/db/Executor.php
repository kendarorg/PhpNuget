<?php

namespace lib\db;

use lib\db\parser\Operator;

class Executor
{

    protected  $parseResult;
    protected $externalTypes;
    protected  $_groupClause;
    protected  $groupBy;
    protected  $_sortClause;
    protected $_types;
    protected $fieldsMatch;

    /**
     * @param mixed $parseResult
     * @param $externalTypes
     * @param array $_groupClause
     * @param array $groupBy
     * @param array $_sortClause
     * @param $_types
     * @return void
     */
    public function initialize(mixed $parseResult, $externalTypes, array 
    $_groupClause, array $groupBy, array $_sortClause, $_types,$fieldsMatch)
    {
        $this->fieldsMatch = $fieldsMatch;
        $this->parseResult = $parseResult;
        $this->externalTypes = $externalTypes;
        $this->_groupClause = $_groupClause;
        $this->groupBy = $groupBy;
        $this->_sortClause = $_sortClause;
        $this->_types = $_types;
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


    public function _doExecute($parseTreeItem,$subject)
    {
        $t = strtolower($parseTreeItem->Type);
        $v = $parseTreeItem->Value;
        $c = $parseTreeItem->Children;
        switch($t){
            case "string":
                return $this->makeString($parseTreeItem);
            case "number":
                return $this->makeNumber($parseTreeItem);
            case "boolean":
                return $this->makeBoolean($parseTreeItem);
        }
        if($t == "function"){
            $params = array();
            for($i=0;$i<sizeof($c);$i++){

                $params[] = $this->_doExecute($c[$i],$subject);
            }

            //$v = the function name
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
            throw new \Exception("Token '".$t."' not supported excuting (2)");
        }
        return $result;
    }


    protected function isExternalType($s)
    {
        if($this->externalTypes!=null) {
            for ($i = 0; $i < sizeof($this->externalTypes); $i++) {
                if ($this->externalTypes[$i]->isExternal($s)) {
                    return $i;
                }
            }
        }
        return -1;
    }

    private function _executeFunction($name,$params)
    {
        if($this->externalTypes!=null) {
            foreach ($this->externalTypes as $type) {
                if($type->canHandle($name,$params)){
                    return $type->$name($params);
                }
                if ($type->isComposite($params)){
                    return $type->$name($params);
                }
            }
        }
        return $this->executeFunctionInt($name,$params);
    }

    protected function executeFunctionInt($name, $params)
    {
        return null;
    }

    protected function makeString($parseTreeItem)
    {
        return $parseTreeItem;
    }

    protected function makeNumber($parseTreeItem)
    {
        return $parseTreeItem;
    }

    protected function makeBoolean($parseTreeItem)
    {
        return $parseTreeItem;
    }
}