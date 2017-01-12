<div ng-controller="profilePackagesController">
	<div class="col-md-6" >
		<br>
		<div class="col-md-12">
			<div class="btn-group">
				<a  class="btn btn-default" ng-disabled="hasPrevious<=0" href="#/profile/{{UserId}}/packages/list/{{previous}}">Previous</a>
				<a class="btn btn-default" ng-disabled="hasNext<=0" href="#/profile/{{UserId}}/packages/list/{{next}}">Next</a>
			</div>
		</div>
		<br>
		<table class="table table-condensed" >
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Package Id</th>
					<th>Version</th>
					<th>Authors</th>
				</tr>
			</thead>
			<!--UserId:|:Name:|:Company:|:Md5Password:|:Packages:|:Enabled:|:Email:|:Token:|:Admin-->
			<tbody>
				
				<tr ng-repeat="package in packages" >
					<td><img withd="25px" height="25px" ng-src="{{package.IconUrl}}"/></td>
					<td><a href="#/profile/{{UserId}}/packages/{{package.Id}}/{{package.Version}}">{{package.Title}}</td>
					<td>{{package.Version}}</td>
					<td>{{package.Author}}</td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
			
		</table>
		
		<br><br><br><br>
	</div>
	
</div>