<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/commons/apibase.php");
require_once(__ROOT__."/inc/commons/smalltextdbapibase.php");
require_once(__ROOT__."/inc/db_nugetpackages.php");
require_once(__ROOT__."/inc/phpnugetobjectsearch.php");
require_once(__ROOT__."/inc/downloadcount.php");


class ApiNugetBase
{
	var $_db;
	var $_path;
	var $_version;
	var $_template;	
	var $_lastQuery;		
	
	public function Execute()
	{
		$action = UrlUtils::GetRequestParamOrDefault("action",null);
		
		if($action == null){
			$this->_root();
		}
		$action = strtolower($action);
		if($action=="packages"){
			$action = "search";
		}
		$this->_buildLastQuery();
		$this->_findpackagesbyd($action);
		$this->_findsingle($action);
		$this->_getupdates($action);
		$this->_search($action);
		$this->_metadata($action);
	}
	
	function _root()
	{
		$r = array();
		$r["@Base@"] = rtrim(UrlUtils::CurrentUrl(Settings::$SiteRoot),"/");
		$result = Utils::ReplaceInFile(Path::Combine($this->_path,"root.xml"),$r);
		HttpUtils::WriteData($result,"application/xml");
	}
	
	
    private function TranslateNet($tf)
    {
        return translateNetVersion($tf);
    }
	
    private function MakeDepString($d)
    {
        $tora = array();
        
        //<d:Dependencies>Castle.Core:3.1.0:net40|Castle.Windsor:3.1.0:net40|Common.Logging:2.0.0:net40|Quartz:2.0.1:net40|Castle.Core:2.1.0:net20|Castle.Windsor:2.1.0:net20|Common.Logging:1.0.0:net20|Quartz:1.0.1:net20</d:Dependencies>
		if(is_array($d)){
			for($i=0;$i<sizeof($d);$i++){
				$sd = $d[$i];
				if($sd->IsGroup){
					$fw= $this->TranslateNet($sd->TargetFramework);
					//if(strpos($fw,"+")===FALSE) {
						for($j=0;$j<sizeof($sd->Dependencies);$j++){
							$sdd = $sd->Dependencies[$j];
							$tora[]=($sdd->Id.":".$sdd->Version.":".$fw);
						}
					/*}else{
						$fws = explode("+",$fw);
						for($k=0;$k<sizeof($fws);$k++){
							$subfw = $fws[$k]
							for($j=0;$j<sizeof($sd->Dependencies);$j++){
								$sdd = $sd->Dependencies[$j];
								$tora[]=($sdd->Id.":".$sdd->Version.":".$subfw);
							}
						}
					}*/
				}else{
					$tora[]=($sd->Id.":".$sd->Version.":");
				}
			}
		}
        //print_r($tora);die();
        return implode("|",$tora);
    }
	
