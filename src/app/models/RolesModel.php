<?php

class RolesModel extends BaseModel
{
    protected function getTableName(){return "roles";}
    protected function getIdColumns(){return ["id"];}
    public function getById($id){
        return $this->getByIdGeneric(true,$id);
    }

    public function delete($id){
        $this->deleteGeneric($id);
    }

    protected function getCustomAutoIncrementColumn(){ return null;}
    protected  function getDefaultOrderBy(){return ['name asc'];}

    protected function getSearchTerms()
    {
        return [
            "name_desc"=>[
                "name_like","description_like"
    ]
        ];
    }

    protected function getUpdateableFields(){
        $res = $this->setupDefaultUpdateable(
            "name","description"
        );
        return $res;
    }

    protected function updateDependencies(&$data){
        $this->deleteChildrens($data);
        foreach($data['permissions'] as $permission){
            $sql = "INSERT INTO permissions (role, id, permissions) 
                    VALUES (?, ?, ?)";

            $this->audit->execute($this,"DELETE_PERMISSIONS",$this->db,$sql,[
                $data['id'],
                $permission['id'],
                $permission['permissions']
                ]);
        }
    }

    protected function deleteChildrens(&$result)
    {
        $sql = "DELETE FROM permissions WHERE role = ?";

        $this->audit->execute($this,"DELETE_PERMISSIONS",$this->db,$sql,[$result['id']]);

    }

    protected function loadDependencies(&$result)
    {
        $sql = "SELECT role,id,permissions FROM permissions WHERE role = ?";

        $result['permissions'] = $this->audit->fetchAll($this,"LOAD_PERMISSIONS",$this->db,$sql,[$result['id']]);
    }
}
