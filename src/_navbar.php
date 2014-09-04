<nav class="navbar navbar-default navbar-static-top navbar-inverse"  id="menuBar">
	<div class="container-fluid">
		
		<div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
			<li><a href="/<?php echo Settings::$SiteRoot;?>/#"><b>Home</b></a></li>
			<li><a href="/<?php echo Settings::$SiteRoot;?>/?specialType=packages">Packages</a></li>
			<?php 
				if($loginController->IsLoggedIn){ ?>
					<li><a href="/<?php echo Settings::$SiteRoot;?>/#/profile/<?php echo $loginController->UserId;?>/upload">Upload Package</a></li>
					<?php 
					if($loginController->Admin){ ?>
						<li><a href="/<?php echo Settings::$SiteRoot;?>/#/admin/users">Users</a></li>
						<?php
					}
				}
			?>
			</ul>
		</div>
	</div>
</nav>