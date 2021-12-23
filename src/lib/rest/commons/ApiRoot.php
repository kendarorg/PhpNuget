<?php

namespace lib\rest\commons;

use lib\http\BaseHandler;
use lib\http\HandlerException;
use lib\http\Request;
use lib\nuget\NugetPackages;
use lib\nuget\NugetUsers;
use lib\rest\NugetDownloads;
use lib\utils\PathUtils;
use lib\utils\Properties;
use lib\utils\StringUtils;

class ApiRoot extends BaseHandler
{
    /**
     * @var NugetPackages
     */
    private $packages;
    /**
     * @var string|null
     */
    private $symbol;
    /**
     * @var NugetUsers
     */
    private $users;
    /**
     * @var \lib\nuget\NugetPackage
     */
    private $package;
    /**
     * @var \lib\nuget\models\NugetUser
     */
    private $user;
    /**
     * @var NugetDownloads
     */
    private $downloads;

    /**
     * @param Properties $properties
     * @param NugetPackages $nugetPackages
     * @param NugetUsers $nugetUsers
     * @param NugetDownloads $nugetDownloads;
     */
    public function __construct($properties, $nugetPackages, $nugetUsers, $nugetDownloads)
    {
        parent::__construct($properties);
        $this->packages = $nugetPackages;
        $this->users = $nugetUsers;
        $this->downloads = $nugetDownloads;
    }

    /**
     * @var string|null
     */
    private $id;
    /**
     * @var string|null
     */
    private $version;

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function preHandle($request)
    {
        $this->id = $request->getParam("id");

        $this->version = $request->getParam("version");
        $this->symbol = $request->getParam("symbol",false);

        if(StringUtils::isNullOrEmpty($this->id) ||StringUtils::isNullOrEmpty($this->version)){
            throw new HandlerException("Missing version or id", 500);
        }
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    private function checkAuthorizations($request){

        $apiKey = $request->getParam(["apiKey","X-NuGet-ApiKey","HTTP_X_NUGET_APIKEY"]);
        if($apiKey!=null){
            $apiKey = strtoupper(trim(trim($apiKey,"{"),"}"));
        }
        if($apiKey==null){
            throw new HandlerException("Missing api key", 403);
        }
        $allUsers = $this->users->query("Token eq '{".$apiKey."}'",1,0);
        if(sizeof($allUsers)==0){
            throw new HandlerException("No results found", 404);
        }
        $this->user = $allUsers[0];
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function httpGet($request)
    {
        $package = $this->loadPackage();
        $file = strtolower($package->Id.".".$package->Version.($this->symbol?".snupkg":".nupkg"));
        $path = PathUtils::combine($this->properties->getProperty("packagesRoot"),$file);
        if(!file_exists($path)){
            throw new HandlerException("No file found", 404);
        }
        $this->answerFile($path,"application/zip");
        $this->downloads->incrementDownloads($package->id,$package->version);
    }

    /**
     * @return \lib\nuget\NugetPackage
     * @throws HandlerException
     */
    protected function loadPackage()
    {
        $query = "Id eq '" . $this->id . "' and Version eq '" . $this->version . "'";
        $result = $this->packages->query($query);
        if (sizeof($result) == 0) {
            throw new HandlerException("No results found", 404);
        }
        return $result[0];
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function post($request)
    {
        $this->checkAuthorizations($request);
        $package = $this->loadPackage();
        if($package->userId!=$this->user->id && !$this->user->admin){
            throw new HandlerException("No results found", 404);
        }
        if(!$package->listed) {
            $package->listed = true;
            $this->packages->update($package);
        }
        $this->answerOk();
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function delete($request)
    {
        $this->checkAuthorizations($request);
        $package = $this->loadPackage();
        if($package->userId!=$this->user->id && !$this->user->admin){
            throw new HandlerException("No results found", 404);
        }
        if(!$package->isPreRelease) {
            $package->isPreRelease = $request->hasParam("setPrerelease");
            $this->packages->update($package);
        }
        $this->answerOk();
    }

}