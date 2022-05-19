<?php

namespace lib\nuget\models;

class NugetUser
{
    /**
     * @var string
     */
    public $Id;

    /**
     * @var bool
     */
    public $Admin = false;

    /**
     * @var string
     */
    public $UserId;
    /**
     * @var string
     */
    public $Name = "";
    /**
     * @var string
     */
    public $Company= "";
    /**
     * @var string
     */
    public $Md5Password= "";
    /**
     * @var boolean
     */
    public $Enabled = false;
    /**
     * @var string
     */
    public $Email = "";
    /**
     * @var string
     */
    public $GravatarUrl = "";
    /**
     * @var string
     */
    public $Token;


    public $Packages = "";
}