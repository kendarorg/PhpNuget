<?php

namespace lib\rest;

class NugetQuery
{

    /**
     * @var string
     */
    public $query;
    /**
     * @var Pagination
     */
    public $pagination;
    /**
     * @var string
     */
    public $xmlAction;
    /**
     * @var bool
     */
    public $count;
    /**
     * @var bool
     */
    public $lineCount;
    /**
     * @var string
     */
    public $baseUrl;
    /**
     * @var string|null
     */
    public $searchTerm;
    /**
     * @var string|null
     */
    public $targetFramework;
    /**
     * @var bool
     */
    public $includePrerelease;
    /**
     * @var string|null
     */
    public $filter;
    /**
     * @var string|null
     */
    public $orderby;
    /**
     * @var string|null
     */
    public $id;
    /**
     * @var string|null
     */
    public $version;
    /**
     * @var bool
     */
    public $includePrereleaseSet;


}