<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\mysql\MySqlDbExecutor;
use lib\db\QueryParser;
use lib\utils\Properties;

class MySqlDbStorage extends DbStorage
{
    private  $dbHost;
    /**
     * @var int
     */
    private  $dbPort;
    private  $dbName;
    private  $dbUser;
    private  $dbPassword;
    private mysqli $mysqli;

    /**
     * @param Properties $properties
     * @param QueryParser $queryParser
     */
    public function __construct($properties, $queryParser, $items = null)
    {
        parent::__construct($properties, $queryParser, $items);
        $this->dbHost = $properties->getProperty("db.host");
        $this->dbPort = intval($properties->getProperty("db.port",3275));
        $this->dbName = $properties->getProperty("db.name");
        $this->dbUser = $properties->getProperty("db.user");
        $this->dbPassword = $properties->getProperty("db.password");
        $this->mysqli = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
    }

    public function query($query, $limit = -1, $skip = 0)
    {
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        $executor = $this->queryParser->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $sqlQuery = $executor->execute(new Object());

        $sqlQuery = "SELECT * FROM (SELECT * FROM ".$this->table." ".$sqlQuery.") ";

        $orderBy = $executor->doSort($toSort);
        $groupBy = $executor->doGroupBy($toSort);
        $query = $sqlQuery." ".$groupBy." ".$orderBy;
        if($skip >0){
            $query.=" offset ".$skip;
        }
        if($limit >0){
            $query.=" limit ".$limit;
        }

        return $toSort;
    }
}