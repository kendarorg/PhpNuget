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
		<style>
		    #fade {
                display: none;
                /*position:absolute;
                top: 0%;
                left: 0%;
                width: 100%;
                height: 12000;*/
                position:fixed; top:0px; left:0px; bottom:0px; right:0px;
                background-color: #ababab;
                z-index: 1001;
                -moz-opacity: 0.8;
                opacity: .70;
                filter: alpha(opacity=80);
            }

            #modalx {
                display: none;
                position: absolute;
                top: 45%;
                left: 45%;
                width: 300px;
                height: 90px;
                padding:30px 15px 0px;
                border: 3px solid #ababab;
                box-shadow:1px 1px 10px #ababab;
                border-radius:20px;
                background-color: white;
                z-index: 1002;
                text-align:center;
                overflow: auto;
  left: 0;
            }
            
            #wrapper {
 width:300px;
 height:100px;
 margin:0 auto;
 position:fixed;
 left:50%;
 top:50%;
 margin-left:-150px;
 margin-top:-150px;
    border: 3px solid #ababab;
    box-shadow:1px 1px 10px #ababab;
    border-radius:20px;
    background-color: white;
}

#wrapperContent {
}

#modal {
    display: none;
    position: absolute;
    width: 1px;
    height: 1px;
    bottom: 0;
    left: 0;
    z-index: 1002;
    text-align:center;
}
            
		</style>
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
		<div id="fade"></div>
        <!--<div id="modal">
            <img id="loader" src="scripts/spinner.gif" /><br>
            <div id="loaderContent"></div>
        </div>-->
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