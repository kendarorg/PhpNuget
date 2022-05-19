<?php

namespace lib\nuget\models;

class NugetDependency
{
    public $IsGroup=false;
    public $Id=null;
    public $Version=null;
    public $TargetFramework=null;
    public $Dependencies=null;
}