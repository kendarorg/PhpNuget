<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\QueryParser;
use lib\utils\Properties;

class MySqlDbStorage extends DbStorage
{

    /*public function query($query, $limit = -1, $skip = 0)
    {
        $toSort = [];
        $parseResult = $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        $query = $this->queryParser->translateToSql($parseResult);

        foreach ($this->items as $item) {
            if ($this->queryParser->execute($item)) {
        return $toSort;
    }*/
}