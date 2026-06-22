<?php

class Reports{
    var $db;

    public function foz(&$data,$id){
        try {
            if (!isset($data[$id])) return 0.00;
            return round(floatval($data[$id]), 2);
        }catch (Exception $e){
            return 0.00;
        }
    }
    public function ioz(&$data,$id){
        try {
            if (!isset($data[$id])) return 0;
            return intval($data[$id]);
        }catch (Exception $e){
            return 0;
        }
    }
    public function __construct()
    {
        $this->db= getDbConnection();
    }

    public function setKeys(&$data,&$keys){
        for($i=0;$i<count($keys);$i++){
            $keyId = $keys[$i][0];
            $value = $data[$keyId];
            $keys[$i][1]=$value;
        }
    }

    function decimalToHoursMinutes($decimalHours) {
        try {
            if($decimalHours < 0 || $decimalHours ==null)return [0,0];
            $hours = floor($decimalHours);
            $minutes = round(($decimalHours - $hours) * 60);

            return [$hours, $minutes];
        }catch (Exception $e){
            return [0,0];
        }
    }

    public function addOrSum(&$data,$key,$value){
        if(!array_key_exists($key,$data)){
            $data[$key]=0;
        }
        $data[$key] += $value;
    }

    public function addOrSums(&$data,$values){
        foreach($values as $key=>$value){
            $this->addOrSum($data,$key,$value);
        }
    }

    public function round(&$data,$keys,$round=0){
        foreach($keys as $key){
            if(!isset($data[$key])){
                $data[$key]=0;
            }else if($data[$key]==null){
                $data[$key]=0;
            }else {
                $data[$key] = round($data[$key], $round);
            }
        }
    }

    public function getByKey(&$data,$keys){
        $tmpData = &$data;
        $lastReportGroup = null;
        for($i=0;$i<count($keys);$i++){


            $keyCouple = $keys[$i];
            $key = $keyCouple[0];
            $index = $keyCouple[1];

            $reportGroup = null;
            for($j=0;$j<count($tmpData);$j++){
                if(!isset($tmpData[$j])){
                    return $lastReportGroup;
                }
                if($tmpData[$j]->group==$index){
                    $reportGroup = $tmpData[$j];
                    break;
                }
            }
            if($reportGroup==null){
                $reportGroup =new ReportGroup($index,($i!=(count($keys)-1)));
                $tmpData[]= $reportGroup;
            }
            $lastReportGroup = $reportGroup;
            $tmpData = &$reportGroup->data;

        }
        return $lastReportGroup;
    }

    private function simpleBottomSums(&$data,&$result)
    {
        for($i=0;$i<count($data);$i++){
            if($i==0){
                foreach ($data[$i] as $k=>$v){
                    if(!array_key_exists($k,$result)){
                        $result[$k]=null;
                    }
                }
            }
            foreach ($data[$i] as $k=>$v){
                if(is_numeric($v)){
                    if($result[$k]==null){
                        $result[$k]=0;
                    }
                    $result[$k]+=$v;
                }
            }
        }
        $data[]=$result;
    }

    public function addBottomSums(&$data,$callback=null){
        $result = array();
        if($callback==null){
            $callback = function(&$data,&$result){
                $this->simpleBottomSums($data,$result);
            };
        }
        $callback($data,$result);
    }

    public function reorderListAndAddHeaders($listOfMap, $orderedFieldsMap){
        $result = [];

        foreach ($listOfMap as $map) {
            $newMap = [];

            foreach ($orderedFieldsMap as $key=>$value) {
                if (array_key_exists($key, $map)) {
                    $newMap[$key] = $map[$key];
                }
            }

            $result[] = $newMap;
        }

        $realKeys = [];
        foreach ($orderedFieldsMap as $key=>$value) {
            $realKeys[$key]=translate($value);
        }
        array_unshift($result,$realKeys);

        return $result;
    }


    public function reorderList($listOfMap, $orderedFieldsMap){
        $result = [];

        foreach ($listOfMap as $map) {
            $newMap = [];

            foreach ($orderedFieldsMap as $key=>$value) {
                if (array_key_exists($key, $map)) {
                    $newMap[$key] = $map[$key];
                }
            }

            $result[] = $newMap;
        }
        return $result;
    }

    public function addSideSums(&$data,$callback){
        for($i=0;$i<count($data);$i++){
            $callback($data[$i],$i);
        }
    }

    public function flatten(&$src, $keys){

        $result = [];
        $childKeys = array_slice($keys, 1);

        for($i=0; $i<count($src); $i++){

            if($src[$i] instanceof ReportGroup){
                $item = $src[$i];
                $baseItem =[];
                $group = $item->group;
                $keyId = $keys[0][0];
                $keyValue = $group;
                $baseItem[$keyId] = $keyValue;
                foreach ($item->tags as $key => $tag){
                    $baseItem[$key] = $tag;
                }
                $partial=null;
                if(count($childKeys)==0){
                    $partial = [$item->data];
                }else{
                    $partial = $this->flatten($item->data,$childKeys);
                }

                for($j=0;$j<count($partial);$j++){
                    foreach($baseItem as $key=>$value){
                        $partial[$j][$key]=$value;
                    }
                    $result[] = $partial[$j];
                }
            }
        }
        return $result;
    }

    function buildMonthDayView(
        array $data,
        string $dateField,
        callable $dayValueCallback,
        $groupBy
    ) {
        $result = [];

        foreach ($data as $item) {
            $date = new DateTime($item[$dateField]);
            $monthKey = $date->format('Y-m');
            $day = (int)$date->format('j');

            // --- group key ---
            if (is_callable($groupBy)) {
                $groupKey = $groupBy($item);
            } elseif (is_array($groupBy)) {
                $groupKey = [];
                foreach ($groupBy as $field) {
                    $groupKey[$field] = $item[$field] ?? null;
                }
            } else {
                $groupKey = $item[$groupBy] ?? null;
            }

            $groupHash = is_array($groupKey)
                ? md5(json_encode($groupKey))
                : (string)$groupKey;

            // --- init month ---
            if (!isset($result[$monthKey])) {

                $dt = new DateTime($monthKey . "-01");
                $monthSize = (int)$dt->format('t');

                $saturdays = [];
                for ($d = 1; $d <= $monthSize; $d++) {
                    $tmp = clone $dt;
                    $tmp->setDate((int)$dt->format('Y'), (int)$dt->format('m'), $d);

                    if ($tmp->format('N') == 6 ||$tmp->format('N') == 7) {
                        $saturdays[] = $d;
                    }
                }

                $result[$monthKey] = [
                    'month' => $monthKey,
                    'monthSize' => $monthSize,
                    'saturdays' => $saturdays,
                    'data' => []
                ];
            }

            // --- init group row ---
            if (!isset($result[$monthKey]['data'][$groupHash])) {

                $row = is_array($groupKey)
                    ? $groupKey
                    : ['group' => $groupKey];

                for ($d = 1; $d <= 31; $d++) {
                    $row[(string)$d] = null;
                }

                $result[$monthKey]['data'][$groupHash] = $row;
            }

            // --- previous value (important part) ---
            $prev = $result[$monthKey]['data'][$groupHash][(string)$day];


            // --- reducer-style update ---
            $result[$monthKey]['data'][$groupHash][(string)$day] =
                $dayValueCallback($prev, $item);
        }

        // cleanup
        foreach ($result as &$month) {
            $month['data'] = array_values($month['data']);
        }

        return array_values($result);
    }
}