    public function _buildNuspecEntity($baseUrl,$e)
    {
        $t = $this->_entryTemplate;
        $t.="  ";
        $authors = explode(";",$e->Author);
        $author = "";
        if(sizeof($authors)>0){
			for($i=0;$i<sizeof($authors);$i++){
				$authors[$i] = htmlspecialchars($authors[$i]);
			}
            $author = "<name>".implode("</name>\n<name>",$authors)."</name>";
        }
        //print_r($e);
		$baseUrl = trim($baseUrl,"\\/");
		
        $t= str_replace("\${BASEURL}",$baseUrl,$t);
        $t= str_replace("\${NUSPEC.ID}",$e->Id,$t);
        
        
        $t= str_replace("\${NUSPEC.IDLOWER}",(strtolower($e->Id)),$t);
        $t= str_replace("\${NUSPEC.TITLE}",htmlspecialchars($e->Title),$t);
        $t= str_replace("\${NUSPEC.VERSION}",($e->Version),$t);
        $t= str_replace("\${NUSPEC.LICENSEURL}",htmlspecialchars($e->LicenseUrl),$t);
        $t= str_replace("\${NUSPEC.PROJECTURL}",htmlspecialchars($e->ProjectUrl),$t);
        $t= str_replace("\${NUSPEC.REQUIRELICENSEACCEPTANCE}",$e->RequireLicenseAcceptance?"true":"false",$t);
        $t= str_replace("\${NUSPEC.DESCRIPTION}",htmlspecialchars($e->Description),$t);
        if($e->Tags!=""){
            $t= str_replace("\${NUSPEC.TAGS}"," ".htmlspecialchars($e->Tags)." ",$t);
        }else{
            $t= str_replace("\${NUSPEC.TAGS}","",$t);
        }
		
		$t= str_replace("\${NUSPEC.SUMMARY}",htmlspecialchars($e->Summary),$t);
		$t= str_replace("\${NUSPEC.RELEASENOTES}",htmlspecialchars($e->ReleaseNotes),$t);
		
        $t= str_replace("\${NUSPEC.AUTHOR}",$author,$t);
        $t= str_replace("\${NUSPEC.AUTHORS}",$author,$t);
        $t= str_replace("\${DB.PUBLISHED}",$e->Published,$t);
        $t= str_replace("\${DB.PACKAGESIZE}",$e->PackageSize,$t);
        $t= str_replace("\${DB.PACKAGEHASHALGORITHM}",$e->PackageHashAlgorithm,$t);
        $t= str_replace("\${DB.PACKAGEHASH}",$e->PackageHash,$t);
       
        if(sizeof($e->Dependencies)==0){
            $t= str_replace("\${NUSPEC.DEPENDENCIES}","",$t);
        }else{
            $t= str_replace("\${NUSPEC.DEPENDENCIES}",$this->MakeDepString($e->Dependencies),$t);
        }
        $t= str_replace("\${DB.DOWNLOADCOUNT}",$e->DownloadCount,$t);
        $t= str_replace("\${DB.UPDATED}",$e->Published,$t);
        /*
        $t= str_replace("\${DB.ISABSOLUTELATESTVERSION}",$e->IsAbsoluteLatestVersion?"true":"false",$t);
        $t= str_replace("\${DB.VERSIONDOWNLOADCOUNT}",$e->VersionDownloadCount,$t);
        $t= str_replace("\${DB.ISLATESTVERSION}",$e->IsLatestVersion?"true":"false",$t);
		*/
		
		
        $t= str_replace("\${DB.ISPRERELEASE}",$e->IsPreRelease?"true":"false",$t);
		$t= str_replace("\${DB.ISABSOLUTELATESTVERSION}",$e->IsAbsoluteLatestVersion?"true":"false",$t);
        $t= str_replace("\${DB.ISLATESTVERSION}",$e->IsLatestVersion?"true":"false",$t);
        $t= str_replace("\${DB.VERSIONDOWNLOADCOUNT}","-1",$t);
        $t= str_replace("\${DB.LISTED}",$e->Listed?"true":"false",$t);
		if($e->Copyright!=null){
			if(is_string($e->Copyright)){
				$t= str_replace("\${DB.COPYRIGHT}",htmlspecialchars($e->Copyright),$t);
			}else{
				$t= str_replace("\${DB.COPYRIGHT}",htmlspecialchars(implode(", ",$e->Copyright)),$t);
			}
		}else{
			$t= str_replace("\${DB.COPYRIGHT}","",$t);
		}
        //rint_r($e);die();
        return preg_replace('/<!--(.*)-->/Uis', '', $t);
    }
	
