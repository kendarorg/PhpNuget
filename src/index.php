<?php
require_once(dirname(__FILE__)."/root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");	

if(UrlUtils::RequestMethod()=="put"){
	require_once(__ROOT__."/upload/index.php");
	die();
}
?>
<html ng-app="phpNugetApp">
	<head>
		<link rel="shortcut icon" href="./favicon.ico">
		<script src="scripts/angular/angular.min.js"></script>
		<script src="scripts/angular/angular-cookies.min.js"></script>
		<script src="scripts/angular/angular-resource.min.js"></script>
		<script src="scripts/angular/angular-sanitize.min.js"></script>
		<script src="scripts/angular/angular-ui/ui-bootstrap.min.js"></script>
		<script src="scripts/angular/angular-ui/ui-bootstrap-tpls.min.js"></script>
		<script src="scripts/angular/angular-ui/ui-router.min.js"></script>
		<script src="scripts/angular/angular-ui/ui-utils.js"></script>
		<script src="assets/app.js"></script>
		<script src="assets/commons/utils.js"></script>
		<script src="assets/commons/tabcontroller.js"></script>
		<script src="assets/initialize.js"></script>
		<script src="assets/route.js"></script>
		<script src="assets/views/users/module.js"></script>
		<script src="assets/views/profile/module.js"></script>
	</head>
	<body>
		<?php
			require_once(__ROOT__."/_header.php");
			require_once(__ROOT__."/_navbar.php");
			
			$specialType = strtolower(UrlUtils::GetRequestParamOrDefault("specialType",null));
			if($specialType == "packages"){
				$groupBy=" groupBy Id";
				require_once(__ROOT__."/inc/partials/_packages.php");
			}else if($specialType == "singlepackage"){
				require_once(__ROOT__."/inc/partials/_singlepackage.php");
			}else if($specialType == "singleprofile"){
				require_once(__ROOT__."/inc/partials/_singleprofile.php");
			}else if($specialType == "logon"){
				require_once(__ROOT__."/inc/partials/_logon.php");
			}else{
		?>
		<div ui-view>
		
		</div>
		<?php
			}
			require_once(__ROOT__."/_footer.php");
		?>
	</body>
</html>