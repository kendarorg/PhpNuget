<?php
require_once("../../../settings.php");
require_once(__ROOT__."/inc/logincontroller.php");
$loginController->UnauthorizedIfNotLoggedIn();
?>
<div ng-controller="profilePackageController">
	<div class="col-md-12">
		<h4>{{PackageId}}-{{PackageVersion}}</h4>
		<a href="#/profile/{{UserId}}/packages/list/0">Back to list</a><br><br>
	</div>
	<div class="col-md-1">
		<div class="col-md-12">
			Versions:
		</div>
		<div class="col-md-12" ng-repeat="version in versions" >
			<a href="#/profile/{{UserId}}/packages/{{package.Id}}/{{version.Version}}">{{version.Version}}</a>
		</div>
	</div>
	<div class="col-md-11">
		
		<form novalidate class="simple-form col-md-12">
			
			<div class="col-md-12">
			<div class="col-md-6">
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Id</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Id" readonly  ng-model="package.Id"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Version</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Version" readonly  ng-model="package.Version"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Title</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Title"  ng-model="package.Title"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Authors, comma separated</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Author"  ng-model="package.Author"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Summary</label>
					</div>
					<div class="col-md-8">
						<textarea class="form-control" name="Summary"  ng-model="package.Summary"></textarea>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Description</label>
					</div>
					<div class="col-md-8">
						<textarea class="form-control" name="Description"  ng-model="package.Description"></textarea>
					</div>
				</div>
			</div>
			<div class="col-md-6 ">
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Copyright</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Copyright"  ng-model="package.Copyright"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Icon Url</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="IconUrl"  ng-model="package.IconUrl"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Project Url</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="ProjectUrl"  ng-model="package.ProjectUrl"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>License Url</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="LicenseUrl"  ng-model="package.LicenseUrl"/>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Release Notes (for this version)</label>
					</div>
					<div class="col-md-8">
						<textarea class="form-control" name="ReleaseNotes"  ng-model="package.ReleaseNotes"></textarea>
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-4">
						<label>Tags, space separated</label>
					</div>
					<div class="col-md-8">
						<input type="text" class="form-control" name="Tags"  ng-model="package.Tags"/>
					</div>
				</div>
				
				<div class="form-group col-md-12">
					<div class="col-md-4"><div class="checkbox">
						<label><input type="checkbox" ng-model="package.Listed" name="Listed">Is Listed</label>
					</div></div>
					<div class="col-md-4"><div class="checkbox">
						<label><input type="checkbox"   ng-model="package.IsPreRelease" name="IsPreRelease" ng-disabled=true>Is Pre Release</label>
					</div></div>
					<div class="col-md-4"><div class="checkbox">
						<label><input type="checkbox"   ng-model="package.RequireLicenseAcceptance" name="RequireLicenseAcceptance">Require License Acceptance</label>
					</div></div>
				</div>
			</div>
			</div>
			<div class="col-md-12">
				<button class="btn btn-default" ng-click="update(package)">Save</button>
				<?php if(__ALLOWPACKAGESDELETE__!=false){ ?>
				<button class="btn btn-default" ng-click="delete(package)">Delete</button>
				<?php } ?>
			</div>
		</form>
	</div>
			<br/><br/><br/><br/><br/><br/>
</div>