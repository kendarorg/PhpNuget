<?php

class SmallTxtDb
{
    var $cr;
    var $separator;
    var $dbFile;
    var $dbRows;
    var $columns;
    var $rows;
    
    public function __construct($dbFile,$dbRows,$loadData = true) 
    {
        $this->initialize($dbFile,$dbRows,$loadData);
    }
    
    public function SmallTxtDb($dbFile,$dbRows,$loadData = true)
    {
        $this->initialize($dbFile,$dbRows,$loadData);
    }
    
    private function initialize($dbFile,$dbRows,$loadData = true)
    {
        
        $this->cr="\n";
        $this->separator = ":|:";
        $this->dbFile = $dbFile;
        $this->dbRows = $dbRows;
        if(!file_exists($this->dbFile)){
            $fp = fopen($this->dbFile, 'w');
            fwrite($fp, $this->dbRows.$this->cr);
            fclose($fp);
        }
        $this->load($loadData);
    }

    private function load($loadData)
    {
        $fp = fopen($this->dbFile,"r");
        $content = fread($fp,filesize($this->dbFile));
        fclose($fp);
        $splitted = explode($this->cr,$content);
        $this->dbRows = $splitted[0];
       
        $cols = explode($this->separator,$this->dbRows);
        $this->columns=array();
        for ($i = 0; $i < sizeof($cols); $i++) {
            $this->columns[$cols[$i]]=$i;
        }
        
        $this->rows = array();
        if($loadData){
            for ($i = 1; $i < sizeof($splitted); $i++) {
                $splitted[$i] = trim($splitted[$i]);
                if($splitted[$i]!=""){
                    $row = array();
                    $vals = explode($this->separator,$splitted[$i]);
                    foreach($this->columns as $key => $value){
                        $row[$key]=unserialize($vals[$value]);
                    }
                    $this->rows[]=$row;
                }
            }
        }
    }
    
    public function add_row($rowHash)
    {
        // print_r($rowHash);print_r($this->columns);die();
        $hashRow = array();
        foreach($this->columns as $key => $value){
            $rowContent = null;
            if(array_key_exists($key,$rowHash)){
                $rowContent = $rowHash[$key];
            }
            $hashRow[$key]=$rowContent;
        }
        $this->rows[]=$hashRow;
        //print_r($this->columns);
     // print_r($this->rows);die();
    }
    
    public function delete_row($rowIndex)
    {
        unset($this->rows[$rowIndex]);
        $this->rows= array_values($this->rows);
    }
    
    function save()
    { 
       // print_r($this->rows);die();
        $fp = fopen($this->dbFile.".tmp", 'w');
        fwrite($fp, $this->dbRows.$this->cr);
        foreach($this->rows as $row){
            $towrite = array_fill(0,sizeof( $this->columns),null);
            foreach($this->columns as $key => $value){
                $towrite[$value]=serialize($row[$key]);
            }
            $rowString = implode($this->separator,$towrite);
            fwrite($fp, $rowString.$this->cr);
        }
        fclose($fp);
        unlink($this->dbFile);
        rename($this->dbFile.".tmp",$this->dbFile);
    }
}

?>