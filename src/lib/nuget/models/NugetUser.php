<?php

namespace lib\nuget\models;

class NugetUser
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var bool
     */
    public $admin;

    /**
     * @var string
     */
    public $userId;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $company;
    /**
     * @var string
     */
    public $md5Password;
    /**
     * @var boolean
     */
    public $enabled;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $gravatarUrl;
    /**
     * @var string
     */
    public $token;


    public $packages;
}