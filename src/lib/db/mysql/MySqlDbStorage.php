<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\mysql\MySqlDbExecutor;
use lib\db\QueryParser;
use lib\utils\Properties;

class MySqlDbStorage extends DbStorage
{

    public function query($query, $limit = -1, $skip = 0)
    {
        $toSort = [];
        $this->queryParser->parse($query, $this->dataType, $this->extraTypes);
        $this->loadData();
        $executor = $this->queryParser->setupExecutor(new MySqlDbExecutor());
        $sqlQuery = "SELECT * FROM (".$executor->execute(new Object()).") ";

        $orderBy = $executor->doSort($toSort);
        $groupBy = $executor->doGroupBy($toSort);
        $query = $sqlQuery." ".$orderBy." ".$groupBy;
        if($skip >0){
            $query.=" offset ".$skip;
        }
        if($limit >0){
            $query.=" limit ".$limit;
        }

        return $toSort;
    }
}