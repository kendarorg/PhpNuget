<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\QueryParser;
use lib\utils\Properties;

class FileDbStorage extends DbStorage
{
    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query,  $limit=-1, $skip=0)
    {
        $toSort=[];
        $this->queryParser->parse($query,$this->dataType,$this->extraTypes);
        foreach($this->items as $item){
            if($this->queryParser->execute($item)){
                $toSort[] = $item;
            }
        }
        $this->queryParser->doSort($toSort);
        $toSort = $this->queryParser->doGroupBy($toSort);

        return $toSort;
    }
}