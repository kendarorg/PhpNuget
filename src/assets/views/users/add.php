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

	<form novalidate class="form-horizontal">

		<!-- User Id -->
		<div class="form-group">
			<label class="control-label col-md-2" for="UserId">User Id</label>
			<div class="col-md-3">
				<input type="text" class="form-control" name="UserId"  ng-model="user.UserId"/>
			</div>
		</div>

		<!-- User Full Name -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Name">Name</label>
			<div class="col-md-3">
				<input type="text" class="form-control" name="Name"  ng-model="user.Name"/>
			</div>
		</div>

		<!-- Company Name -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Company">Company</label>
			<div class="col-md-3">
				<input type="text" class="form-control" name="Company"  ng-model="user.Company"/>
			</div>
		</div>

		<!-- Email Address -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Email">Email</label>
			<div class="col-md-3">
				<input type="text" class="form-control"  name="Email" ng-model="user.Email"/>
			</div>
		</div>

		<!-- Packages -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Packages">Packages</label>
			<div class="col-md-6">
				<input type="text" class="form-control" readonly  name="Packages" ng-model="user.Packages"/>
			</div>
		</div>

		<!-- Token -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Token">Token</label>
			<div class="col-md-6">
				<input type="text" class="form-control" readonly name="Token" ng-model="user.Token"/>
			</div>
		</div>

		<!-- Set Password -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Token">Password</label>
			<div class="col-md-6">
				<input type="password" class="form-control" name="Password" ng-model="user.Password"/>
			</div>
		</div>

		<!-- Confirm Password -->
		<div class="form-group">
			<label class="control-label col-md-2" for="PasswordConfirm">Confirm Password</label>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="PasswordConfirm" ng-model="user.PasswordConfirm"/>
			</div>
		</div>

		<div class="form-group">

			<!-- Enable User Checkbox -->
			<div class="col-md-1 col-md-offset-2">
				<div class="checkbox">
					<label><input type="checkbox" ng-model="user.Enabled" name="Enabled" readonly>Is Enabled</label>
				</div>
			</div>

			<!-- User Is Admin Checkbox -->
			<div class="col-md-1">
				<div class="checkbox">
					<label><input type="checkbox"   ng-model="user.Admin" name="Admin" readonly>Is Administrator</label>
				</div>
			</div>
		</div>

		<!-- Add User Button -->
		<div class="form-group">
			<div class="col-md-1 col-md-offset-2">
				<button ng-click="add(user)" class="btn btn-default">Add</button>
			</div>
		</div>
	</form>
</div>