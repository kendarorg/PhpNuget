<?php

namespace lib\rest\utils;

class LastQueryBuilder
{
    public function buildLastQuery($request)
    {
        $lastQuery = array();

        $val = $request->getParam("packageIds");
        if($val!=null)$lastQuery["packageIds"]=$val;
        $val = $request->getParam("versions");
        if($val!=null)$lastQuery["versions"]=$val;
        $val = $request->getParam("includePrerelease","false");
        if($val!=null)$lastQuery["includePrerelease"]=$val;
        $val = $request->getParam("includeAllVersions");
        if($val!=null)$lastQuery["includeAllVersions"]=$val;
        $val = $request->getParam("targetFrameworks");
        if($val!=null)$lastQuery["targetFrameworks"]=$val;
        $val = $request->getParam("versionConstraints");
        if($val!=null)$lastQuery["versionConstraints"]=$val;
        $val = $request->getParam("searchTerm");
        if($val!=null)$lastQuery["searchTerm"]=$val;
        $val = $request->getParam("\$filter");
        if($val!=null)$lastQuery["\$filter"]=$val;
        $val = $request->getParam("\$orderby");
        if($val!=null)$lastQuery["\$orderby"]=$val;
        $val = $request->getParam("id");
        if($val!=null)$lastQuery["id"]=$val;
        return $lastQuery;
    }
}