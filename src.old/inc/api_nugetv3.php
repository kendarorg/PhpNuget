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
require_once(__ROOT__."/inc/api_nuget.php");


class ApiNugetBaseV3
{	
	public function Initialize($path)
	{
		$this->_initialize($path,"3.0.0-beta.1");
	}
	
	
	private function Resources()
	{
		$baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot)."/";
		$current = UrlUtils::CurrentUrl(UrlUtils::GetUrlDirectory())."/";
		$resources = array();
		$resources[] = array("@id"=>$current."search","@type"=>"SearchGalleryQueryService");
		$resources[] = array("@id"=>$current."registration","@type"=>"RegistrationsBaseUrl");
		$resources[] = array("@id"=>$current."registration/{id-lower}/index.json","@type"=>"PackageDisplayMetadataUriTemplate");
		$resources[] = array("@id"=>$current."registration/{id-lower}/{version-lower}.json","@type"=>"PackageVersionDisplayMetadataUriTemplate");
		$resources[] = array("@id"=>$baseUrl."api/v2","@type"=>"LegacyGallery");
		$result = array( 
			'version' => $this->_version,
			'@context' => array(
				'@vocab'=> 'https://schema.nuget.org/services#'),
			'resources' =>$resources);
		return $result;
	}
	
	private function SearchServices()
	{
		$baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot)."/";
		$current = UrlUtils::CurrentUrl(UrlUtils::GetUrlDirectory());
		$result = array();
		$result["self"]=$current;
		$result["services"] = array("search"=>$baseUrl."api/v3/search/service");
		$result["versions"] = array("search"=>
								array("version"=>$this->_version));
		
		return $result;
	}
	
	private function SearchResources()
	{
		$baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot)."/";
		$current = UrlUtils::CurrentUrl(UrlUtils::GetUrlDirectory());
		$result = array();
		$result["name"] = "PhpNuget v.".Settings::$Version;
		$result["service"] = "search";
		$resources = array();
		$resources["fields"]=$baseUrl."api/v3/search/service/fields";
		$resources["query"]=$baseUrl."api/v3/search/service/query";
		$resources["diagnostics"]=$baseUrl."api/v3/search/service/diag";
		$result["resources"] = $resources;
		return $result;
	}
	
	public function Execute()
	{
		header('Content-Type: application/json');
		$action = UrlUtils::GetRequestParamOrDefault("action",null);
		$packageId = UrlUtils::GetRequestParamOrDefault("id","angularjs");
		$version = UrlUtils::GetRequestParamOrDefault("version","1.0.3");
		$data = array();
		switch($action){
			case('resources'):
				//header("Location: https://api.nuget.org/v3/index.json");
				$data = $this->Resources();
				break;
			case('searchServices'):
				//header("Location: https://api-search.nuget.org/");
				$data = $this->SearchServices();
				break;
			case('searchResources'):
				//header("Location: https://api-search.nuget.org/search");
				$data = $this->SearchResources();
				break;
			case('searchFields'):
				$data =("Location: https://api-search.nuget.org/search/fields");
				break;
			case('searchQuery'):
				$data =("Location: https://api-search.nuget.org/search/query");
				break;
			case('searchDiag'):
				$data =("Location: https://api-search.nuget.org/search/diag");
				break;
			case('packages'):
				$data =("Location: https://api.nuget.org/v3/registration0/".$packageId."/index.json");
				break;
			case('package'):
				$data =("Location: https://api.nuget.org/v3/registration0/".$packageId."/".$version.".json");
				break;
			default:
				HttpUtils::ApiError(404,"Not found");
				break;
		
		}
		echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
		die();
	}
	
	protected function _initialize($path,$version)
	{
		$this->_path = $path;
		$this->_version = $version;
		$this->_db = new NuGetDb();
	}
}
?>