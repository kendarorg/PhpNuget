<?php
require_once(__DIR__."/root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/uploadUtils.php");
require_once(__ROOT__."/inc/nugetreader.php");
require_once(__ROOT__."/inc/commons/url.php");
if (!empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
	HttpUtils::ApiError('403', 'Invalid request');
	die();
}
$message ="";
?>
<html><body>
<script type="text/javascript">
<?php
if(!$loginController->IsLoggedIn){
	?>
		parent.packagesUploadControllerCallback("fail-unathorized","none","none");
	<?php
}else if(UploadUtils::IsUploadRequest()){
	$uploader = new UploadUtils(Settings::$PackagesRoot,array("nupkg"),Settings::$MaxUploadBytes);
	$result = $uploader->Upload("fileName");
	$fileName = basename($result["name"],".nupkg");
	$message = "";
	if($result["hasError"]==true){
		$message = "Failed uploading '".$result["name"]."'.";
		$message .= "Error is: ".$result["errorMessage"];
		if($result["errorCode"]!=null){
			$message .= "Error code is:".$result["errorCode"]."."; 
		}
		unlink($result["destination"]);
		?>
		parent.packagesUploadControllerCallback(false,"none","none","<?php echo$result["errorMessage"];?>");
		<?php
	}else{
		try{
			$udb = new UserDb();
			$user = $udb->GetByUserId($loginController->UserId);
			$baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot);
			$nugetReader = new NugetManager();
			
			$parsedNuspec = $nugetReader->LoadNuspecFromFile($result["destination"]);
			
			$parsedNuspec->UserId=$user->Id;
			$nuspecData = $nugetReader->SaveNuspec($result["destination"],$parsedNuspec);
			
			$message = "Uploaded ".$result["name"]." on ".dirname($result["destination"]);
			?>
			parent.packagesUploadControllerCallback(true,"<?php echo $parsedNuspec->Title;?>","<?php echo $parsedNuspec->Version;?>");
			<?php
		}catch(Exception $ex){
			unlink($result["destination"]);
			?>
			parent.packagesUploadControllerCallback(false,"none","none","<?php echo $ex->getMessage();?>");
			<?php
		}
	}
}else{
?>
		parent.packagesUploadControllerCallback(false,"none","none","unknown reason");
	<?php
}
?>
</script>
<?php 

echo $result["name"];
echo "<br>";
echo $message; 
?>
</body>
</html>