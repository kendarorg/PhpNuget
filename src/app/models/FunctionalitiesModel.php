<?php

class FunctionalitiesModel extends BaseModel
{
    protected function getTableName(){return "functionalities";}
    protected function getIdColumns(){return ["functionality","id"];}
    public function getById($functionality,$id){
        return $this->getByIdGeneric(true,$functionality,$id);
    }

    public function delete($functionality,$id){
        $this->deleteGeneric($functionality,$id);
    }

    protected function getCustomAutoIncrementColumn(){ return null;}
    protected  function getDefaultOrderBy(){return ['functionality asc', 'name asc'];}

    protected function getSearchTerms()
    {
        return [
            "functionality"=>[],"name"=>[],"value"=>[],"id"=>[]
        ];
    }

    protected function getUpdateableFields(){
        return $this->setupDefaultUpdateable(
            "description","name","value"
        );
    }
    public function getFunctionalities($functionality){
        $db = $this->getDbConnection();
        return $this->audit->fetchAll($this,"GET_FUNCTIONALITIES",$db,
            "SELECT functionality,id,name,description FROM {$this->getTableName()} WHERE functionality like CONCAT(?,'%') GROUP BY functionality",
            $functionality);
    }

    public function getFunctionalityItems($functionality){
        $db = $this->getDbConnection();
        return $this->audit->fetchAll($this,"GET_FUNCTIONALITIES",$db,
            "SELECT functionality,id,name,description FROM {$this->getTableName()} WHERE functionality = ? order by name asc",
            $functionality);
    }
}
