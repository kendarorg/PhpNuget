<?php


require_once("../config.inc");

class UsersApi extends BaseApis
{
    var $model;
    var Permissions $permissions;

    var $labels;


    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->model = GlobalRegistry::get("UsersModel");
        $this->permissions = $this->auth->loadPermissions('users');
        $this->labels = [
            "provincia" => translateHt("PROVINCE"),
            "fax" => translate("fax"),
            "code"=>translate("code"),
            "ragioneSociale"=>translateHt("name"),
            "codiceFiscale"=>translateHt("tax_code"),
            "partitaIva"=>translateHt("vat_number"),
            "indirizzo"=>translateHt("address"),
            "citta"=>translateHt("town"),
            "cap"=>translateHt("post_code"),
            "username" => translate("username"),
            "telefono" => translate("phone"),
            "email" => translate("email"),
            "contacts" => translate("contacts"),
            "notes" => translate("NOTES"),
            "livello" => translate("level"),
            "relationKind" => translateHt("kind"),
            "role" => translateHt("role"),
            "description" => translateHt("DESCRIPTION"),
            "tariffaOraria" => translateHt("hourly_rate"),
            "locked" => translateHt("locked"),
            "country" => translateHt("COUNTRY"),
            "errors" => translateHt("ERRORS"),
            "kind" => translateHt("KIND"),
            'id' => translateHt("id"),
        ];
    }

    public function apiCallPutChangePassword(){
        $this->auth->userMustBeLoggedIn();
        $id = $this->getParamOrDefault("id");
        $currentUser = $this->auth->getUser();

        $newPassword = $this->getJsonPostData()['password'];

        $isOwn = $currentUser["id"] == $id;
        if(!$this->permissions->canRead() && !$isOwn){
            $this->permissions->canReadThrow();
        }
        $this->model->updatePassword($id,$newPassword);
        $this->sender->sendSuccessResponse();
    }

    public function apiCallGet(){
        $this->auth->userMustBeLoggedIn();
        $id = $this->getParamOrDefault("id");
        $currentUser = $this->auth->getUser();
        $isOwn = $currentUser["id"] == $id;
        if(!$this->permissions->canRead() && !$isOwn){
            $this->permissions->canReadThrow();
        }

        $data = $this->model->getById( $id);
        if(!$this->permissions->canRead() && $isOwn){
            $this->purgeItem($data,"permissions","tariffaOraria","role","livello","notes","locked");
        }
        $this->purgeItem($data,"password");
        $result = [
            'item'=>$data,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    function apiCallPostSearch(){
        $items =[];
        if(!$this->permissions->canRead()){
            $currentUser = $this->auth->getUser();
            $data = $this->model->getById( $currentUser["id"]);
            $this->purgeItem($data,"permissions",
                "tariffaOraria","password","role","livello","notes","locked");
            $items[]=$data;
        }else {
            $this->permissions->canReadThrow();
            $data = $this->getJsonPostData('SearchQuery');
            if (array_key_exists('locked', $data->searchTerms)) {
                if ($data->searchTerms['locked'] === "" || $data->searchTerms['locked'] === null) {
                    unset($data->searchTerms['locked']);
                }
            }

            $items = $this->model->search($data);
        }
        $this->purgeItems($items,"password");
        $this->purgeItems($items,"created_at","updated_at");
        if($data->format==="xls"){
            $this->purgeItems($items,"relationKindOther","permissions");
            $this->sender->sendSimpleXLSResponse($items,$this->labels,"users");
        }
        $result = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    public function apiCallWorkers(){
        $this->auth->userMustBeLoggedIn();
        $query = $this->getParamOrDefault("query");
        $permission = $this->getParamOrDefault("permission");
        $exact = filter_var($this->getParamOrDefault("exact","false"), FILTER_VALIDATE_BOOLEAN);
        $active = filter_var($this->getParamOrDefault("active","true"), FILTER_VALIDATE_BOOLEAN);
        $items = $this->model->getComboWorkers($query, $permission, $exact,$active);
        $this->purgeItems($items, "password","permissions");
        if(!$this->permissions->canCreate()) {
            $this->purgeItems($items, "tariffaOraria","role","livello","notes","locked");
        }
        $items = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($items);

    }
    function apiCallPutPasswordChange(){
        $this->auth->userMustBeLoggedIn();
        $id = $this->getParamOrDefault("id");
        $currentUser = $this->auth->getUser();
        $data = $this->getJsonPostData();
        $isOwn = $currentUser["id"] == $id;
        if(!$this->permissions->canCreate() && $isOwn) {
            $this->model->updatePassword($currentUser["id"],$data["password"]);
        }else{
            $this->permissions->canCreateThrow();
            $this->model->updatePassword($id,$data["password"]);
        }
    }

    function apiCallPut(){
        $this->auth->userMustBeLoggedIn();




        $currentUser = $this->auth->getUser();
        $data = $this->getJsonPostData();

        $id = $this->getParamOrDefault("id");
        $currentUser = $this->auth->getUser();
        $isOwn = $currentUser["id"] == $id;
        if(!$this->permissions->canCreate() && !$isOwn){
            $this->permissions->canCreateThrow();
        }

        $requestedUser = $this->model->getById( $data["id"]);
        $isOwn = $currentUser["id"] == $data["id"];
        if(!$this->permissions->canCreate() && $isOwn) {
            $data['permissions'] = $requestedUser["permissions"];
            $data['role'] = $requestedUser["role"];
            $data['livello'] = $requestedUser["livello"];
            $data['notes'] = $requestedUser["notes"];
            $data['tariffaOraria'] = $requestedUser["tariffaOraria"];
            $data['code'] = $requestedUser["code"];
            $data['username'] = $requestedUser["username"];
            $data['password'] = $requestedUser["password"];
            $data['locked'] = $requestedUser["locked"];
        }else {
            $this->permissions->canCreateThrow();
        }
        unset($data["password"]);
        $this->model->update($data);

        if(!$this->permissions->canCreate() && $isOwn) {
            $this->purgeItem($data,"permissions","locked",
            "notes","tariffaOraria","password","livello","role");
        }
        $this->sender->sendSuccessResponse($data);
    }

    function apiCallPost(){
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData();
        $this->model->create($data);
        $this->sender->sendSuccessResponse($data);
    }

    function apiCallPostSimulate(){
        $this->auth->userMustBeLoggedIn();
        $data = $this->getJsonPostData();
        $id = $data['id'] ?? null;
        if(!$id){
            $this->sender->sendErrorResponse("ERROR_GENERIC", 400);
            return;
        }
        $currentUser = $this->auth->getUser();
        $this->audit->audit("SIMULATE_USER", "USER", [
            'tosimulate' => $id,
            'simulator'  => $currentUser['id'],
        ]);
        $simulatePerm = $this->auth->loadPermissions('simulate');
        $simulatePerm->canCreateThrow();

        $userToSimulate = $this->model->getById($id);
        if(!$userToSimulate){
            $this->sender->sendErrorResponse("ERROR_NOT_FOUND", 404);
            return;
        }
        if($userToSimulate['role'] === 'SuperAdmin'){
            $this->audit->auditError("SIMULATE_USER", "USER", [
                'tosimulate' => $id,
                'simulator'  => $currentUser['id'],
                'reason'     => 'Cannot simulate SuperAdmin',
            ]);
            $this->sender->sendErrorResponse("NOT_AUTHORIZED", 403);
            return;
        }
        if($currentUser['role'] !== 'Admin' && $currentUser['role'] !== 'SuperAdmin'
            && $userToSimulate['role'] === 'Admin'){
            $this->audit->auditError("SIMULATE_USER", "USER", [
                'tosimulate' => $id,
                'simulator'  => $currentUser['id'],
                'reason'     => 'Only admins can simulate other admins',
            ]);
            $this->sender->sendErrorResponse("NOT_AUTHORIZED", 403);
            return;
        }
        $this->auth->simulate($userToSimulate);
        $this->sender->sendSuccessResponse();
    }
    function apiCallDelete()
    {
        $this->permissions->canDeleteThrow();
        $id = $this->getParamOrDefault("id");
        $this->model->delete( $id);
        $this->sender->sendSuccessResponse();
    }
}

$api = new UsersApi();
$api->handle();