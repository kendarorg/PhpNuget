<?php
require_once(dirname(__FILE__)."/../../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/logincontroller.php");

$loginController->UnauthorizedIfNotLoggedIn();
?>
<div ng-controller="usersListController">
	<!--<div class="col-md-12">
		<a href="#/admin" class="btn btn-default">Back to Admin Home</a><br>&nbsp;
	</div>-->
	<div class="col-md-6">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>User Id</th>
					<th>Enabled</th>
					<th>Admin</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<!--UserId:|:Name:|:Company:|:Md5Password:|:Packages:|:Enabled:|:Email:|:Token:|:Admin-->
			<tbody>
				<tr ng-repeat="user in users">
					<td>				
					<?php if(defined('__ALLOWGRAVATAR__') && __ALLOWGRAVATAR__) { ?>
						<a href="#/admin/users/{{user.UserId}}"><img class="img-circle" src="{{user.GravatarUrl}}&s=24" /></a>						
						<a href="#/admin/users/{{user.UserId}}">{{user.UserId}}</a>
					<?php } else {?>
						<a href="#/admin/users/{{user.UserId}}">{{user.UserId}}</a>					
					<?php } ?>
					</td>					
					<td>{{user.Enabled}}</td>
					<td>{{user.Admin}}</td>
					<td><button class="btn btn-danger" ng-click="delete(user)">Delete</button></td>
				</tr>
			</tbody>
		</table>
		<a class="btn btn-primary" href="#/admin/users/add" >Add New User</a>
	</div>
</div>