<?php

/*

array(1) {
  [0]=>
  object(Operator)#47 (4) {
    ["Type"]=>
    string(8) "function"
    ["Value"]=>
    string(5) "doand"
    ["Id"]=>
    NULL
    ["Children"]=>
    array(2) {
      [0]=>
      object(Operator)#46 (4) {
        ["Type"]=>
        string(8) "function"
        ["Value"]=>
        string(5) "doand"
        ["Id"]=>
        NULL
        ["Children"]=>
        array(2) {
          [0]=>
          object(Operator)#45 (4) {
            ["Type"]=>
            string(8) "function"
            ["Value"]=>
            string(5) "doand"
            ["Id"]=>
            NULL
            ["Children"]=>
            array(2) {
              [0]=>
              object(Operator)#41 (4) {
                ["Type"]=>
                string(8) "function"
                ["Value"]=>
                string(4) "door"
                ["Id"]=>
                NULL
                ["Children"]=>
                array(2) {
                  [0]=>
                  object(Operator)#40 (4) {
                    ["Type"]=>
                    string(8) "function"
                    ["Value"]=>
                    string(4) "doeq"
                    ["Id"]=>
                    NULL
                    ["Children"]=>
                    array(2) {
                      [0]=>
                      object(Operator)#11 (4) {
                        ["Type"]=>
                        string(5) "field"
                        ["Value"]=>
                        string(15) "TargetFramework"
                        ["Id"]=>
                        NULL
                        ["Children"]=>
                        array(0) {
                        }
                      }
                      [1]=>
                      object(Operator)#13 (4) {
                        ["Type"]=>
                        string(6) "string"
                        ["Value"]=>
                        string(0) ""
                        ["Id"]=>
                        NULL
                        ["Children"]=>
                        array(0) {
                        }
                      }
                    }
                  }
                  [1]=>
                  object(Operator)#17 (4) {
                    ["Type"]=>
                    string(8) "function"
                    ["Value"]=>
                    string(11) "substringof"
                    ["Id"]=>
                    NULL
                    ["Children"]=>
                    array(2) {
                      [0]=>
                      object(Operator)#19 (4) {
                        ["Type"]=>
                        string(6) "string"
                        ["Value"]=>
                        string(5) "net45"
                        ["Id"]=>
                        NULL
                        ["Children"]=>
                        array(0) {
                        }
                      }
                      [1]=>
                      object(Operator)#20 (4) {
                        ["Type"]=>
                        string(5) "field"
                        ["Value"]=>
                        string(15) "TargetFramework"
                        ["Id"]=>
                        NULL
                        ["Children"]=>
                        array(0) {
                        }
                      }
                    }
                  }
                }
              }
              [1]=>
              object(Operator)#42 (4) {
                ["Type"]=>
                string(8) "function"
                ["Value"]=>
                string(4) "doeq"
                ["Id"]=>
                NULL
                ["Children"]=>
                array(2) {
                  [0]=>
                  object(Operator)#22 (4) {
                    ["Type"]=>
                    string(5) "field"
                    ["Value"]=>
                    string(12) "IsPreRelease"
                    ["Id"]=>
                    NULL
                    ["Children"]=>
                    array(0) {
                    }
                  }
                  [1]=>
                  object(Operator)#24 (4) {
                    ["Type"]=>
                    string(7) "boolean"
                    ["Value"]=>
                    bool(false)
                    ["Id"]=>
                    NULL
                    ["Children"]=>
                    array(0) {
                    }
                  }
                }
              }
            }
          }
          [1]=>
          object(Operator)#43 (4) {
            ["Type"]=>
            string(8) "function"
            ["Value"]=>
            string(4) "doeq"
            ["Id"]=>
            NULL
            ["Children"]=>
            array(2) {
              [0]=>
              object(Operator)#27 (4) {
                ["Type"]=>
                string(5) "field"
                ["Value"]=>
                string(6) "Listed"
                ["Id"]=>
                NULL
                ["Children"]=>
                array(0) {
                }
              }
              [1]=>
              object(Operator)#29 (4) {
                ["Type"]=>
                string(7) "boolean"
                ["Value"]=>
                bool(true)
                ["Id"]=>
                NULL
                ["Children"]=>
                array(0) {
                }
              }
            }
          }
        }
      }
      [1]=>
      object(Operator)#44 (4) {
        ["Type"]=>
        string(8) "function"
        ["Value"]=>
        string(4) "doeq"
        ["Id"]=>
        NULL
        ["Children"]=>
        array(2) {
          [0]=>
          object(Operator)#30 (4) {
            ["Type"]=>
            string(5) "field"
            ["Value"]=>
            string(6) "Listed"
            ["Id"]=>
            NULL
            ["Children"]=>
            array(0) {
            }
          }
          [1]=>
          object(Operator)#32 (4) {
            ["Type"]=>
            string(7) "boolean"
            ["Value"]=>
            bool(true)
            ["Id"]=>
            NULL
            ["Children"]=>
            array(0) {
            }
          }
        }
      }
    }
  }
}
*/

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
    
    public function __construct($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData = true) 
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData);
    }
    
    public function SmallTxtDb($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData = true)
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData);
    }
	
	public function doQuery($query){
		//echo $query;
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		$result = mysqli_query( $connection,$query );
		$data = array();
		
		$r = explode(":|:",$this->dbRows);
		$t = explode(":|:",$this->FieldTypes());
		
		if(gettype($result)=="boolean"){
			mysqli_close($connection);
			return $data;
		}
		
		while ( $list = mysqli_fetch_array($result,MYSQLI_ASSOC) ) {
			
			$item = $this->CreateItem($list);
			for($i=0;$i<sizeof($r);$i++){
				$k = $r[$i];
				$kt = $t[$i];
				$val = $list[strtolower($k)];
				if($kt=="object"){
					$item->$k = unserialize($val);
				}else if($kt=="number" && $val!=null){
					$item->$k= intval($val);
				}else if($kt=="boolean"){
					$item->$k= filter_var($val, FILTER_VALIDATE_BOOLEAN);
				}else{
					$item->$k = $val;
				}
			}
			
			array_push($data,$item);
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
	
	var $_keys=array();
    
    private function initialize($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData = true)
    {
        $pi = pathinfo($dbFile);
        $this->cr="\n";
        $this->separator = ":|:";
        $this->dbFile =  $pi["filename"];
        $this->dbRows = $dbRows;
        $this->dbTypes = $dbTypes;
		$this->_version = $version;
		$this->_keys = array();
		foreach(explode(":|:",$keys) as $kk){
			$this->_keys[strtolower($kk)]=$kk;
			$this->_keys[$kk]=$kk;
		}
		
		
		
		$fieldTypes = explode(":|:",$this->FieldTypes());
		
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
			
			$pks = array();
			$toJoin = array();
			for($i=0;$i<sizeof($fieldNames);$i++){
				$field = strtolower($fieldNames[$i]);
				$type = strtolower($fieldTypes[$i]);
				
				$isPk =false;
				if(isset($this->_keys[$field])){
					array_push($pks,"`".$field."`");
					$isPk =true;
				}
				if($type=="number"){
					$toJoin[$i]="INT";
				}else if($type=="boolean"){
					$toJoin[$i]="BOOLEAN";
				}else{
					$toJoin[$i]="TEXT";
				}
				if($isPk  && $toJoin[$i]=="TEXT"){
					$toJoin[$i]="CHAR(128)";
				}
					//$toJoin[$i] = "`".$field."` ".$toJoin[$i]." PRIMARY KEY";
				
				$toJoin[$i] = "`".$field."` ".$toJoin[$i];
			}
			
			$create = $create." (".join(",",$toJoin).", PRIMARY KEY (".join(",",$pks)."))";
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
        
    }
	
	public function update_row($rowHash,$keys)
    {
		$this->rows[]=$this->VerifyTypes($rowHash);
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		
		$fieldNames = explode(":|:",$this->FieldNames());
		$fieldTypes = explode(":|:",$this->FieldTypes());
		$create = "UPDATE ".$this->dbFile." SET ";
		$values= array();
		//var_dump($fieldNames);
		//var_dump($rowHash);
		$toJoin = array();
		for($i=0;$i<sizeof($fieldNames);$i++){
			$field = $fieldNames[$i];
			$type = strtolower($fieldTypes[$i]);

			if(isset( $keys[$field])){
				$value = $keys[$field];
				if($type=="boolean"){
					array_push($toJoin,"`".$field."`=".($value==false?"false":"true"));
				}else if($type=="number" ){
					array_push($toJoin,"`".$field."`=".$value);
				}else if($type=="object" || gettype($value)=="array"){
					array_push($toJoin,"`".$field."`="."'".mysqli_real_escape_string($connection,serialize($value))."'");
				}else{
					//echo $field.":".gettype($value)."--";
					array_push($toJoin,"`".$field."`="."'".mysqli_real_escape_string($connection,$value)."'");
				}
			}
		}
		
		for($i=0;$i<sizeof($fieldNames);$i++){
			$field = $fieldNames[$i];
			$type = strtolower($fieldTypes[$i]);
			if(isset( $rowHash[$field]) && !isset( $keys[$field])){
				$value = $rowHash[$field];
				if($type=="boolean"){
					array_push($values,"`".$field."`=".($value==false?"false":"true"));
				}else if($type=="number" ){
					array_push($values,"`".$field."`=".$value);
				}else if($type=="object" || gettype($value)=="array"){
					array_push($values,"`".$field."`="."'".mysqli_real_escape_string($connection,serialize($value))."'");
				}else{
					//echo $field.":".gettype($value)."--";
					array_push($values,"`".$field."`="."'".mysqli_real_escape_string($connection,$value)."'");
				}
			}
		}
		$create = $create." ".join(",",$values)." WHERE ".join(" and ",$toJoin);
		//echo $create;die();
		$this->doQueryExecute($create);
	}
    
    public function add_row($rowHash)
    {
		//$rowHash = array_change_key_case($rowHash, CASE_LOWER);

        $this->rows[]=$this->VerifyTypes($rowHash);
		$connection = mysqli_connect(__MYSQL_SERVER__, __MYSQL_USER__, __MYSQL_PASSWORD__,__MYSQL_DB__);
		
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
				if($type=="boolean"){
					array_push($values,$value==false?"false":"true");
				}else if($type=="number" ){
					array_push($values,$value);
				}else if($type=="object" || gettype($value)=="array"){
					array_push($values,"'".mysqli_real_escape_string($connection,serialize($value))."'");
				}else{
					//echo $field.":".gettype($value)."--";
					array_push($values,"'".mysqli_real_escape_string($connection,$value)."'");
				}
				array_push($toJoin,"`".$field."`");
			}
		}
		$create = $create." (".join(",",$toJoin).") VALUES (".join(",",$values).")";
		//echo $create;
		$this->doQueryExecute($create);
		//echo $create;
        //print_r($this->columns);
     // print_r($this->rows);die();
    }
    
    public function delete_row($indexs)
    {
        $this->rows[]=$this->VerifyTypes($rowHash);
		
		$fieldNames = explode(":|:",$this->FieldNames());
		$fieldTypes = explode(":|:",$this->FieldTypes());
		$create = "DELETE FROM ".$this->dbFile." ";
		$values= array();
		//var_dump($fieldNames);
		//var_dump($rowHash);
		for($i=0;$i<sizeof($fieldNames);$i++){
			$field = $fieldNames[$i];
			$type = strtolower($fieldTypes[$i]);
			if(isset( $rowHash[$field])){
				$value = $rowHash[$field];
				if($type=="number" || $type=="boolean"){
					array_push($values,"`".$field."`=".$value);
				}else{
					array_push($values,"`".$field."`="."'".$value."'");
				}
			}
		}
		$create = $create." WHERE ".join(" ABD ",$values);
		$this->doQueryExecute($create);
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
	
	
	
	public function GetAll($limit=99999,$skip=0,$objectSearch=null)
	{
		if($objectSearch==null){
			$select = "SELECT * FROM ".$this->dbFile." LIMIT ".$limit." OFFSET ".$skip ;
			return $this->doQuery($select);
		}
		
		//$objectSearch->dump();
		//die();
		
		$where = trim($objectSearch->ToMySql());
		
		$select = "SELECT * FROM ".$this->dbFile;
		if($where!=null && strlen($where)>0){
			$select = $select." WHERE ".$where;
		}
		$select = $select." LIMIT ".$limit." OFFSET ".$skip ;
		
		//echo "<!-- ".$select."-->";
		
		
		return $this->doQuery($select);
		
		
		die();
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