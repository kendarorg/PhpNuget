<?php
function newMySqlDb($a,$b,$c,$d,$e)
{
	return new MySqlDb($a,$b,$c,$d,$e);
}
class MySqlDb
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
			throw new Exception("Mismatch types/fields 01");
		}
		
        for($i=0;$i<sizeof($fieldNames);$i++){
            $field = $fieldNames[$i];
            $type = $fieldTypes[$i];
			if(array_key_exists($field,$row)){
				$value = $row[$field];
				$row[$field] = $this->VerifyType($value,$type); 
			}
        }
        return $row;   
    }
    
    public function __construct($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData = true) 
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData);
    }
    
    public function MySqlDb($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData = true)
    {
        $this->initialize($version,$dbFile,$dbRows,$dbTypes,$keys,$loadData);
    }
	
	public function doQuery($query){
		//echo $query."\r\n<br>";
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
				throw new Exception("Mismatch types/fields 02");
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
        $result = $this->doQuery("SELECT Version FROM versions");
		if(sizeof($result)!=1) throw new Exception("Wrong database!");
		if($result[0]["Version"]!=__DB_VERSION__) throw new Exception("Wrong database version! Expected ".__DB_VERSION__." but founded ".$result[0]["Version"]);
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
		//var_dump($rowHash);
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
		
		$this->doQueryExecute($create);
		//echo $create;
        //print_r($this->columns);
     // print_r($this->rows);die();
    }
    
    public function delete_row($indexs)
    {
        $this->rows[]=$this->VerifyTypes($indexs);
		
		$fieldNames = explode(":|:",$this->FieldNames());
		$fieldTypes = explode(":|:",$this->FieldTypes());
		$create = "DELETE FROM ".$this->dbFile." ";
		$values= array();
		//var_dump($fieldNames);
		//var_dump($rowHash);
		for($i=0;$i<sizeof($fieldNames);$i++){
			$field = $fieldNames[$i];
			$type = strtolower($fieldTypes[$i]);
			if(isset( $indexs[$field])){
				$value = $indexs[$field];
				if($type=="number" || $type=="boolean"){
					array_push($values,"`".$field."`=".$value);
				}else{
					array_push($values,"`".$field."`="."'".$value."'");
				}
			}
		}
		$create = $create." WHERE ".join(" AND ",$values);
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
		
		$fieldNames = explode(":|:",$this->FieldNames());
		$fieldTypes = explode(":|:",$this->FieldTypes());
		
		$where = trim($objectSearch->ToMySql());
		
		$select = "SELECT * FROM ".$this->dbFile;
		if($where!=null && strlen($where)>0){
			$select = $select." WHERE ".$where;
		}
		
		if($objectSearch!=null){
			$select = $select." ".$objectSearch->DoSortMySql($fieldNames,$fieldTypes);
		}
		if($objectSearch!=null){
			$gp = $objectSearch->DoGroupByMySql($fieldNames,$fieldTypes);
			if($gp!=""){
				$selNames = $objectSearch->BuildSelectNames($fieldNames,$fieldTypes);
				$select = "SELECT ".$selNames." FROM (".$select.") as TB ".$gp;
			}
		}
		
		$select = $select." LIMIT ".$limit." OFFSET ".$skip ;
		
		//echo "<!-- ".$select."-->";
		
		//echo $select;
		return  $this->doQuery($select);
	}
}

?>