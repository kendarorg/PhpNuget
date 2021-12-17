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
     * @param Properties $properties
     * @param QueryParser $queryParser
     */
    public function __construct($properties,$queryParser)
    {
        $this->properties = $properties;
        $this->queryParser = $queryParser;
    }

    /**
     * @param string $query
     * @param string[] $keys
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $keys, $limit = -1, $skip = 0,$extraTypes = null)
    {
        $allRows = array();

        $toSort=[];
        $this->queryParser->parse($query,$extraTypes);
        foreach($allRows as $item){
            if($this->queryParser->execute($item)){
                $toSort[] = $item;
            }
        }
        $toSort = $this->queryParser->DoSort($toSort);
        $toSort = $this->queryParser->DoGroupBy($toSort);

        return $toSort;
    }
}