	protected function _getPagination($top=10,$verbs = "all")
	{
		$pg = new Pagination();
		$pg->Skip = UrlUtils::GetRequestParamOrDefault("\$skip",0,$verbs);
		$pg->Top = UrlUtils::GetRequestParamOrDefault("\$top",$top,$verbs);
		return $pg;
	}
	
	
	protected function _initialize($path,$version)
	{
		$this->_path = $path;
		$this->_version = $version;
		$this->_entryTemplate = file_get_contents(Path::Combine($path,"entrytemplate.xml"));
		$this->_db = new NuGetDb();
	}
	
	
	function _query($query,$setupLatest = false)
	{
		$pg= $this->_getPagination();
		$db = new NuGetDb();
				
		$count = UrlUtils::GetRequestParamOrDefault("count","false")=="true";
		$allpages = UrlUtils::GetRequestParamOrDefault("\$inlinecount","none")=="allpages";
		$itemsCount = -1;
		
		if($count || $allpages){
			$allRows = $db->Query($query);
			$itemsCount = sizeof($allRows);
			if(!$allpages){
				HttpUtils::WriteData($itemsCount);
			}
		}
		$allRows = $db->Query($query,$pg->Top+1,$pg->Skip);
		
		
		if(!UrlUtils::IsFake()){
			header('Content-Type: 	application/atom+xml;type=feed;charset=utf-8');
		}
		$baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot);
		
		//
		$r = array();
		$r["@BASEURL@"]=$baseUrl;
		$r["@NEXTITEM@"]="";
		$r["@ITEMSCOUNT@"]="";
		
		if($itemsCount>=0){
			$r["@ITEMSCOUNT@"]="<m:count>".$itemsCount."</m:count>";
		}
		
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo Utils::ReplaceInFile(Path::Combine($this->_path,"entrytemplatepre.xml"),$r);
		
		if($setupLatest){
			for($i=sizeof($allRows)-1;$i>=0;$i--)
			{
				$allRows[$i]->IsAbsoluteLatestVersion=false;
				$allRows[$i]->IsLatestVersion=false;
			}
			if(sizeof($allRows)>0){
				$allRows[sizeof($allRows)-1]->IsAbsoluteLatestVersion =true;
			}
			for($i=sizeof($allRows)-1;$i>=0;$i--)
			{
				$row = $allRows[$i];
				if($row->IsPreRelease!=true){
					$row->IsLatestVersion=true;
					break;
				}
			}
		}
			
		for($i=0;$i<sizeof($allRows) && $i<$pg->Top;$i++)
		{
			$row = $allRows[$i];
			
			echo $this->_buildNuspecEntity($baseUrl,$row);
		}
		
		
		if(sizeof($allRows)>=$pg->Top){
			$act = strtolower(UrlUtils::GetRequestParamOrDefault("action",null));
			if($act=="packages") $act = "Packages";
			if($act=="search") $act = "Search";
			if($act=="findpackagesbyd") $act = "FindPackagesById";
			if($act=="getupdates") $act = "GetUpdates";
			$nq = $this->_setupLastQuery();
			if($act!="Search"){
				$nq = "\$skip=".($pg->Skip+$pg->Top)."&amp;\$top=".$pg->Top.$nq;
				echo "<link rel=\"next\" href=\"".$baseUrl."/api/".$this->_version."/".$act."?".$nq."\"/>";
			}
			/*?><link rel="next" href="http://localhost:8020/phpnuget/api/v2/Search?$skip=30&amp;$top=30&amp;searchTerm=''&amp;$filter=IsAbsoluteLatestVersion&amp;$orderby=DownloadCount+desc%2CId"/><?php*/
		}
		
		echo Utils::ReplaceInFile(Path::Combine($this->_path,"entrytemplatepost.xml"),$r);
		
