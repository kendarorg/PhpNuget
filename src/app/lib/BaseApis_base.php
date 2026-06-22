<?php


require_once "utils/Validator.php";

abstract class BaseApis
{

    protected Audit $audit;
    protected Auth $auth;
    protected $postData = null;
    private $jsonPostData =null;
    private $typedPostData = null;
    protected Validator $validator;
    protected Sender $sender;
    protected Logger $log;
    function __construct()
    {
        $this->audit = GlobalRegistry::get('Audit');
        $this->auth = GlobalRegistry::get('Auth');
        $this->validator = GlobalRegistry::getTransient('Validator',$this);
        $this->sender = GlobalRegistry::get('Sender');
        $this->log = LogManager::getLogger(get_class($this));
    }

    function trimItems(&$items,$maxLength=20)
    {
        for($i=0;$i<count($items);$i++){
            $this->trimItem($items[$i],$maxLength);
        }
    }


    function purgeItems(&$items,...$toPurge){
        for($i=0;$i<count($items);$i++){
            $this->purgeItem($items[$i],...$toPurge);
        }
    }

    function purgeItemsNegate(&$items,...$toPurge){
        for($i=0;$i<count($items);$i++){
            $this->purgeItemNegate($items[$i],...$toPurge);
        }
    }

    function purgeItemNegate(&$item,...$toPurge)
    {
        foreach($item as $key=>$value){
            if(!in_array($key,$toPurge)){
                unset($item[$key]);
            }
        }
    }


    function purgeItem(&$item,...$toPurge)
    {
        foreach($toPurge as $toPurgeItem){
            if(array_key_exists($toPurgeItem,$item)){
                unset($item[$toPurgeItem]);
            }
        }
    }

    function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    function getAction()
    {
        return getOrDefaultMany('action', [$_GET, $_POST], null);
    }

    function getDefaultMethod()
    {
        $method = getOrDefaultMany('REQUEST_METHOD', [$_SERVER], 'GET');
        return getOrDefaultMany('method', [$_GET, $_POST], $method);
    }

    function getRealMethod()
    {
        return getOrDefaultMany('REQUEST_METHOD', [$_SERVER], 'GET');
    }

    function getPostData()
    {
        if ($this->postData == null) {
            if ($this->getRealMethod() === "POST" || $this->getRealMethod() === "PUT") {
                $this->postData = file_get_contents('php://input');
            }
        }
        return $this->postData;
    }

    function getParamOrDefault($key,$default=null){
        $method = $this->getDefaultMethod();
        if(isset($_GET[$key]))return $_GET[$key];


        if($method==="POST" || $method === "PUT"){
            if(isset($_POST[$key]))return $_POST[$key];
            if($this->isJsonRequest()){
                try {
                    $data = $this->getJsonPostData();
                    if (is_array($data) && isset($data[$key])) return $data[$key];
                }catch (Exception $e) {}
            }
        }

        if(isset($_SESSION[$key]))return $_SESSION[$key];
        if(isset($_SERVER[$key]))return $_SERVER[$key];
        return $default;
    }

    function isJsonRequest(){
        return $_SERVER['CONTENT_TYPE'] === 'application/json';
    }

    function mapJsonToObject($data, $className) {
        $object = new $className();

        if($data!=null && is_array($data)){
            foreach ($data as $key => $value) {
                if (property_exists($object, $key)) {
                    $propType = (new ReflectionProperty($className, $key))->getType();

                    if ($propType && !$propType->isBuiltin() && is_array($value) || is_object($value)) {
                        // Recursively map nested objects
                        $object->$key = mapJsonToObject($value, $propType->getName());
                    } else {
                        $object->$key = $value;
                    }
                }
            }
        }

        return $object;
    }


    function getJsonPostData($className=null)
    {
        if ($this->postData == null) {
            $this->postData = $this->getPostData();
        }
        if($this->postData!=null){
            if($className==null){
                if($this->jsonPostData ==null) {
                    $this->jsonPostData = json_decode($this->postData, true) ?? [];
                }
                return $this->jsonPostData;
            }else{
                if(!isset($this->typedPostData[$className])){
                    $this->typedPostData[$className]= $this->mapJsonToObject(json_decode($this->postData, true),$className);
                }
                return  $this->typedPostData[$className];
            }
        }

        return [];
    }

    function getClassMethods()
    {
        $reflection = new ReflectionClass($this);
        $allMethods = $reflection->getMethods(); // Returns an array of ReflectionMethod objects
        $methodNames = [];
        foreach ($allMethods as $method) {
            $methodNames[] = $method->getName();
//            echo $method->getName() . " (Visibility: " .
//                ($method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private')) . ")\n";
        }
        return $methodNames;
    }

    function renderApiUi($params){
        throw new Exception("Calling as UI");
    }

    function handle()
    {
        $isCalled = GlobalRegistry::getNoThrow("INVOKE_AS_UI");


        $defaultMethod = $this->getDefaultMethod();
        if($defaultMethod=="GET" && $isCalled!=null){
            return;
        }
        $methods = $this->getClassMethods();
        $toCall = "apiCall" . $defaultMethod;
        $toCallForAll = "apiCall";
        $action = $this->getAction();
        if ($action !== null) {
            $toCall .= $action;
            $toCallForAll.= $action;
        }
        $toInvoke = null;
        foreach ($methods as $method) {
            if (strtolower($method) === strtolower($toCall)) {
                $toInvoke = $method;
                break;
            }else if (strtolower($method) === strtolower($toCallForAll)) {
                $toInvoke = $method;
                break;
            }
        }
        if ($toInvoke === null) {
            $this->sender->sendErrorResponse("ERROR_NOT_FOUND", 404);
            return false;
        }
        try {
            call_user_func_array([$this, $toInvoke], []);
            return true;
        } catch (Exception $e) {
            $this->log->error("Error calling $toInvoke",$e);
            $this->sender->sendErrorResponse("ERROR_GENERIC", 500, [$e->getCode(),$e->getMessage()]);
            return false;
        }
    }

    private function trimItem(&$item, $maxLength)
    {
        foreach ($item as $key => $value) {
            if(is_string($value) && strlen($value) > $maxLength){
                $item[$key] = substr($value,0,$maxLength);
            }
        }
    }
}
