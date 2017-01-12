<?php
require_once(dirname(__FILE__)."../../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/commons/apibase.php");
require_once(__ROOT__."/inc/commons/smalltextdbapibase.php");
require_once(__ROOT__."/inc/db_nugetpackages.php");
require_once(__ROOT__."/inc/phpnugetobjectsearch.php");
require_once(__ROOT__."/inc/db_users.php");

$id = UrlUtils::GetRequestParamOrDefault("id",null);

$db = new UserDb();
$items = $db->Query( "UserId eq '".$id."' or Name eq '".$id."'",1,0);
$inferred = false;
if(sizeof($items)==0){
	$nu = new UserEntity();
	$nu->UserId = $id;
	$nu->Name = $id;
	$items =array();
	$items[]=$nu;
	$inferred = true;
}


$item = $items[0];



?>
<h3><?php echo $item->Name;?>'s Profile</h3> 
<?php
if($inferred){
	echo "This profile is extrapolated from the data.<br>";
	echo "It <b>DOES NOT EXISTS</b> on this server.<br>";
}

$query = "substringof('".$item->Name."',Author) or substringof('".$item->UserId."',Author) orderby Title asc, Version desc groupby Id";

$db = new NuGetDb();

$pg = new Pagination();
$pg->Skip = UrlUtils::GetRequestParamOrDefault("skip",0);
$pg->Top = UrlUtils::GetRequestParamOrDefault("top",10);

$items = $db->Query($query,999999,0);
$count = sizeof($items);
?>
<h3> There are <?php echo $count;?> packages for the given profile</h3> 

<?php 
	if(sizeof($items)>0){
		?>
		Displaying results <?php echo $pg->Skip;?> - <?php echo sizeof($items);?>.
		
		<ul>
			<?php 
			for($i=0;$i<sizeof($items);$i++){
				$item = $items[$i];
				?>
				<li>
					<b><a href="<?php echo Settings::$SiteRoot;
						?>?specialType=singlePackage<?php
						echo "&id=".urlencode($item->Id);
						echo "&version=".urlencode($item->Version);
						?>"><?php echo $item->Title." v.".$item->Version;?></a></b>
					<ul>
						<li><b>By:</b>
						<?php
						$ath = explode(",",$item->Author);
						for($k=0;$k<sizeof($ath);$k++){
							$v= trim($ath[$k]);
							if($k>0)echo ",&nbsp;";
							?>
							<a href="<?php echo Settings::$SiteRoot;
							?>?specialType=singleProfile<?php
							echo "&id=".urlencode($v);
							?>"><?php echo $v;?></a>
							<?php
						}
						?></li>
						<li><b>Description:</b> <?php echo $item->Description;?></li>
						<li><b>Total downloads:</b> <?php echo $item->VersionDownloadCount;?></li>
						<li><b>Tags:</b> <?php echo $item->Tags;?></li>
					</ul>
				</li>
				<?php
			}
			?>
		</ul>
		<br><br><br><br>
		<?php
	}else{?>
		No packages for the selected profile.
	<?php
	}

?>