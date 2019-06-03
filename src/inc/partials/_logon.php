<div class="container-fluid">
	<div class="row">
		<div class="col-md-12" >

			<div class="row">
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
					<form class="form-horizontal" method="POST" action="<?php echo Settings::$SiteRoot;?>" >
						<div class="form-group">
							<label class="control-label col-md-2" for="UserId">Username</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="UserId" name="UserId"/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="Email">Email</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="Email" name="Email"/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="Password">Password</label>
							<div class="col-md-10">
								<input type="password" class="form-control" id="Password" name="Password"/>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-10">
								<input type="submit" value="Register" class="btn btn-default"></input>
							</div>
						</div>
					</form>
				</div><!-- col ends -->
					<?php 
				} ?>

				<div class="col-md-6" >
					<h3>Sign in</h3>
					<form class="form-horizontal" method="POST" action="<?php echo Settings::$SiteRoot;?>?specialType=logon" >
						<input type="hidden" id="DoLogin" name="DoLogin" value="true"/>
						<div class="form-group">
							<label class="control-label col-md-2" for="UserId">Username or Email</label>
							<div class="col-md-10">
								<input type="text" class="form-control" id="UserId" name="UserId"/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-2" for="Password">Password</label>
							<div class="col-md-10">
								<input type="password" class="form-control" id="Password" name="Password"/>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-10">
								<input type="submit" value="Sign In" class="btn btn-default"></input>
							</div>
						</div>
						<?php if(isset($_GET["result"]) && strlen($_GET["result"]) > 0) {?>
						<div class="form-group">
							<div class="col-md-offset-2 col-md-10">
								<div class="alert alert-danger alert-dismissible fade in">
									<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>								
									<?php echo base64_decode($_GET["result"]); ?>
								</div>
							</div>
						</div>
						<?php } ?>
					</form>
				</div><!-- col ends -->
				<?php
				}
				?>
			</div><!-- row ends -->

		</div><!-- col ends -->
	</div><!-- row ends -->
</div><!-- container ends -->