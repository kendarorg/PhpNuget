<?php
require_once(dirname(__FILE__)."/../../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/logincontroller.php");

$loginController->UnauthorizedIfNotLoggedIn();
?>
<div ng-controller="userController">
	<div class="col-md-12">
		<!--<a href="#/admin" class="btn btn-default">Back to Admin Home</a><br>-->
		<a href="#/admin/users" class="btn btn-default">Back to Users</a><br>&nbsp;
	</div>
	<form novalidate class="simple-form">
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label>User Id</label>
			</div>
			<div class="col-md-2">
				<input type="text" class="form-control" name="UserId"  ng-model="user.UserId"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Name">Name</label>
			</div>
			<div class="col-md-2">
				<input type="text" class="form-control" name="Name"  ng-model="user.Name"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Company">Company</label>
			</div>
			<div class="col-md-2">
				<input type="text" class="form-control" name="Company"  ng-model="user.Company"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Email">Email</label>
			</div>
			<div class="col-md-4">
				<input type="text" class="form-control"  name="Email" ng-model="user.Email"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Packages">Packages</label>
			</div>
			<div class="col-md-6">
				<input type="text" class="form-control" readonly  name="Packages" ng-model="user.Packages"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Token">Token</label>
			</div>
			<div class="col-md-6">
				<input type="text" class="form-control"  readonly  name="Token" ng-model="user.Token"/>
			</div>
		</div>
		
		
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Password">Password</label>
			</div>
			<div class="col-md-6">
				<input type="password" class="form-control" name="Password" ng-model="user.Password"/>
			</div>
		</div>
		
		
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="PasswordConfirm">Confirm Password</label>
			</div>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="PasswordConfirm" ng-model="user.PasswordConfirm"/>
			</div>
		</div>
		
		<div class="form-group col-md-4">
			<div class="col-md-6"><div class="checkbox">
				<label><input type="checkbox" ng-model="user.Enabled" name="Enabled">Is Enabled</label>
			</div></div>
			<div class="col-md-6"><div class="checkbox">
				<label><input type="checkbox"   ng-model="user.Admin" name="Admin">Is Administrator</label>
			</div>
		</div>
		<button ng-click="add(user)" class="btn btn-default">Add</button>
	</form>
</div>