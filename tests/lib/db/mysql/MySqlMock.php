<?php

namespace lib\db\mysql;

class MySqlMock
{
    private $items =array();
    public $queries =array();
    private $index =0;
    private $itemsIndex =-1;
    public $connect_error = false;
    public int $num_rows = 0;

    public function initialize($items)
    {
        $this->items[] = $items;
        $this->index = 0;
    }

    public function query($query){
        $this->queries[]=$query;
        $this->itemsIndex++;
        $this->index =0;
        $this->num_rows = sizeof($this->items[$this->itemsIndex]);
        return $this;
    }

    public function fetch_assoc(){
        $currentItems = $this->items[$this->itemsIndex];
        if($this->index>=sizeof($currentItems)){
            return false;
        }
        $this->index++;
        return $currentItems[$this->index-1];
    }

    public function real_escape_string($arg){
        return $arg;
    }
}