		die();
	}
	
	function _metadata($action)
	{
		if($action != "metadata") return;
		HttpUtils::WriteFile(Path::Combine($this->_path,"metadata.xml"),"application/xml");
	}
	
	function _append($query,$data,$linkWith="")
	{
		if(strlen($query)==0) return $data;
		return $query." ".$linkWith." ".$data;
	}
	
	
	function _search($action)
	{	
		$query = "";
		if($action != "search") return;
		$searchTerm = UrlUtils::GetRequestParamOrDefault("searchTerm",null);
		$targetFramework = UrlUtils::GetRequestParamOrDefault("targetFramework",null);
		$includePrerelease = strtolower(UrlUtils::GetRequestParamOrDefault("includePrerelease",null));
		$filter = UrlUtils::GetRequestParamOrDefault("\$filter",null);		
		$orderby = UrlUtils::GetRequestParamOrDefault("\$orderby",null);
		
		$id = UrlUtils::GetRequestParamOrDefault("id",null);
		
		//Maybe allow UrlUtils to check without case sensitivity?
		if($id==null){
			$id = UrlUtils::GetRequestParamOrDefault("Id",null);
		}
		
		if($id!=null){
			$id = trim($id,"'");
			$x = "(Id eq '".$id."')";
			$query = $this->_append($query,$x,"and");
		}
		$version = UrlUtils::GetRequestParamOrDefault("version",null);
		if($version==null){
			$version = UrlUtils::GetRequestParamOrDefault("Version",null);
		}
		
		if($version!=null){
			$version = trim($version,"'");
			$x = "(Version eq '".$version."')";
			$query = $this->_append($query,$x,"and");
		}
		
		if($targetFramework!=null && $targetFramework!="" && $targetFramework!="''"){
			$targetFramework = urldecode(trim($targetFramework,"'"));	
			$tf = explode("|",$targetFramework);
			$ar = array();
			$tt = array();
			foreach($tf as $ti){
				if(!in_array($ti,$ar)){
					$ar[]=$ti;
					$tt[]=" substringof('".$ti."',TargetFramework) ";
				}
			}
			$x = "(TargetFramework eq '' or (".implode("and",$tt)."))";
			$query = $this->_append($query,$x,"and");
		}
		
		if($includePrerelease==null){
			if($filter=="IsLatestVersion"){
				$filter = null;
				$query = $this->_append($query,"(IsPreRelease eq false)","and");
			}else if($filter=="IsAbsoluteLatestVersion"){
				$filter = null;
			}
		}else if(strtolower($includePrerelease)=="false"){
			$x = "(IsPreRelease eq false)";
			$query = $this->_append($query,$x,"and");
			if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
				$filter = null;
			}
		}else if(strtolower($includePrerelease)=="true"){
			if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
				$filter = null;
			}
		}
		if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
			$filter = null;
		}
		
		
		
		
		if($searchTerm!=null && strlen($searchTerm)>0){
			if($searchTerm!="''"){
				$searchTerm = trim($searchTerm,"'");
				$x = "(";
				$x.= "substringof('".$searchTerm."',Title) or ";
				$x.= "substringof('".$searchTerm."',Id) or ";
				$x.= "substringof('".$searchTerm."',Description))";
				$query = $this->_append($query,$x,"and");
				$query = $this->_append($query," Listed eq true","and");
			}
		}
		
		
		$query = $this->_append($query,"(Listed eq true)","and");
		
		
		
		if($filter!=null){
			$x = "(".urldecode($filter).")";
			$query = $this->_append($query,$x,"and");
		}
		if($orderby!=null){
			$query =$query." orderby Id asc,Version desc, ".$orderby;
		}
		
		if($orderby==null){
			$query =$query." orderby Id asc,Version desc";
		}
		$query =$query." groupby Id";
		$this->_query($query);
	}
	
	function _findpackagesbyd($action)
	{
		$query = "";
		if($action != "findpackagesbyd") return;
		$id = UrlUtils::GetRequestParamOrDefault("id",null);
		if($id!=null){
			$id = trim($id,"'");
		}
		$query = "Id eq '".$id."' and Listed eq true orderby Id asc,Version asc";
		
		$this->_query($query,true);
	}
	
	function _findsingle($action)
	{
		$query = "";
		if($action != "single") return;
		$id = UrlUtils::GetRequestParamOrDefault("id","");
		$version = UrlUtils::GetRequestParamOrDefault("version","");
		
		$query = "Id eq '".$id."' and Version eq '".$version."'";
		
		$this->_query($query);
	}
	
	function _getupdates($action)
	{
		$query = "";
		if($action != "getupdates") return;
		$packageIds = UrlUtils::GetRequestParamOrDefault("packageIds",null);
		$versions = UrlUtils::GetRequestParamOrDefault("versions",null);
		$includePrerelease = UrlUtils::GetRequestParamOrDefault("includePrerelease","false");
		$includeAllVersions = UrlUtils::GetRequestParamOrDefault("includeAllVersions",null);	
		$targetFrameworks = UrlUtils::GetRequestParamOrDefault("targetFrameworks",null);	
		$versionConstraints = UrlUtils::GetRequestParamOrDefault("versionConstraints",null);
		
		
		if($packageIds==null){
			HttpUtils::ApiError(500,"Missing package ids.");		
		}else{
			$packageIds =explode("|",$packageIds);
		}
		if($versions!=null){
			$versions =explode("|",$versions);
			if(sizeof($versions)!=sizeof($packageIds)){
				HttpUtils::ApiError(500,"Package ids must match versions.");	
			}
		}
		if($versions!=null && $packageIds!=null){
			$tmp = array();
			for($i=0;$i<sizeof($versions);$i++){
				
				$tmp[] = "(Id eq '".trim($packageIds[$i],"'")."' and Version gt '".trim($versions[$i],"'")."')";
			}
			if(sizeof($tmp)>1){
				$query.="(".implode(" or ",$tmp).")";
			}else{
				$query.=$tmp[0];
			}
		}
		if($includePrerelease==null || strtolower($includePrerelease)=="false"){
			$x = "(IsPreRelease eq false)";
			$query = $this->_append($query,$x,"and");
		}
		
		$x = "(Listed eq true)";
		$query = $this->_append($query,$x,"and");
		
		$query .=" orderby Title asc, Version desc";
		if($includeAllVersions!="true"){
			$query .=" groupby Id";
		}
		$this->_query($query);
	}
	
	function _buildLastQuery()
	{
		$this->_lastQuery = array();
		
		$val = UrlUtils::GetRequestParamOrDefault("packageIds",null);
		if($val!=null)$this->_lastQuery["packageIds"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("versions",null);
		if($val!=null)$this->_lastQuery["versions"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("includePrerelease","false");
		if($val!=null)$this->_lastQuery["includePrerelease"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("includeAllVersions",null);
		if($val!=null)$this->_lastQuery["includeAllVersions"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("targetFrameworks",null);
		if($val!=null)$this->_lastQuery["targetFrameworks"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("versionConstraints",null);
		if($val!=null)$this->_lastQuery["versionConstraints"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("searchTerm",null);
		if($val!=null)$this->_lastQuery["searchTerm"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("\$filter",null);
		if($val!=null)$this->_lastQuery["\$filter"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("\$orderby",null);
		if($val!=null)$this->_lastQuery["\$orderby"]=$val;
		$val = UrlUtils::GetRequestParamOrDefault("id",null);
		if($val!=null)$this->_lastQuery["id"]=$val;
	}
	
	function _setupLastQuery()
	{
		$res = "";
		foreach($this->_lastQuery as $k=>$v){
			$res.="&amp;".$k."=".htmlentities($v);
		}
		return $res;
	}
}


class ApiNugetBaseV1 extends ApiNugetBase
{
	public function Initialize($path)
	{
		$this->_initialize($path,"v1");
	}
	
	function _getupdates($action)
	{
		if($action != "getupdates") return;
		HttpUtils::ApiError(404,"Not found");
	}
}

class ApiNugetBaseV2 extends ApiNugetBase
{
	public function Initialize($path)
	{
		$this->_initialize($path,"v2");
	}
}
?>
