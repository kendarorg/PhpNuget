<?php


require_once("../config.inc");

class RolesApi extends BaseApis
{
    var $model;
    var Permissions $permissions;

    var $labels;


    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->model = GlobalRegistry::get("RolesModel");
        $this->permissions = $this->auth->loadPermissions('roles');
        $this->labels=[];
    }

    public function apiCallGet(){

        $this->auth->userMustBeLoggedIn();
        $id = $this->getParamOrDefault("id");
        if($id==null) {
            $items = $this->model->getAll();
            if(!$this->permissions->canCreate()){
                $this->purgeItems($items,"permissions");
            }

            $items = [
                'items' => $items,
                'labels' => $this->labels,
                'permissions' => $this->permissions->value(),
            ];
            $this->sender->sendSuccessResponse($items);
        }else{
            $data = $this->model->getById( $id);
            if(!$this->permissions->canCreate()){
                $this->purgeItem($data,"permissions");
            }
            $result = [
                'item'=>$data,
                'labels'=>$this->labels,
                'permissions'=>$this->permissions->value(),
            ];
            $this->sender->sendSuccessResponse($result);
        }
    }

    function apiCallPut(){
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData();
        $this->model->update($data);
        $this->sender->sendSuccessResponse($data);
    }

    function apiCallPostSearch(){
        $this->permissions->canReadThrow();
        $data = $this->getJsonPostData('SearchQuery');
        $items=$this->model->search($data);
        if(!$this->permissions->canCreate()){
            $this->purgeItems($items,"permissions");
        }
        $this->purgeItems($items,"created_at","updated_at");
        if($data->format==="xls"){
            $this->sender->sendSimpleXLSResponse($items,$this->labels,"roles");
        }
        $result = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    function apiCallPost(){
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData();
        $this->model->create($data);
        $this->sender->sendSuccessResponse($data);
    }
    function apiCallDelete()
    {
        $this->permissions->canDeleteThrow();
        $id = $this->getParamOrDefault("id");
        $this->model->delete( $id);
        $this->sender->sendSuccessResponse();
    }
}

$api = new RolesApi();
$api->handle();