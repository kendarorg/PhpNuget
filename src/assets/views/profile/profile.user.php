<?php
require_once(dirname(__FILE__)."/../../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/logincontroller.php");

$loginController->UnauthorizedIfNotLoggedIn();
?>
<div ng-controller="userController">
	<form novalidate class="form-horizontal">

		<?php if(defined('__ALLOWGRAVATAR__') && __ALLOWGRAVATAR__) { ?>
		<!-- Avatar -->		
		<div class="form-group">
			<label class="control-label col-md-2">&nbsp;</label>
			<div class="col-md-3">
				<img class="img-circle" src="{{user.GravatarUrl}}&s=100" />
			</div>
		</div>
		<?php } ?>	
		
		<!-- User Id -->
		<div class="form-group">
			<label class="control-label col-md-2" for="UserId">User Id</label>
			<div class="col-md-3">
				<input type="text" class="form-control" name="UserId" readonly  ng-model="user.UserId"/>
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

		<!-- Current Token / Generate New Token -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Token">Token</label>
			<div class="col-md-6">
				<div class="input-group">
					<input type="text" class="form-control" readonly name="Token" ng-model="user.Token"/>
					<span class="input-group-btn">
						<button ng-click="updateToken(user)" class="btn btn-default">Regenerate</button>
					</span>
				</div>
			</div>
		</div>

		<!-- Current Password -->
		<div class="form-group">
			<label class="control-label col-md-2" for="Password">Password</label>
			<div class="col-md-6">
				<input type="password" class="form-control" name="Password" ng-model="user.Password"/>
			</div>
		</div>

		<!-- Set New Password -->
		<div class="form-group">
			<label class="control-label col-md-2" for="NewPassword">New Password</label>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="NewPassword" ng-model="user.NewPassword"/>
			</div>
		</div>

		<!-- Confirm New Password -->
		<div class="form-group">
			<label class="control-label col-md-2" for="NewPasswordConfirm">Confirm New Password</label>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="NewPasswordConfirm" ng-model="user.NewPasswordConfirm"/>
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
				<button ng-click="update(user)" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
</div>
</div>