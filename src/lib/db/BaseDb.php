<?php

namespace lib\db;

use lib\utils\Properties;

class BaseDb
{
    /**
     * @var DbStorage
     */
    private $storage;
    /**
     * @var string
     */
    private $table;

    /**
     * @param DbStorage $storage
     * @param string $table
     */
    public function __construct($storage, $table)
    {
        $this->storage = $storage;
        $this->table = $table;
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query,$limit = -1, $skip = 0){
        return [];
    }

    /**
     * @param string $query
     * @param integer $count
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function queryAndCount($query,&$count,$limit = -1, $skip = 0){
        return [];
    }

    /**
     * @param mixed $item
     * @return void
     */
    public function update($item){

    }

    /**
     * @return mixed
     */
    public function getByKey(){
        $keys = func_get_args();
    }
}