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
require_once(__ROOT__."/inc/downloadcount.php");

$id = UrlUtils::GetRequestParamOrDefault("id",null);
$version = UrlUtils::GetRequestParamOrDefault("version",null);

$db = new NuGetDb();
$items = $db->Query("Id eq '".$id."' and Version eq '".$version."'",1,0);
if(sizeof($items)==0){
	$items = $db->Query("Id eq '".$id."'  orderby Version desc",1,0);
}


function buildSimpleDep($d)
{
	if(strlen($d->Version)>0){
		$address= Settings::$SiteRoot."?specialType=singlePackage&id=".urlencode($d->Id)."&version=".urlencode($d->Version);
		$name = $d->Id." v ".$d->Version;
	}else{
		$address= Settings::$SiteRoot."?specialType=packages&searchQuery=Id eq '".urlencode($d->Id)."'";
		$name = $d->Id;
	}
	?>
	<li><a href="<?php echo $address;?>"><?php echo $name;?></a></li>
	<?php
}

function buildDepGroup($d)
{
	echo "<li>";
		echo "<u>".$d->TargetFramework."</u>";
		echo "<ul>";
		foreach($d->Dependencies as $dd){
			buildSimpleDep($dd);
		}
		echo "</ul>";
	echo "</li>";
}

if(sizeof($items)==0){
echo "Not found";

}else{
$item = $items[0];
loadDownloadCount($item);

?>
<ul>
	<li><h3><?php echo $item->Id;?> v <?php echo $item->Version;?>
		<img withd="25px" height="25px" src="<?php echo $item->IconUrl;?>"/>
	</h3></li>
	<li><a href="<?php echo $item->ProjectUrl;?>">Website</a></li>
	<li><a href="<?php echo $item->LicenseUrl;?>">License</a></li>
	<li><b>Owners:</b><?php echo $item->Owners;?></li>
	<li><b>Authors:</b> <?php
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
	
	<li><?php echo $item->Description;?></li>
	<li><b>Total downloads:</b> <?php echo $item->DownloadCount;?> </li>
	<li><b>This version downloads:</b> <?php echo $item->VersionDownloadCount;?> </li>
	<li><b>Download package:</b> <a href="<?php echo UrlUtils::CurrentUrl(rtrim(Settings::$SiteRoot,"/")."/api/?id=".$item->Id."&version=".$item->Version);?>">Here</a> </li>
	<li><b>Tags:</b> <?php echo $item->Tags;?></li>
	<?php
	if(sizeof($item->Dependencies)>0){
	?>
	<li><b>Dependencies</b>
		<ul>
		<?php
		if(is_array($item->Dependencies)){
			for($i=0;$i<sizeof($item->Dependencies);$i++){
				$d = $item->Dependencies[$i];
				if(!$d->IsGroup){
					buildSimpleDep($d);
				}else{
					buildDepGroup($d);
				}
			}
		}else{
			echo "<li>None</li>";
		}
		?>
		</ul>
	</li>
	<?php
	}
	$items = $db->Query("Id eq '".$id."' orderby Version desc",99999,0);
	if(sizeof($items)>1){
	?>
	<li><b>Versions</b>
	
		<ul>
		<?php
		foreach($items as $item){
			?>
			<li>Version: <a href="<?php echo Settings::$SiteRoot;
						?>?specialType=singlePackage<?php
						echo "&id=".urlencode($item->Id);
						echo "&version=".urlencode($item->Version);
						?>"><?php echo $item->Version;?></a></li>
			<?php
		
		}
		?>
		</ul>
	</li>
	<?php
	}
	?>
</ul>
<!--ul>
	h4>Versions<h4>
	li>EntityFramework 6.1.1 (this version) 	227408 	Friday, June 20 2014 <li
	li>EntityFramework 6.1.1-beta1 	17318 	Tuesday, May 20 2014 </li
ul>-->
<?php
}
?>