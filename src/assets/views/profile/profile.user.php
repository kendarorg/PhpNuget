<div ng-controller="userController">
	<form novalidate class="simple-form">
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label>User Id</label>
			</div>
			<div class="col-md-2">
				<input type="text" class="form-control" name="UserId" readonly  ng-model="user.UserId"/>
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
			<div class="col-md-5">
				<button ng-click="updateToken(user)" class="btn btn-default">Regenerate</button>
			</div>
		</div>
		
		
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="Password">Password</label>
			</div>
			<div class="col-md-6">
				<input type="password" class="form-control" name="Password"/>
			</div>
		</div>
		
		
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="NewPassword">New Password</label>
			</div>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="NewPassword"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-1">
				<label for="NewPasswordConfirm">New Password Confirm</label>
			</div>
			<div class="col-md-6">
				<input type="password" class="form-control"  name="NewPasswordConfirm"/>
			</div>
		</div>
		
		<div class="form-group col-md-4">
			<div class="col-md-6"><div class="checkbox">
				<label><input type="checkbox" ng-model="user.Enabled" name="Enabled" readonly>Is Enabled</label>
			</div></div>
			<div class="col-md-6"><div class="checkbox">
				<label><input type="checkbox"   ng-model="user.Admin" name="Admin" readonly>Is Administrator</label>
			</div>
		</div>
		<button ng-click="update(user)" class="btn btn-default">Save</button>
	</form>
</div>
</div>