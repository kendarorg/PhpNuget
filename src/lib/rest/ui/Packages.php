<?php

namespace lib\rest\ui;

use lib\http\BaseHandler;
use lib\http\HandlerException;
use lib\http\Request;
use lib\nuget\NugetPackages;
use lib\nuget\NugetUsers;
use lib\rest\Result;
use lib\utils\Properties;

class Packages extends BaseHandler
{
    /**
     * @var NugetUsers
     */
    private $users;
    /**
     * @var NugetPackages
     */
    private $packages;

    /**
     * @param Properties $properties
     * @param NugetUsers $nugetUsers
     * @param NugetPackages $nugetPackages
     */
    public function __construct($properties, $nugetUsers, $nugetPackages)
    {
        parent::__construct($properties);
        $this->users = $nugetUsers;
        $this->packages = $nugetPackages;
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function httpGet($request)
    {
        //AUTHENTICATED logincontroller
        $method = $request->getParam("method");

        if ($method == "download") {
            $this->dodownload($request);
        } else if ($method == "countpackagestorefresh") {
            $this->docountpackagestorefresh($request);
        } else if ($method == "refreshpackages") {
            $this->dorefreshpackages($request);
        } else {
            $this->dogetbyquery($request);
        }
    }

    /**
     * @param Request $request
     */
    protected function dogetbyquery(Request $request)
    {
        $query = $request->getParam("query");
        $doGroup = $request->getBoolean("doGroup");
        $skip = $request->getInteger("skip", 0);
        $top = $request->getInteger("top", 1000) + 1;
        $user = $request->authorization;
        if (!$user->admin) {
            if (strlen($query) > 0) {
                $query = " and (" . $query . ")";
            }
            $query = "(UserId eq '" . $user->id . "')" . $query;
        }
        $query .= " orderBy Title asc, Version desc";
        if ($doGroup) {
            $query .= " groupby Id";
        }
        $count = 0;
        $data = $this->packages->queryAndCount($query, $count, $top, $skip);
        $res = new Result();
        $res->Success = true;
        $res->Data = $data;
        $res->CountAll = $count;
        $this->answerJson($res);
    }


    public function docountpackagestorefresh()
    {
        /*$result =0;
        try{
            $this->_preExecute();
            global $loginController;

            if(!$loginController->Admin){
                throw new Exception("Unauthorized");
            }
            $files = scandir(Settings::$PackagesRoot);
            $result = sizeof($files);
            $message = $result ;
            ApiBase::ReturnSuccess($message);
        }catch(Exception $ex){
            $message = "Refreshed only ".$result." files.";
            ApiBase::ReturnError($message."\r\n".$ex->getMessage(),500);
        }*/
    }

    /**
     * @param Request $request
     * @return void
     */
    public function dodownload($request)
    {
        /*$tempFile ="";
        try{
            $url = $request->getParam("Url");
            $id = $request->getParam("Id");
            $version = $request->getParam("Version");
            $isSymbol =$request->getBoolean("symbol",false);

            if($id==null || $url==null || $version==null){
                throw new HandlerException("Missing data",500);
            }
            $user = $request->authorization;

            if(!$user->admin){
                throw new HandlerException("Unauthorized",403);
            }

            $url = str_replace("@ID",$id,$url);
            $url = str_replace("@VERSION",$version,$url);

            $nupackage = HttpUtils::download($url);
            $tempFile = PathUtils::writeTemporaryFile($nupackage);

            $udb = new UserDb();
            $user = $udb->GetByUserId($loginController->UserId);
            $baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot);
            $nugetReader = new NugetManager();

            $parsedNuspec = $nugetReader->LoadNuspecFromFile($tempFile);
            if(!$isSymbol){
                $isSymbol=$parsedNuspec->IsSymbols;
            }

            $parsedNuspec->UserId=$user->Id;
            $nugetReader->SaveNuspec($tempFile,$parsedNuspec,$isSymbol);
        }catch(Exception $ex){
            if(file_exists($tempFile))
                unlink($tempFile);
            ApiBase::ReturnError($ex->getMessage(),500);
        }
        if(file_exists($tempFile))unlink($tempFile);
        ApiBase::ReturnSuccess(null);*/
    }
}