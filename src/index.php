<?php
require_once(dirname(__FILE__)."/root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");	
require_once(__ROOT__."/inc/logincontroller.php");

if(UrlUtils::RequestMethod()=="put"){
	require_once(__ROOT__."/upload/index.php");
	die();
}
?>
<html ng-app="phpNugetApp">
	<head>
		<link rel="shortcut icon" href="./favicon.ico">
		<link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
		<script src="scripts/spin.min.js"></script>
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
		<script src="scripts/jquery/jquery-3.2.1.min.js"></script>
		<script src="scripts/bootstrap/bootstrap.min.js"></script>
	</head>
	<body>
		<?php
			require_once(__ROOT__."/_header.php");

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
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12 margin-bottom-md">
					<div ui-view></div>
				</div><!-- col ends -->
			</div><!-- row ends -->
		</div><!-- container ends -->

		<?php } require_once(__ROOT__."/_footer.php"); ?>

		<div id="fade"></div>

		<div id="modal">
			<div id="wrapper">
				<div id="wrapperContent">
					<br>
					<img id="loader" src="scripts/spinner.gif" /><br>
					<label id="loaderContent">
					</label>

				</div>
			</div>
		</div>
		<script>
			function setSpinnerValue(value){
				document.getElementById("loaderContent").innerHTML=value;
			}
			function openModalSpinner() {
					document.getElementById('modal').style.display = 'block';
					document.getElementById('fade').style.display = 'block';
			}

			function closeModalSpinner() {
				document.getElementById('modal').style.display = 'none';
				document.getElementById('fade').style.display = 'none';
			}
		</script>
	</body>
</html>
