<div class="col-md-12" >
<?php
	require_once(dirname(__FILE__)."../../../root.php");
	require_once(__ROOT__."/settings.php");
	
	

if($loginController->IsLoggedIn){
	?>
	User logged in.
	<?php
}else{

if(Settings::$AllowUserAdd){ ?>
<div class="col-md-6" >
	<h3>Register</h3>
	<form method="POST" action="<?php echo Settings::$SiteRoot;?>" >
		<div class="form-group col-md-12">
			<div class="col-md-4">
				<label>Username</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="UserId" name="UserId"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-4">
				<label>Email</label>
			</div>
			<div class="col-md-8">
				<input type="text" class="form-control" id="Email" name="Email"/>
			</div>
		</div>
		<div class="form-group col-md-12">
			<div class="col-md-4">
				<label>Password</label>
			</div>
			<div class="col-md-8">
				<input type="password" class="form-control" id="Password" name="Password"/>
			</div>
		</div>
		<input type="submit" value="Register" class="btn btn-default"></input>
	</form>
</div>	
	<?php 
} ?>
<div class="col-md-6" >
<h3>Sign in</h3>
<form method="POST" action="<?php echo Settings::$SiteRoot;?>?specialType=logon" >
	<input type="hidden" id="DoLogin" name="DoLogin" value="true"/>
	<div class="form-group col-md-12">
		<div class="col-md-4">
			<label>Username or Email</label>
		</div>
		<div class="col-md-8">
			<input type="text" class="form-control" id="UserId" name="UserId"/>
		</div>
	</div>
	<div class="form-group col-md-12">
		<div class="col-md-4">
			<label>Password</label>
		</div>
		<div class="col-md-8">
			<input type="password" class="form-control" id="Password" name="Password"/>
		</div>
	</div>
	
	<input type="submit" value="Sign In" class="btn btn-default"></input>
</form>
</div>
<?php
}
?>
</div>