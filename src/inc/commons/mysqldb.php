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
	public $BuildItem;
    
    public function FieldNames()
    {
        return $this->dbRows;
    }
	
	public static function RowTypes($rows,$types)
	{
		$r = explode(":|:",$rows);
		$t = explode(":|:",$types);
		$result = array();
		for($i=0;$i<sizeof($r);$i++){
			$result[$r[$i]]=$t[$i];
		}
		return $result;
	}
    
    public function FieldTypes()
    {
        return $this->dbTypes;
    }
	
	public function CreateItem($array)
	{
		$bi = $this->BuildItem;
		$item = $bi();
		foreach ($array as $key=> $value) {
			$item->$key = $value;
		}
		return $item;
	}
    
    
    public function VerifyTypes($row)
    {
        $fieldNames = explode(":|:",$this->FieldNames());
        $fieldTypes = explode(":|:",$this->FieldTypes());
        
		
		if(sizeof($fieldNames)!=sizeof($fieldTypes)){
			throw new Exception("Mismatch types/fields");
		}
		
        for($i=0;$i<sizeof($fieldNames);$i++){
            $field = $fieldNames[$i];
            $type = $fieldTypes[$i];
            $value = $row[$field];
            $row[$field] = $this->VerifyType($value,$type); 
        }
        return $row;   
    }
    
    public function __construct($version,$dbFile,$dbRows,$dbTypes,$loadData = true) 
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$loadData);
    }
    
    public function SmallTxtDb($version,$dbFile,$dbRows,$dbTypes,$loadData = true)
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$loadData);
    }
	
	public function doQuery($query){
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		$result = mysqli_query( $connection,$query );
		$data = array();

		while ( $list = mysqli_fetch_array($result,MYSQLI_ASSOC) ) {
			array_push($data,$list);
		}
		mysqli_close($connection);
		return $data;
	}
	
	public function doQueryScalar($query){
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		$result = mysqli_query( $connection,$query );
		$data = mysqli_fetch_array($result,MYSQLI_ASSOC);
			
		mysqli_close($connection);
		return $data;
	}
	
	public function doQueryExecute($query){
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		$result = mysqli_query( $connection,$query );
		mysqli_close($connection);
		return $result;
	}
	
	
    
    private function initialize($version,$dbFile,$dbRows,$dbTypes,$loadData = true)
    {
        $pi = pathinfo($dbFile);
        $this->cr="\n";
        $this->separator = ":|:";
        $this->dbFile =  $pi["filename"];
        $this->dbRows = $dbRows;
        $this->dbTypes = $dbTypes;
		$this->_version = $version;
		
		
		$result = $this->doQueryScalar("SELECT COUNT(*) AS counter FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '"
			.__MYSQL_DB__."' AND TABLE_NAME = '"
			.$this->dbFile."'");
		if($result["counter"]==0){
			$create = "CREATE TABLE ".$this->dbFile." ";
			
			$fieldNames = explode(":|:",$this->FieldNames());
			$fieldTypes = explode(":|:",$this->FieldTypes());
			
			
			if(sizeof($fieldNames)!=sizeof($fieldTypes)){
				throw new Exception("Mismatch types/fields");
			}
			
			$toJoin = array();
			for($i=0;$i<sizeof($fieldNames);$i++){
				$field = strtolower($fieldNames[$i]);
				$type = strtolower($fieldTypes[$i]);
				if($type=="number"){
					$toJoin[$i]="INT";
				}else if($type=="boolean"){
					$toJoin[$i]="TINYINT";
				}else{
					$toJoin[$i]="TEXT";
				}
				if($field == "id"){
					if($toJoin[$i]=="TEXT"){
						$toJoin[$i]="CHAR(128)";
					}
					$toJoin[$i] = "`".$field."` ".$toJoin[$i]." PRIMARY KEY";
				}else{
					$toJoin[$i] = "`".$field."` ".$toJoin[$i];
				}
			}
			$create = $create." (".join(",",$toJoin).")";
			$this->doQueryExecute($create);
			//echo $create;die();
		}
		
        /*if(!file_exists($this->dbFile)){
            $fp = fopen($this->dbFile, 'w');
            fwrite($fp, "@Version:".$this->_version.$this->cr);
            fwrite($fp, $this->dbRows.$this->cr);
            fclose($fp);
        }*/
        //$this->load($loadData);
    }
	
	private $_version;
	public function Version()
	{
		return $this->_version;
	}

    private function load($loadData)
    {
        $fp = fopen($this->dbFile,"r");
        $content = fread($fp,filesize($this->dbFile));
        fclose($fp);
        $splitted = explode($this->cr,$content);
		$st = 0;
		$firstRow = $splitted[$st];
		if(starts_with($firstRow,"@Version:")){
			$kk=explode(":",$firstRow);
			$currentVersion = $kk[1];
			
			if($this->_version != strtolower(trim($currentVersion))){
				throw new Exception("Founded db version ".$currentVersion." instead of ".$this->_version);
			}
			$st=1;
		}
        $this->dbRows = $splitted[$st];
		$st++;
       
        $cols = explode($this->separator,$this->dbRows);
        $this->columns=array();
        for ($i = 0; $i < sizeof($cols); $i++) {
            $this->columns[$cols[$i]]=$i;
        }
		
        $this->rows = array();
        if($loadData){
            for ($i = $st; $i < sizeof($splitted); $i++) {
			
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
		//$rowHash = array_change_key_case($rowHash, CASE_LOWER);

        $this->rows[]=$this->VerifyTypes($rowHash);
		
		$fieldNames = explode(":|:",$this->FieldNames());
		$fieldTypes = explode(":|:",$this->FieldTypes());
		$create = "INSERT INTO ".$this->dbFile." ";
		$values= array();
		//var_dump($fieldNames);
		//var_dump($rowHash);
		$toJoin = array();
		for($i=0;$i<sizeof($fieldNames);$i++){
			$field = $fieldNames[$i];
			$type = strtolower($fieldTypes[$i]);
			if(isset( $rowHash[$field])){
				$value = $rowHash[$field];
				if($type=="number" || $type=="boolean"){
					array_push($values,$value);
				}else{
					array_push($values,"'".$value."'");
				}
				array_push($toJoin,$field);
			}
		}
		$create = $create." (".join(",",$toJoin).") VALUES (".join(",",$values).")";
		$this->doQueryExecute($create);
		//echo $create;
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
        $v = str_replace(array("\r\n","\r\f","\n","\r","\f"),"@CRLF@",$value);
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
        /*$fp = fopen($this->dbFile.".tmp", 'w');
		fwrite($fp, "@Version:".$this->_version.$this->cr);
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
        rename($this->dbFile.".tmp",$this->dbFile);*/
    }
    
    public function VerifyType($value,$type)
    {
        //echo $value."=".$type."<br>";
        $type = strtolower($type);
        switch($type){
            case("number"):
                {
					$value = strtolower($value);
                    if(is_numeric($value)) return $value;  
                    return (int)$value; 
                }
            case("boolean"):
                {
					$value = strtolower($value);
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
	
	
	
	public function GetAll($limit,$skip=0,$objectSearch=null)
	{
		$r = explode(":|:",$this->dbRows);
		$t = explode(":|:",$this->dbTypes);
		$rowTypes = array();
		for($i=0;$i<sizeof($r);$i++){
			$rowTypes[$r[$i]]=$t[$i];
		}
		
		$toSort = array();
		$bi = $this->BuildItem;
		foreach($this->rows as $row){
		
			$item = $bi();
			foreach ($row as $key=> $value) {
                $item->$key = $value;
            }
			if($objectSearch!=null){
				if($objectSearch->Execute($item)){
					$toSort[] = $item;
				}
			}else{
				$toSort[] = $item;
			}
		}
		
		/*$result = array();
		foreach($toSort as $row){
			if($objectSearch!=null){
				if(!$objectSearch->Execute($row)){
					continue;
				}
			}
			$result[]=$row;
		}
		$toSort = $result;*/
		
		if($objectSearch!=null){
			$toSort = $objectSearch->DoSort($toSort,$rowTypes);
			$toSort = $objectSearch->DoGroupBy($toSort);
		}
		$result = array();
		
		
		
		foreach($toSort as $row){
			if($skip>0){
				$skip--;
				continue;
			}
				
            $result[]=$row;
			$limit--;
			if($limit==0){
				break;
			}
        }
		
		return $result;
	}
}

?>