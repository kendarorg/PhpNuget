<?php

class SmallTxtDb
{
    var $cr;
    var $separator;
    var $dbFile;
    var $dbRows;
    var $columns;
    var $rows;
    var $dbTypes;
    
    public function FieldNames()
    {
        return $this->dbRows;
    }
    
    public function FieldTypes()
    {
        return $this->dbTypes;
    }
    
    
    public function VerifyTypes($row)
    {
        $fieldNames = explode(":|:",$this->FieldNames());
        $fieldTypes = explode(":|:",$this->FieldTypes());
        
        for($i=0;$i<sizeof($fieldNames);$i++){
            $field = $fieldNames[$i];
            $type = $fieldTypes[$i];
            $value = $row[$field];
            $row[$field] = $this->VerifyType($value,$type); 
        }
        return $row;   
    }
    
    public function __construct($dbFile,$dbRows,$dbTypes,$loadData = true) 
    {
        $this->initialize($dbFile,$dbRows,$dbTypes,$loadData);
    }
    
    public function SmallTxtDb($dbFile,$dbRows,$dbTypes,$loadData = true)
    {
        $this->initialize($dbFile,$dbRows,$dbTypes,$loadData);
    }
    
    private function initialize($dbFile,$dbRows,$dbTypes,$loadData = true)
    {
        
        $this->cr="\n";
        $this->separator = ":|:";
        $this->dbFile = $dbFile;
        $this->dbRows = $dbRows;
        $this->dbTypes = $dbTypes;
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
                        $row[$key]=$this->re_cr_lf(unserialize($vals[$value]));
                        
                    }
                    $this->rows[]=$this->VerifyTypes($row);
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
        $this->rows[]=$this->VerifyTypes($hashRow);
        //print_r($this->columns);
     // print_r($this->rows);die();
    }
    
    public function delete_row($rowIndex)
    {
        unset($this->rows[$rowIndex]);
        $this->rows= array_values($this->rows);
    }
    
    function de_cr_lf($value)
    {
        $v = str_replace(["\r\n","\r\f","\n","\r","\f"],"@CRLF@",$value);
        return $v;
    }
    
    function re_cr_lf($value)
    {
        $v = str_replace("@CRLF@","\n",$value);
        return $v;
    }
    
    function save()
    { 
       // print_r($this->rows);die();
        $fp = fopen($this->dbFile.".tmp", 'w');
        fwrite($fp, $this->dbRows.$this->cr);
        foreach($this->rows as $row){
            $towrite = array_fill(0,sizeof( $this->columns),null);
            $row = $this->VerifyTypes($row);
            foreach($this->columns as $key => $value){
                $towrite[$value]=serialize($this->de_cr_lf($row[$key]));
            }
            $rowString = implode($this->separator,$towrite);
            fwrite($fp, $rowString.$this->cr);
        }
        fclose($fp);
        unlink($this->dbFile);
        rename($this->dbFile.".tmp",$this->dbFile);
    }
    
    public function VerifyType($value,$type)
    {
        //echo $value."=".$type."<br>";
        $type = strtolower($type);
        switch($type){
            case("number"):
                {
                    if(is_numeric($value)) return $value;  
                    return (int)$value; 
                }
            case("bool"):
                {
                    if(is_bool($value))return $value;
                    if(is_numeric($value)) return $value > 0;
                    $value = strtolower($value);
                    if($value=="true" || $value=="false") return $value=="true"?true:false;
                    if($value=="N" || $value=="Y") return $value=="Y"?true:false;
                    if($value=="NULL" || is_null($value))return false;
                    return false;
                }
            case("array"):
            case("date"):
            case("object"):
            case("string"):
            default:
                return $value;   
        }
    }
}

?>