<?php

require_once("../config.inc");

class FunctionalitiesApi extends BaseApis
{
    var $model;
    var Permissions $permissions;

    var $labels;
    public function __construct()
    {
        parent::__construct();
        $this->model = GlobalRegistry::get("FunctionalitiesModel");
        $this->permissions = $this->auth->loadPermissions('functionalities');
        $this->labels=[];
    }

    function apiCallPut(){
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData();
        $this->model->update($data);
        $this->sender->sendSuccessResponse($data);
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
        $functionality = $this->getParamOrDefault("functionality");
        $id = $this->getParamOrDefault("id");
        $result = $this->model->delete($functionality, $id);
        $this->sender->sendSuccessResponse();
    }

    function apiCallGet(){
        $this->auth->userMustBeLoggedIn();
        $functionality = $this->getParamOrDefault("functionality");
        $id = $this->getParamOrDefault("id");
        if($functionality==null || $id==null) {
            $items = $this->model->getAll();

            $items = [
                'items' => $items,
                'labels' => $this->labels,
                'permissions' => $this->permissions->value(),
            ];
            $this->sender->sendSuccessResponse($items);
        }else{
            $data = $this->model->getById( $functionality,$id);
            $result = [
                'item'=>$data,
                'labels'=>$this->labels,
                'permissions'=>$this->permissions->value(),
            ];
            $this->sender->sendSuccessResponse($result);
        }
    }

    function apiCallFunctionalities(){
        $this->auth->userMustBeLoggedIn();
        $data = $this->getParamOrDefault("functionality","");
        $items=[];
        if(strlen($data)>3){
            foreach ($this->model->getFunctionalities($data) as $item){
                $items[]=[
                    'label'=>$item['functionality'],
                    'value'=>$item['functionality'],
                ];
            };
        }
        $result = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    function apiCallFunctionalityItems(){
        $this->auth->userMustBeLoggedIn();
        $data = $this->getParamOrDefault("functionality","");
        $prefix = $this->getParamOrDefault("prefix","");
        $items=[];
        if(strlen($data)>3){
            foreach ($this->model->getFunctionalityItems($data) as $item){
                $itemId = $item['id'];
                if($prefix===""){
                    if(stripos($itemId,"/")>0){
                        continue;
                    }
                }else{
                    $founded = stripos($itemId,$prefix."/");
                    if($founded===false ||$founded!=0){
                        continue;
                    }
                }
                $items[]=[
                    'label'=>$item['name'],
                    'value'=>$itemId,
                ];
            };
        }
        $result = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }
    function apiCallPostSearch(){
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData('SearchQuery');
        $items=$this->model->search($data);
        $this->purgeItems($items,"created_at","updated_at");
        if($data->format==="xls"){
            $this->sender->sendSimpleXLSResponse($items,$this->labels,"functionalities");
        }
        $result = [
            'items'=>$items,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }
}

$api = new FunctionalitiesApi();
$api->handle();