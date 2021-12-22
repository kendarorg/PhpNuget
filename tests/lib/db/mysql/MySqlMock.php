<?php

namespace lib\db\mysql;

class MySqlMock
{
    private $items =array();
    private $index =0;
    private $itemsIndex =-1;

    public function __construct($items)
    {
        $this->items[] = $items;
        $this->index = 0;
    }

    public function query($query){
        $this->itemsIndex++;
        $this->index =0;
        return $this;
    }

    public function fetch_assoc(){
        $currentItems = $this->items[$this->itemsIndex];
        if($this->index>=sizeof($this->currentItems)){
            return false;
        }
        $this->index++;
        return $currentItems[$this->index-1];
    }
}