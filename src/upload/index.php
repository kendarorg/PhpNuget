<?php
//TODO Porting (package parsing/persistence still on legacy classes)
require_once(dirname(__DIR__)."/vendor/autoload.php");
require_once(dirname(__DIR__)."/settings.php");   // populates Properties from conf/properties.json
use lib\http\Request;
use lib\utils\Properties;

$request = \lib\OminousFactory::getObject("request");
$properties = \lib\OminousFactory::getObject("properties");


uplog("upload","==================================");
uplog("upload","request: ".$_SERVER['REQUEST_URI']);
uplogh("upload","Post",$_POST);
uplogh("upload","Get",$_GET);

$temp_file = tempnam(sys_get_temp_dir(), 'Tux');
$result = array();
try{
	
	if (empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
		HttpUtils::ApiError('403', 'Invalid API key');
		uplog("upload","No api key!");
		die();
	}
	
	$token = strtoupper(trim(trim($_SERVER['HTTP_X_NUGET_APIKEY'],"{"),"}"));
	// User identity is merged into the uifw `users` table; resolve the uploader by API key.
	$mysqli = new mysqli(
		$properties->getProperty("db.host"),
		$properties->getProperty("db.user"),
		$properties->getProperty("db.password"),
		$properties->getProperty("db.name"),
		intval($properties->getProperty("db.port", 3306))
	);
	$stmt = $mysqli->prepare("SELECT id, locked FROM users WHERE apiKey = ? LIMIT 1");
	$stmt->bind_param("s", $token);
	$stmt->execute();
	$userRow = $stmt->get_result()->fetch_assoc();

	if(!$userRow || intval($userRow['locked']) === 1){
		HttpUtils::ApiError('403', 'Invalid API key');
		uplog("upload","Wrong api key!");
		die();
	}

	uplog("upload","Validation done!");
	$user = (object)["Id" => $userRow['id']];
	$uploader = new UploadUtils(Settings::$PackagesRoot,array("nupkg","snupkg"),Settings::$MaxUploadBytes,true);
	$uploader->allowAll = true;
	uplog("upload","Upload utils initialized!");
	$result = $uploader->Upload("package");
	if($result['hasError']) { 
		uplogv("upload","UploadUtils error uploading",$result);
		throw new Exception($result['errorCode']); 
	}

	$fileName = basename($result["name"],".nupkg");


	$nugetReader = new NugetManager();
	uplog("upload","NugetManager initialized!");
	$parsedNuspec = $nugetReader->LoadNuspecFromFile($result["destination"]);
	uplogv("upload","Nuspec loaded!",$parsedNuspec);
	$parsedNuspec->UserId=$user->Id;

    $isSymbol = false;
    if(stripos($result["name"],".snupkg")!==false ||stripos($result["name"],".symbols.")!==false || UrlUtils::GetRequestParamOrDefault("symbol",null)!=null){
        $isSymbol=true;
    }

	$nugetReader->SaveNuspec($result["destination"],$parsedNuspec,$isSymbol);
	
	uplog("upload","Upload completed");
	// All done!
	header('HTTP/1.1 201 Created');
}catch(Exception $ex){
	uplogv("upload","Error uploading",$ex);
	if(array_key_exists ("destination",$result)){
		unlink($result["destination"]);
	}
	
	if($doUpLog){
		file_put_contents("upload.log",$ex->Message."\r\n", FILE_APPEND);
	}
	
	unlink($temp_file);
	HttpUtils::ApiError('500', $ex->getMessage());
	die();
}
?>