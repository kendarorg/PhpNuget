<?php
require_once(dirname(__FILE__)."/root.php");
require_once(__ROOT__."/inc/logincontroller.php");

$searchQuery = UrlUtils::GetRequestParamOrDefault("searchQuery","");

?>
<link href="scripts/bootstrap/bootstrap.min.css" rel="stylesheet" />
<nav class="navbar navbar-default navbar-static-top navbar-inverse" role="navigation" id="headerBar">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<span class="navbar-brand">PhpNuget</span>
		</div>
		<div id="navbarCollapse" class="collapse navbar-collapse">
			<form 
				method="POST" action="<?php echo Settings::$SiteRoot;?>?specialType=packages"
				enctype="multipart/form-data" 
				class="navbar-form navbar-left">
				<div class="form-group">
					<input type="text" class="form-control" id="searchQuery" name="searchQuery" value="<?php echo $searchQuery;?>"/>
					<input type="submit" value="Search" class="btn btn-default"></input>
				</div>
			</form>
			<ul class="nav navbar-nav navbar-right"><?php
						if($loginController->IsLoggedIn){
					?><li>
	<a href="<?php echo Settings::$SiteRoot;?>#/profile/<?php echo $loginController->UserId; ?>/user"><?php echo $loginController->UserId; ?></a>
</li>
<li>
	<a href="<?php echo Settings::$SiteRoot;?>?specialType=logon&DoLogin=false">Sign out</a>
</li><?php
	}else{
?><li><a href="<?php echo Settings::$SiteRoot;?>?specialType=logon">Register/Sign in</a></li><?php
	}
?></ul>
		</div>
	</div>
</nav>