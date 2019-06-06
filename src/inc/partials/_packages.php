<?php /** @noinspection ALL */
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
$q = UrlUtils::GetRequestParamOrDefault("q",null);
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
            $t[]="substringof('".$qs."',Description)";
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

$exceptionThrown = null;
$items = array();
$count = -1;


if($searchQuery == null || !$db->TryParse($searchQuery)) {
    try {
        if ($searchQuery != null) {
            if ($orderBy != null) {
                $orderBy = " orderby " . $orderBy;
            } else {
                $orderBy = " orderby Title asc,Version desc";
            }

            $searchQuery = "(" . $searchQuery . ") and Listed eq true " . $orderBy . $groupBy;
        } else if ($orderBy != null) {
            $orderBy = "orderby " . $orderBy;
            $os = new PhpNugetObjectSearch();
            $searchQuery = "Listed eq true " . $orderBy . $groupBy;
        } else {
            $searchQuery = "Listed eq true orderby Title asc, Version desc" . $groupBy;
        }

        $items = $db->Query($searchQuery);
        $count = sizeof($items);
        $items = $db->Query($searchQuery, $pg->Top, $pg->Skip);
    } catch (Exception $ex) {
        $count = -1;
        $items = array();
        $exceptionThrown = $ex;
    }
}

if($count==-1 ){
	try{
		if($fallbackQuery!=null){
			$orderBy = UrlUtils::GetRequestParamOrDefault("orderBy",null);

			if($orderBy!=null){
				$orderBy = " orderby ".$orderBy;
			}else{
				$orderBy = " orderby Title asc,Version desc";
			}
			$os = new PhpNugetObjectSearch();
			$fallbackQuery = "(".$fallbackQuery.") and Listed eq true ".$orderBy.$groupBy;
            //var_dump($fallbackQuery);die();
			$items = $db->Query($fallbackQuery);
			$count = sizeof($items);
			$items = $db->Query($fallbackQuery,$pg->Top,$pg->Skip);
		}
	}catch(Exception $ex){
		$count = -1;
		$items = array();
	}
}

if($count==-1 && $exceptionThrown!=null){
	echo "<b>Parsing error:</b> ".$exceptionThrown->getMessage();
	die();
}

$next = Settings::$SiteRoot."?specialType=packages";
if($searchQuery!=null){
	$next.="&searchQuery=".urlencode($searchQuery);
}

?>

<div class="container-fluid">
	<div class="row margin-bottom-md">

		<?php
		if(sizeof($items)>0){
		?>
		<div class="col-md-12">
			<h3>There are <?php echo $count;?> packages</h3>
			<span>Displaying results <?php echo $pg->Skip;?> - <?php echo (sizeof($items)+$pg->Skip);?>.</span>
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
		</div><!-- col ends -->
	</div><!-- row ends -->

	<div class="row">
		<div class="col-md-12">

			<table class="table table-condensed" >
				<tbody>
				<?php
				for($i=0;$i<sizeof($items);$i++){
					$item = $items[$i];
					loadDownloadCount($item);
					?>
					<tr>
						<td>
							<img class="package__icon package__icon--md" src="<?php echo $item->IconUrl;?>"/>
						</td>
						<td>
							<b><a href="<?php echo Settings::$SiteRoot;
							?>?specialType=singlePackage<?php
							echo "&id=".urlencode($item->Id);
							echo "&version=".urlencode($item->Version);
							?>"><?php echo $item->Title?></a></b><br><?php echo " v.".$item->Version;?>
						</td>
						<td>
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
				</tbody>
			</table>

		</div><!-- col ends -->
	</div><!-- row ends -->
</div><!-- container ends -->

<?php
}else{ ?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<h3>No results found.</h3>
		</div><!-- col ends -->
	</div><!-- row ends -->
</div><!-- container ends -->
<?php
}
?>
