<?php

namespace lib\db\file;

use lib\db\DbStorage;
use lib\db\QueryParser;
use lib\utils\Properties;

class FileDbStorage extends DbStorage
{
    /**
     * @var Properties
     */
    private $properties;
    /**
     * @var QueryParser
     */
    private  $queryParser;

    /**
     * @var array
     */
    private $items = array();

    /**
     * @param Properties $properties
     * @param QueryParser $queryParser
     */
    public function __construct($properties,$queryParser,$items = null)
    {
        $this->properties = $properties;
        $this->queryParser = $queryParser;
        $this->items = $items;
    }

    /**
     * @param string $query
     * @param string[] $keys
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $keys, $limit, $skip,$extraTypes,$dataType)
    {
        $toSort=[];
        $this->queryParser->parse($query,$dataType,$extraTypes);
        foreach($this->items as $item){
            if($this->queryParser->execute($item)){
                $toSort[] = $item;
            }
        }
        $toSort = $this->queryParser->doSort($toSort);
        $toSort = $this->queryParser->doGroupBy($toSort);

        return $toSort;
    }
}