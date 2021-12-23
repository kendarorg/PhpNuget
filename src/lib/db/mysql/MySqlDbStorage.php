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
    private  $mysqli;
    /**
     * @var null
     */
    private $converter;

    /**
     * @param Properties $properties
     * @param QueryParser $queryParser
     */
    public function __construct($properties, $queryParser, $items = null,$mysqli = null,$converter = null)
    {
        parent::__construct($properties, $queryParser, $items);
        $this->dbHost = $properties->getProperty("db.host");
        $this->dbPort = intval($properties->getProperty("db.port",3275));
        $this->dbName = $properties->getProperty("db.name");
        $this->dbUser = $properties->getProperty("db.user");
        $this->dbPassword = $properties->getProperty("db.password");
        $this->mysqli = $mysqli;
        if ($this->mysqli->connect_error) {
            throw new \Exception("Unable to connect to db");
        }
        $this->converter = $converter;
        if($converter == null){
            $this->converter = new BasicMysqlConverter();
        }
    }

    public function query($query, $limit = -1, $skip = 0)
    {
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        $executor = $this->queryParser->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $sqlQuery = $executor->execute(null);
        $what = "*";
        if($this->queryParser->hasGroupBy()){
            $fields = ["count(*) as count"];
            foreach ($this->queryParser->_groupClause as $gc){
                $fields[]=$gc;
            }
            $what = join(",",$fields);
        }

        $sqlQuery = "SELECT * FROM (SELECT ".$what." FROM ".$this->table." ".$sqlQuery.") ";

        $orderBy = $executor->doSort($toSort);
        $groupBy = $executor->doGroupBy($toSort);
        $query = $sqlQuery." ".$groupBy." ".$orderBy;
        if($skip >0){
            $query.=" offset ".$skip;
        }
        if($limit >0){
            $query.=" limit ".$limit;
        }

        $result = $this->mysqli->query($query);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $toSort[] = $this->converter->fromAssoc($row);
            }
        }

        return $toSort;
    }
    public function count($query)
    {
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        $executor = $this->queryParser->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $sqlQuery = $executor->execute(null);
        $what = "*";
        if($this->queryParser->hasGroupBy()){
            $fields = ["count(*) as count"];
            foreach ($this->queryParser->_groupClause as $gc){
                $fields[]=$gc;
            }
            $what = join(",",$fields);
        }
        $orderBy = $executor->doSort($toSort);
        $groupBy = $executor->doGroupBy($toSort);
        $query = $sqlQuery." ".$groupBy." ".$orderBy;

        $sqlQuery = "SELECT count(*) as countResult FROM (SELECT ".$what." FROM ".$this->table." ".$query.") ";

        $result = $this->mysqli->query($sqlQuery);
        $row = $result->fetch_assoc();
        return $row['countResult'];
    }
    /**
     * @param string $query
     * @param int $limit
     * @param int $skip
     * @param int $count
     * @return array|mixed
     */
    public function queryAndCount($query, &$count, $limit, $skip)
    {
        $count = $this->count($query);
        return $this->query($query,$limit,$skip);
    }
    /**
     * @param mixed $item
     * @param bool $param
     * @return void
     */
    public function save($byKey, $query,$item)
    {
        $data = $this->query($query);
        $update = sizeof($data)>0;
        $assocItem = $this->converter->toAssoc($item);
        //UPDATE table_name SET column1 = value1, column2 = value2, ...WHERE condition
        //INSERT INTO table_name (column1, column2, column3, ...) VALUES (value1, value2, value3, ...);
    }
    /**
     * @param array $foundedUsers
     * @param string|null $query
     * @return void
     */
    public function delete($byKey,$query)
    {

    }
}