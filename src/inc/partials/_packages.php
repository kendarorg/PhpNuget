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
	
//Author eq 'Microsoft'
$db = new NuGetDb();

$pg = new Pagination();
$searchQuery = UrlUtils::GetRequestParamOrDefault("searchQuery",null);
$q =  UrlUtils::GetRequestParamOrDefault("q",null);
$orderBy = UrlUtils::GetRequestParamOrDefault("orderBy",null);
$originalOrderBy = $orderBy;
$fallbackQuery = "";

function buildFallbackQuery($q,$op = "and")
{
	if($q!="" && $q!=null){
		$q = str_replace("'","",$q);
		$q = str_replace("\"","",$q);
		$q = preg_split('/\s+/', $q);
		$t = array();
		foreach($q as $qs){
			$t[]="substringof('".$qs."',Id)";
			$t[]="substringof('".$qs."',Title)";
		}
		return implode(" ".$op." ",$t);
	}
	return null;
}


if($q!=null && $q!=""){
	$searchQuery = buildFallbackQuery($q);
}

if($searchQuery!=null && $searchQuery!=""){
	$fallbackQuery = buildFallbackQuery($searchQuery,"or");
}

if($originalOrderBy!=null){
	$originalOrderBy = "&orderBy=".$originalOrderBy;
}


$pg->Skip = UrlUtils::GetRequestParamOrDefault("skip",0);
$pg->Top = UrlUtils::GetRequestParamOrDefault("top",10);

$os = null;
$exceptionThrown = null;

try{
	if($searchQuery!=null){
		if($orderBy!=null){
			$orderBy = " orderby ".$orderBy;
		}else{
			$orderBy = " orderby Title asc,Version desc";
		}
		$os = new PhpNugetObjectSearch();
		$os->Parse("(".$searchQuery.") and Listed eq true ".$orderBy.$groupBy,$db->GetAllColumns());
	}else if($orderBy!=null){
		$orderBy = "orderby ".$orderBy;
		$os = new PhpNugetObjectSearch();
		$os->Parse("Listed eq true ".$orderBy.$groupBy,$db->GetAllColumns());
	}else{
		$os = new PhpNugetObjectSearch();
		$os->Parse("Listed eq true orderby Title asc, Version desc".$groupBy,$db->GetAllColumns());
	}
}catch(Exception $ex){
	$os = null;
	$exceptionThrown = $ex;
}

$retryCount=0;

doRetry:
if($os==null && $retryCount ==0){
	$retryCount++;
	try{
		if($fallbackQuery!=null){
			if($orderBy!=null){
				$orderBy = " orderby ".$orderBy;
			}else{
				$orderBy = " orderby Title asc,Version desc";
			}
			$os = new PhpNugetObjectSearch();
			$os->Parse("(".$fallbackQuery.") and Listed eq true ".$orderBy.$groupBy,$db->GetAllColumns());
		}
	}catch(Exception $ex){
		$os = null;
	}
}

if($os==null && $exceptionThrown!=null){
	echo "<b>Parsing error:</b> ".$exceptionThrown->getMessage();
	die();
}

$next = Settings::$SiteRoot."?specialType=packages";
if($searchQuery!=null){
	$next.="&searchQuery=".urlencode($searchQuery);
}
try{
	$items = $db->GetAllRows(999999,0,$os);
	$count = sizeof($items);
	$items = $db->GetAllRows($pg->Top,$pg->Skip,$os);
}catch(Exception $ex)
{
	$os = null;
	if($retryCount==0){
		goto doRetry;
	}
	$count = 0;
	$items = array();
	echo "<b>Parsing error:</b> ".$ex->getMessage();
}
?>
<h3> There are <?php echo $count;?> packages</h3> 


<?php
	if(sizeof($items)>0){
		?>
		Displaying results <?php echo $pg->Skip;?> - <?php echo (sizeof($items)+$pg->Skip);?>.
		<br><br>
		<div class="col-md-12">
			<div class="btn-group">
				<a  class="btn btn-default" ng-disabled="<?php
					$href = $next."&skip=".($pg->Skip-$pg->Top)."&top=".$pg->Top.$originalOrderBy;
					if($pg->Skip>0)echo "false";else echo "true";
					?>" href="<?php echo $href;?>">Previous</a>
				<a class="btn btn-default" ng-disabled="<?php
					$href = $next."&skip=".($pg->Skip+$pg->Top)."&top=".$pg->Top.$originalOrderBy;
					if($count>($pg->Top+$pg->Skip))echo "false";else echo "true";
					?>" href="<?php echo $href;?>">Next</a>
			</div>
		</div>
		<br><br>
		<table class="table table-condensed" >
			<tbody>
				
			<?php 
			for($i=0;$i<sizeof($items);$i++){
				$item = $items[$i];
				loadDownloadCount($item);
				?>
				<tr>
					<td><img withd="25px" height="25px" src="<?php echo $item->IconUrl;?>"/></td>
					<td><b><a href="<?php echo Settings::$SiteRoot;
						?>?specialType=singlePackage<?php
						echo "&id=".urlencode($item->Id);
						echo "&version=".urlencode($item->Version);
						?>"><?php echo $item->Title?></a></b><br><?php echo " v.".$item->Version;?>
					</td><td>
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
						?>
						</li>
						<li><b>Description:</b> <?php echo $item->Description;?></li>
						<li><b>Total downloads:</b> <?php echo $item->DownloadCount;?></li>
						<li><b>Tags:</b> <?php echo is_array($item->Tags) ? implode(",",$item->Tags):$item->Tags;?></li>
					</ul>
					</td>
				</tr>
				<?php
			}
			?>
				<tr><td></td><td></td><td></td></tr>
			<tbody>
		<table>
		<br><br>
		<?php
	}else{?>
		No results found.
	<?php
	}
?>