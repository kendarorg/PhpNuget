<?php
require_once(dirname(__FILE__)."/../../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/logincontroller.php");

$loginController->UnauthorizedIfNotLoggedIn();

$udb = new UserDb();
$user = $udb->GetByUserId($loginController->UserId);
?>

<div ng-controller="packagesUploadController">
	<div class="panel panel-default">
		<div class="panel-heading">Upload Package</div>
		<div class="panel-body">

			<p>To upload packages through the command line:</p>
			<pre>
NuGet SetApiKey <?php echo trim(trim($user->Token,"}"),"{");?> -Source <?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot."upload");?>

NuGet Push mypackage.nupkg -Source <?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot."upload");?>
			</pre>
			<p>If the default command line arguments does not work (or with <a href="https://npe.codeplex.com/">Nuget Package Explorer</a>) try with the following:</p>
			<pre>
NuGet SetApiKey <?php echo trim(trim($user->Token,"}"),"{");?> -Source <?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>

NuGet Push mypackage.nupkg -Source <?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>
			</pre>

			<p>To upload packages via web:</p>
			<form method="POST" action="<?php echo Settings::$SiteRoot;?>uploadnupkg.php"  enctype="multipart/form-data" target="output_frame">
				<div class="form-group col-md-12">
					<label class="btn btn-default btn-file" for="fileName">Filename
						<input type="file" id="fileName" name="fileName" class="hidden" />
					</label>
				</div>
				<input type="submit" value="Upload" class="btn btn-default"></input>
			</form>
		</div>
	</div>

	<?php
	if($loginController->Admin){
	?>
	<div class="panel panel-default">
		<div class="panel-heading">Load from another nuget repository</div>
		<div class="panel-body">
			<form novalidate class="simple-form col-md-12">
				<div class="form-group col-md-12">
					<div class="col-md-2">
						<label for="Url">Repository Url Template</label>
					</div>
					<div class="col-md-8">
						<!--https://www.nuget.org/api/v2/package/_Atrico.Lib.CommonAssemblyInfo/1.0.0-->
						<input class="form-control" name="Url" type="text" ng-model="downloadItem.Url" >
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-2">
						<label for="Id">Id</label>
					</div>
					<div class="col-md-8">
						<input type="text"  name="Id" ng-model="downloadItem.Id" class="form-control" />
					</div>
				</div>
				<div class="form-group col-md-12">
					<div class="col-md-2">
						<label for="Version">Version</label>
					</div>
					<div class="col-md-8">
						<input type="text"  name="Version"  ng-model="downloadItem.Version" class="form-control" />
					</div>
				</div>
				<button ng-click="download(downloadItem)" class="btn btn-default">Upload</button>
			</form>
		</div>
	</div>

	<div id="refreshPackages" name="refreshPackages" class="panel panel-default">
		<div class="panel-heading">Refresh packages db from packages directory.</div>
		<div class="panel-body">

			<form novalidate class="simple-form col-md-10">
				<button ng-click="refreshPackages()" class="btn btn-default">Go</button>
			</form>
		</div>
	</div>
	<?php }?>

	<!-- <iframe name="output_frame" src="about:blank" id="output_frame" width="100%" height="300px" frameborder="1"></iframe> -->
	<br>
	<br>
	<br>
	<iframe name="output_frame" src="#" id="output_frame" width="0px" height="0px" frameborder="0"></iframe>

</div>