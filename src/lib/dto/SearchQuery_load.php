<?php

class SearchQuery
{
    public $from = 0;
    public $count = -1;
    public $format = "json";
    public $orderBy = array();
    public $fields = array();
    public $query = null;
    public $extraConditions = array();
    public $join = null;
    public $searchTerms = array();
    public $extraFilter = null;//extraFilter($searchQuery,$row) return bool

    public function initialize($ids, $values)
    {
        for ($i = 0; $i < count($ids); $i++) {
            $this->searchTerms[$ids[$i]] = $values[$i];
        }
    }
}
