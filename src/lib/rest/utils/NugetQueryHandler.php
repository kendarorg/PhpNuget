<?php

namespace lib\rest\utils;

use lib\nuget\NugetPackages;

class NugetQueryHandler
{
    /**
     * @var NugetPackages
     */
    private $nugetPackages;

    /**
     * @param NugetPackages $nugetPackages
     */
    public function __construct($nugetPackages)
    {
        $this->nugetPackages = $nugetPackages;
    }

    /**
     * @param NugetQuery $nugetQuery
     * @return NugetQueryResult
     */
    public function search($nugetQuery){
        $result = new NugetQueryResult();
        $result->query = $this->setupQuery($nugetQuery);
        if($result->query->count || $result->query->allpages){
            $allRows = $this->nugetPackages->query($result->query->query);
            $result->itemsCount = sizeof($allRows);
            if(!$result->query->allpages){
                return $result;
            }
        }
        $result->data = $this->nugetPackages->query(
            $nugetQuery->query,$nugetQuery->pagination->Top+1,$nugetQuery->pagination->Skip);
        return $result;
    }

    /**
     * @param NugetQuery $nugetQuery
     * @return NugetQueryResult
     */
    public function query($nugetQuery){
        $result = new NugetQueryResult();
        $result->query = $nugetQuery;
        if($result->query->count || $result->query->allpages){
            $allRows = $this->nugetPackages->query($result->query->query);
            $result->itemsCount = sizeof($allRows);
            if(!$result->query->allpages){
                return $result;
            }
        }
        if($nugetQuery->pagination == null){
            $nugetQuery->pagination = new Pagination();
        }
        $result->data = $this->nugetPackages->query(
            $nugetQuery->query,$nugetQuery->pagination->Top+1,$nugetQuery->pagination->Skip);
        return $result;
    }

    /**
     * @param string $query
     * @param string $data
     * @param string $linkWith
     * @return mixed|string
     */
    public function append($query, $data, $linkWith="")
    {
        if(strlen($query)==0) return $data;
        return $query." ".$linkWith." ".$data;
    }

    /**
     * @param NugetQuery $nugetQuery
     * @return void
     */
    protected function setupQuery(NugetQuery $nugetQuery)
    {
        $query = "";
        if ($nugetQuery->id != null) {
            $nugetQuery->id = trim($nugetQuery->id, "'");
            $x = "(Id eq '" . $nugetQuery->id . "')";
            $query = $this->append($query, $x, "and");
        }


        if ($nugetQuery->version != null) {
            $nugetQuery->version = trim($nugetQuery->version, "'");
            $x = "(Version eq '" . $nugetQuery->version . "')";
            $query = $this->append($query, $x, "and");
        }

        if ($nugetQuery->targetFramework != null && $nugetQuery->targetFramework != "" && $nugetQuery->targetFramework != "''") {
            $nugetQuery->targetFramework = urldecode(trim($nugetQuery->targetFramework, "'"));
            $tf = explode("|", $nugetQuery->targetFramework);
            $ar = array();
            $tt = array();
            foreach ($tf as $ti) {
                if (!in_array($ti, $ar)) {
                    $ar[] = $ti;
                    $tt[] = " substringof('" . $ti . "',TargetFramework) ";
                }
            }
            $x = "(TargetFramework eq '' or (" . implode("and", $tt) . "))";
            $query = $this->append($query, $x, "and");
        }

        if (!$nugetQuery->includePrereleaseSet) {
            if ($nugetQuery->filter == "IsLatestVersion") {
                $nugetQuery->filter = null;
                $query = $this->append($query, "(IsPreRelease eq false)", "and");
            } else if ($nugetQuery->filterfilter == "IsAbsoluteLatestVersion") {
                $filter = null;
            }
        } else if (!$nugetQuery->includePrerelease) {
            $x = "(IsPreRelease eq false)";
            $query = $this->append($query, $x, "and");
            if ($nugetQuery->filter == "IsLatestVersion" || $nugetQuery->filter == "IsAbsoluteLatestVersion") {
                $nugetQuery->filter = null;
            }
        } else if ($nugetQuery->includePrerelease) {
            if ($nugetQuery->filter == "IsLatestVersion" || $nugetQuery->filter == "IsAbsoluteLatestVersion") {
                $nugetQuery->filter = null;
            }
        }
        if ($nugetQuery->filter == "IsLatestVersion" || $nugetQuery->filter == "IsAbsoluteLatestVersion") {
            $nugetQuery->filter = null;
        }


        if ($nugetQuery->searchTerm != null && strlen($nugetQuery->searchTerm) > 0) {
            if ($nugetQuery->searchTerm != "''") {
                $nugetQuery->searchTerm = trim($nugetQuery->searchTerm, "'");
                $x = "(";
                $x .= "substringof('" . $nugetQuery->searchTerm . "',Title) or ";
                $x .= "substringof('" . $nugetQuery->searchTerm . "',Id) or ";
                $x .= "substringof('" . $nugetQuery->searchTerm . "',Description))";
                $query = $this->append($query, $x, "and");
                $query = $this->append($query, " Listed eq true", "and");
            }
        }


        $query = $this->append($query, "(Listed eq true)", "and");


        if ($nugetQuery->filter != null) {
            $x = "(" . urldecode($nugetQuery->filter) . ")";
            $query = $this->append($query, $x, "and");
        }
        if ($nugetQuery->orderby != null) {
            $query = $query . " orderby Id asc,Version desc, " . $nugetQuery->orderby;
        }

        if ($nugetQuery->orderby == null) {
            $query = $query . " orderby Id asc,Version desc";
        }
        $query = $query . " groupby Id";
        $nugetQuery->query = $query;
        return $nugetQuery;
    }
}