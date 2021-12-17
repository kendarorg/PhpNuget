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
    public function query($query,$keys, $limit = -1, $skip = 0,$extraTypes){
        throw new \Exception();
    }
}