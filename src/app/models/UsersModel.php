<?php

class UsersModel extends BaseModel
{
    protected function getTableName(){return "users";}
    protected function getIdColumns(){return ["id"];}
    public function getById($id){
        $result =  $this->getByIdGeneric(true,$id);
        return $result;
    }

    public function delete($id){
        $this->deleteGeneric($id);
    }

    protected function getCustomAutoIncrementColumn(){ return "id";}
    protected  function getDefaultOrderBy(){return ['username asc'];}
    public function update(&$data)
    {
        $data["relationKind"]="Altro";
        $data["relationKindOther"]="";
        unset($data["password"]);
        parent::update($data);
    }
    public function create(&$data){
        $data["relationKind"]="Altro";
        $data["relationKindOther"]="";
        $data["password"] = password_hash($data['password'], PASSWORD_DEFAULT);
        parent::create($data);
    }
    protected function getSearchTerms()
    {
        return [
            "role"=>[],
            "combo"=>[
                "users.ragioneSociale_like",
                "users.username_like",
                "users.code_eq",
                "users.id_eq",
            ],
            "names"=>[
                "users.username_like",
                "users.email_like",
                "users.ragioneSociale_like",
            ]
        ];
    }

    public function updatePassword($id,$password)
    {
        $user = $this->getById($id);
        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
        $user["relationKind"]="Altro";
        $user["relationKindOther"]="";
        parent::update($user);
    }
    protected function getJsonFields()
    {
        return ["permissions"];
    }

    protected function getUpdateableFields(){
        $res = $this->setupDefaultUpdateable(
            "username","email","code","ragioneSociale","codiceFiscale","partitaIva",
            "indirizzo","citta","provincia","cap","telefono","fax","relationKind",
            "relationKindOther","livello","tariffaOraria","role","notes","locked",
            "country","permissions","password"
        );
        $res['country']=['default'=>'it'];
        $res['tariffaOraria']=['default'=>'0'];
        $res['role']=['default'=>''];
        return $res;
    }

    function getComboWorkers($query, $permission,$exact, $active){
        if(!$active){
            $active=null;
        }else{
            $active=false;
        }
        if(is_string($permission) && $permission!=null && strlen($permission)>0){
            $permission = explode("|", $permission);
        }
        if(is_array($permission) && count($permission)==0){
            $permission = null;
        }

        $searchQuery = new SearchQuery();
        $searchQuery->fields = ["users.id",
            "CONCAT('(',users.code,') ',users.username) as username",
            "CONCAT('(',users.code,') ',users.ragioneSociale) as ragioneSociale",
            "users.tariffaOraria",
            "users.role"];
        $searchQuery->orderBy = ["users.username asc"];

        if($exact && $this->isNumericLike($query)){
            $searchQuery->searchTerms = [
                "users.id" => $query,
            ];
        }else {
            $searchQuery->searchTerms = [
                "users.locked" => $active,
                "combo" => $query
            ];
        }
        if($this->isNumericLike($query)){
            $searchQuery->searchTerms["users.id"]=$query;
        }else{
            $searchQuery->searchTerms["combo"]=$query;
        }
        if($permission!=null){
            $searchQuery->join =$this->getTableName()." LEFT JOIN permissions ON users.role = permissions.role ";
            $searchQuery->searchTerms["permissions.id_in"]=$permission;
        }
        $items = $this->search($searchQuery);
        return $items;
    }
}
