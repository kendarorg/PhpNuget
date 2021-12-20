<?php

namespace lib\db;

class DbStorage
{
    /**
     * @param string $query
     * @param string[] $keys
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $keys, $limit, $skip,$extraTypes,$dataType){
        throw new \Exception();
    }
}