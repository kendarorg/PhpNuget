<?php
$searchQuery = UrlUtils::GetRequestParamOrDefault("searchQuery","");
# implement gravatar.
use forxer\Gravatar\Gravatar;
?>
<nav class="navbar navbar-default navbar-static-top navbar-inverse" role="navigation" id="headerBar">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a href="<?php echo Settings::$SiteRoot;?>#" class="navbar-brand">PhpNuget</a>
		</div>
		<div id="navbarCollapse" class="collapse navbar-collapse">
			<div class="row">
				<div class="col-md-3">
					<form
						method="POST" action="<?php echo Settings::$SiteRoot;?>?specialType=packages"
						enctype="multipart/form-data"
						class="navbar-form navbar-left"
						role="search">
						<div class="input-group">
							<input type="text" class="form-control" id="searchQuery" name="searchQuery" placeholder="Search package" value="<?php echo $searchQuery;?>"/>
							<span class="input-group-btn"><button type="submit" class="btn btn-default">Search</button></span>
						</div>
					</form>
				</div>

				<div class="col-md-7 col-lg-8">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="<?php echo Settings::$SiteRoot;?>#"><b>Home</b></a></li>
						<li><a href="<?php echo Settings::$SiteRoot;?>?specialType=packages">Packages</a></li>
						<?php
							if($loginController->IsLoggedIn){ ?>
								<li><a href="<?php echo Settings::$SiteRoot;?>#/profile/<?php echo $loginController->UserId;?>/upload">Upload Package</a></li>
								<?php
								if($loginController->Admin){ ?>
									<li><a href="<?php echo Settings::$SiteRoot;?>#/admin/users">Users</a></li>
									<?php
								}
							}
						?>
						<?php
							if($loginController->IsLoggedIn){ ?>
								<li><a href="<?php echo Settings::$SiteRoot;?>#/profile/<?php echo $loginController->UserId; ?>/user">
									<?php if(defined('__ALLOWGRAVATAR__') && __ALLOWGRAVATAR__) { ?>
										<img class="img-circle" src="<?php echo Gravatar::image($loginController->Email,20,'mm'); ?>" />
									<?php } ?>								
									<?php echo $loginController->UserId; ?>
								</a></li>
								<li><a href="<?php echo Settings::$SiteRoot;?>?specialType=logon&DoLogin=false">Sign out</a></li>
						<?php
							}else{ ?>
								<li><a href="<?php echo Settings::$SiteRoot;?>?specialType=logon">Register/Sign in</a></li><?php
							}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</nav>