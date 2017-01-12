<?php
require_once("../../../settings.php");
require_once(__ROOT__."/inc/logincontroller.php");
$loginController->UnauthorizedIfNotLoggedIn();
?>
<div ng-controller="tabController">
	<ul class="nav nav-tabs" role="tablist">
	  <li class="{{getActive(0,'/user')}}"><a ng-click="onClickTab(0)"  href="#/profile/{{UserId}}/user">Profile</a></li>
	  <li class="{{getActive(1,'/upload')}}"><a ng-click="onClickTab(1)"  href="#/profile/{{UserId}}/upload">Upload Package</a></li>
	  <li class="{{getActive(2,'/packages/')}}"><a ng-click="onClickTab(2)"  href="#/profile/{{UserId}}/packages/list/0">Manage Packages</a></li>
	</ul>
	<br>
	<div ui-view class="col-md-10">
	</div>
</div>
