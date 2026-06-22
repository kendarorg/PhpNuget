<?php


abstract class BaseModel
{
    protected function getJsonFields(){
        return array();
    }
    public function setupJoinedSearch(
        $searchQuery,
        $searchTerm,
        $fields,
        $join,
        $extraSearchTermsMap,
        $removeSearchTerm = false,
        $loadEventWithMissingTerm = false,
    ){
        if( is_array($searchQuery->searchTerms) && array_key_exists($searchTerm,$searchQuery->searchTerms)&&
            $searchQuery->searchTerms[$searchTerm]!=""&&
            $searchQuery->searchTerms[$searchTerm]!=null){
            $searchTermValue = $searchQuery->searchTerms[$searchTerm];
            if($removeSearchTerm){
                unset($searchQuery->searchTerms[$searchTerm]);
            }
            if(!is_array($searchQuery->fields) ){

                $searchQuery->fields = [];
            }
            if(!in_array($this->getTableName().".*", $searchQuery->fields)){
                $searchQuery->fields = [$this->getTableName().".*"];
            }
            if($searchQuery->join===null || $searchQuery->join===''){
                $searchQuery->join = " ".$this->getTableName()." ";
            }

            foreach ($fields as $field){
                $searchQuery->fields[] = $field;
            }
            foreach ($extraSearchTermsMap as $field=>$term){
                if($term==="_".$searchTerm){
                    $searchQuery->searchTerms[$field] = $searchTermValue;
                }else{
                    $searchQuery->searchTerms[$field] = $term;
                }

            }
            if($searchQuery->join===null || $searchQuery->join===''){
                $searchQuery->join = " ".$this->getTableName();
            }
            $searchQuery->join .=$join;
            return true;
        }else if($loadEventWithMissingTerm){
            if(!is_array($searchQuery->fields) ){
                $searchQuery->fields = [];
            }
            if(!in_array($this->getTableName().".*", $searchQuery->fields)){
                $searchQuery->fields = [$this->getTableName().".*"];
            }
            if($searchQuery->join===null || $searchQuery->join===''){
                $searchQuery->join = " ".$this->getTableName()." ";
            }

            foreach ($fields as $field){
                $searchQuery->fields[] = $field;
            }
            foreach ($extraSearchTermsMap as $field=>$term){
                if($term==="_".$searchTerm){
                    continue;
                }else{
                    $searchQuery->searchTerms[$field] = $term;
                }

            }
            if($searchQuery->join===null || $searchQuery->join===''){
                $searchQuery->join = " ".$this->getTableName();
            }
            $searchQuery->join .=$join;
            return true;
        }
        return false;
    }
    protected function beforeCreate(&$data){

    }
    /**
     * @param $data
     * @return mixed
     * @throws Exception
     */
    protected function createInternal(&$data): mixed
    {
        $ids = $this->getIdColumns();
        $autoIncrementColumn = $this->getCustomAutoIncrementColumn();
        $uuidColumn = $this->getCustomUuidColumn();
        $normalAutoIncrement = $this->getNormalAutoIncrement();
        if ($autoIncrementColumn) {
            $data[$autoIncrementColumn] = $this->getNextLong($data);
            $this->lastId = $data[$autoIncrementColumn];
        }
        if ($uuidColumn) {
            $data[$uuidColumn] = generateUuidV7();
            $this->lastId = $data[$uuidColumn];
        }
        if ($normalAutoIncrement) {
            unset($data[$normalAutoIncrement]);
        }
        $this->beforeCreate($data);
        $this->beforeModification($data);
        $existingIds = 0;

        $jsonFields = $this->getJsonFields();
        foreach ($ids as $id) {
            if (isset($data[$id])) {
                if ($normalAutoIncrement === $id) continue;
                $existingIds++;
            }
        }

        if ($existingIds != count($ids) && !$normalAutoIncrement) {
            $this->rollback();
            $this->log->error("Can't update records with ids(1): " . implode(',', $ids) . " needed $existingIds");
            throw new Exception("Missing ids");
        }
        $cols = $this->getUpdateableFields();
        foreach ($this->getIdColumns() as $key) $cols[$key] = [];
        //$cols = array_merge($this->getUpdateableFields(),$this->getIdColumns());

        $fields = [];
        $vars = [];
        $values = [];

        foreach ($cols as $col=>$val) {
                $value = null;
                 if (isset($data[$col])) {
                     $value = $data[$col];
                 }
                if ($val!=null && isset($val['default']) && ($value == null || $value == '')) {
                    $value = $val['default'];
                }else if($value == null || $value == ''){
                    continue;
                }

                 $vars[] = "?";
                 $fields[] = $col;

                if (in_array($col, $jsonFields) && !is_string($value)) {
                    $values[] = json_encode($value);
                } else {
                    $values[] = $value;
                }
        }
        $sql = "INSERT INTO " . $this->getTableName() . " (" . join(",", $fields) . ") VALUES (" . join(",", $vars) . ")";

        $db = $this->getDbConnection();

        error_log($this->audit->approximateQuery($sql, $values));
        $this->audit->execute($this, "CREATE", $db, $sql, ...$values);
        if ($normalAutoIncrement) {
            $this->lastId = $db->lastInsertId();
            $data[$normalAutoIncrement] = $this->lastId;
        }
        $this->updateDependencies($data);
        return $data;
    }

    protected function setupDefaultUpdateable(...$dt){
        $result = [];
        for($i=0;$i<count($dt);$i++){
            $result[$dt[$i]] = [];
        }
        return $result;
    }
    function isNumericLike($value) {
        if($value==null) return false;
        if(is_array($value))return false;
        if (is_numeric($value)) {
            return true;
        }
        if(is_string($value)){
            $value = trim($value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = trim((string)$value);
        }
        if(is_string($value) && strlen($value)>0) {
            return is_numeric($value);
        }

        return false;
    }
    static $extensions = [
        'gt','gte','gte','lt','lte','bitmask','bitmaskend',
        'eq','like','in','null','notnull','ne',"eqornull"
    ];
    function splitExtension(string $delimiter, string $string): array {
        foreach (self::$extensions as $suffix) {
            if (str_ends_with($string, "_".$suffix)) {
                $realValue =substr($string, 0, -strlen("_".$suffix));
                return [$realValue,$suffix];
            }
        }
        return [$string];
    }
    /**
     * @return {"term":{multi:[fields]}},
     */
    abstract protected function getSearchTerms();
    abstract protected function getUpdateableFields();

    abstract protected function getTableName();

    protected function getIdColumns(){return ["id"];}
    protected  function getDefaultOrderBy(){return [];}

    protected  function getNormalAutoIncrement(){return null;}

    protected function getCustomAutoIncrementColumn(){ return "id";}
    protected function getCustomUuidColumn(){ return null;}

    public $audit;
    public $log;
    protected $lastId = null;

    public function __construct($db = null)
    {
        $this->audit = GlobalRegistry::get('audit');
        if($db==null) {
            $this->db = $this->getDbConnection();
        }else {
            $this->db = $db;
            $this->externalTransaction =true;
        }
        $this->log = LogManager::getLogger(get_class($this));
    }

    var $externalTransaction=false;
    public $db = null;

    public function startTransaction()
    {
        if(!$this->externalTransaction ) {
            $db = $this->getDbConnection();
            $db->beginTransaction();
        }
    }

    public function commit()
    {
        if(!$this->externalTransaction ) {
            $this->db->commit();
        }
    }

    public function rollback()
    {
        if(!$this->externalTransaction ) {
            $this->db->rollBack();
        }
    }

    protected function getDbConnection()
    {
        if($this->db == null) {
            $this->db = getDbConnection();
        }
        return $this->db;
    }

    function getAll($from = 0, $count = 101)
    {
        $sq = new SearchQuery();
        $sq->orderBy = $this->getDefaultOrderBy();
        $sq->from = $from;
        $sq->count = $count;
        return $this->search($sq);
    }

    protected function beforeModification(&$data){

    }


    public function update(&$data)
    {
        $this->startTransaction();
        try{
        $ids = $this->getIdColumns();
        $existingIds = 0;
        foreach ($ids as $id) {
            if (isset($data[$id])) {
                $existingIds++;
            }
        }

        $this->beforeModification($data);
        if ($existingIds != count($ids)) {
            $this->rollback();
            $this->log->error("Can't update records with ids (2): |".implode(',',$ids)."| needed $existingIds");
            throw new Exception("Missing ids");
        }
        $cols = $this->getUpdateableFields();


        $sql = "UPDATE ".$this->getTableName()." SET ";
        $sets =[];
        $values = [];
        $jsonFields = $this->getJsonFields();
        foreach ($data as $key => $value) {
            if(in_array($key, $ids)) continue; //Ids should not be changed...never


            if(array_key_exists($key, $cols)){
                $sets[] = " $key=? ";
                if(isset($cols[$key]['default']) && ($value===null|| $value==='')){
                    $value=$cols[$key]['default'];
                }
                if(in_array($key, $jsonFields)){
                    $values[] = json_encode($value);
                }else {
                    $values[] = $value;
                }
            }
        }
        $sql .= implode(', ', $sets);
        $sets=[];
        foreach ($data as $key => $value) {
            if(in_array($key, $cols))continue;
            if(in_array($key, $ids)){
                if($value===null|| $value==='')continue;
                $sets[] = " $key=? ";
                $values[] = $value;
            }
        }
        $sql .= " WHERE ".implode(' AND ', $sets);
        $db = $this->getDbConnection();

        error_log($this->audit->approximateQuery($sql,$values));
        $this->audit->execute($this,"UPDATE",$db,$sql,...$values);
        $this->updateDependencies($data);

        $this->commit();
        }catch (Exception $e){
            $this->rollback();
            throw $e;
        }
    }

    protected function updateDependencies(&$data){

    }

    public function getLastId(){
        return $this->lastId;
    }

    public function create(&$data){
        $this->startTransaction();
        try {
            $data = $this->createInternal($data);

            $this->commit();
            return $data;
        }catch (Exception $e){
            $this->rollback();
            throw $e;
        }
    }

    public function createMultiple(&$dataMultiple){
        $this->startTransaction();
        try {
            foreach ($dataMultiple as &$data) {
                $this->createInternal($data);
            }

            $this->commit();
        }catch (Exception $e){
            $this->rollback();
            throw $e;
        }
    }

    public function deleteGeneric(...$values)
    {
        $this->startTransaction();
        $tn = $this->getTableName();
        $sq = new SearchQuery();
        $sq->initialize($this->getIdColumns(), $values);
        $sq->query = "DELETE FROM $tn ";
        $data = $sq->searchTerms;

        $this->execute($sq,"DELETE");
        $this->deleteChildrens($data);
        $this->commit();
    }

    protected function loadDependencies(&$result)
    {

    }
    public function getByIdGeneric($loadDependencies,...$values)
    {
        $sq = new SearchQuery();
        $sq->count=1;
        $sq->initialize($this->getIdColumns(), $values);

        $list = $this->search($sq);
        if (count($list) == 0) {
            return null;
        }
        $result = $list[0];
        if($loadDependencies){
            $this->loadDependencies($result);
        }
        return $result;
    }

    public function execute($searchQuery,$operation="GENERIC")
    {
        $db = $this->getDbConnection();
        list($queryParams, $select) = $this->createQuery($searchQuery);

        $this->audit->execute($this,$operation,$db,$select,...$queryParams);
    }

    protected function uniqueFilter(&$row,&$context){
        return true;
    }

    protected function adaptJsonFieldOut($id,$serializedData){
        return json_decode($serializedData);
    }

    public function search($searchQuery)
    {
        try{
        $db = $this->getDbConnection();
        list($queryParams, $select) = $this->createQuery($searchQuery);
        error_log($this->audit->approximateQuery($select,$queryParams));
        $fetchId = $this->audit->fetchLoop($this,"SEARCH",$db,$select,...$queryParams);

        $result =[];
        $jsonFields = $this->getJsonFields();
        $context = [];

        $count = $searchQuery->count<0?PHP_INT_MAX:$searchQuery->count;
        while($row = $this->audit->fetchLoopItem($fetchId)){
            if($searchQuery->extraFilter){
                if(!($searchQuery->extraFilter)($searchQuery,$row))continue;
            }
            if(!$this->uniqueFilter($row,$context))continue;
            $count--;
            foreach ($jsonFields as $jsonField) {
                if(isset($row[$jsonField])){
                    $row[$jsonField] = $this->adaptJsonFieldOut($jsonField,$row[$jsonField]);
                }
            }
            $result[] = $row;
            if($count==0)break;
        }
        return $result;
        }catch (Exception $e){
            error_log(json_encode($searchQuery));
            $this->log->error("Parameter: ".json_encode($searchQuery),$e);
            throw $e;
        }
    }

    private function bitmaskSplit($input){
        $res = [];
        if(is_string($input)){
            $spl = explode("|",$input);
            foreach ($spl as $v){
                if($v==='')continue;
                $res[] = $v;
            }
        }else if (is_array($input)){
            foreach ($input as $value) {
                $spl = $this->bitmaskSplit($value);
                foreach ($spl as $v){
                    if($v==='')continue;
                    $res[] = $v;
                }
            }
        }
        return $res;
    }

    protected function setupSingleWhereCondition($multiSource, $key, $type, $value, &$queryParams, &$querySplit)
    {
        $multi = [$key];
        if (isset($multiSource[$key]) && count($multiSource[$key])>0) {
            $multi = $multiSource[$key];
        }
        if(!$value || (is_array($value) && count($value)==0)||(is_string($value) && strlen($value)==0) ){
            if(!$value && !is_numeric($value) && is_bool($value))return;
            if(is_array($value)|| (is_string($value) && strlen($value)==0))return;
        }
        $subSplit = array();

        foreach ($multi as $multiKey) {
            $splMulti = $this->splitExtension('_', $multiKey);
            if($multiKey===$key)$splMulti = [$multiKey];
            if(count($splMulti)>1){
                $multiKey = $splMulti[0];
                $type = $splMulti[1];
            }
            if(!(strpos($multiKey,".")!==false)){
                $multiKey = $this->getTableName().".".$multiKey;
            }
            switch ($type) {
                case "bitmask":

                    $value = $this->bitmaskSplit($value);
                    if(count($value)==0)break;
                    $subSub =[];
                    foreach ($value as $valueItem) {
                        $queryParams[] = $valueItem."";
                        $queryParams[] = $valueItem."";
                        $subSub[] = " ($multiKey & ?)=? ";
                    }

                    $subSplit[] = " ( ".join(" OR ",$subSub)." )";
                    break;
                case "bitmaskand":

                    $value = $this->bitmaskSplit($value);
                    if(count($value)==0)break;
                    $subSub =[];
                    foreach ($value as $valueItem) {
                        $queryParams[] = $valueItem."";
                        $queryParams[] = $valueItem."";
                        $subSub[] = " ($multiKey & ?)=? ";
                    }

                    $subSplit[] = " ( ".join(" AND ",$subSub)." )";
                    break;
                case "eq":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey=?) ";
                    break;
                case "eqornull":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey=? OR $multiKey IS NULL) ";
                    break;
                case "like":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey LIKE CONCAT('%', ?,'%')) ";
                    break;
                case "gt":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey > ?) ";
                    break;
                case "lt":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey < ?) ";
                    break;
                case "gte":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey >= ?) ";
                    break;
                case "lte":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey <= ?) ";
                    break;
                case "ne":
                    $queryParams[] = $value."";
                    $subSplit[] = " ($multiKey <> ?) ";
                    break;
                case "in":

                    $splValues = $value;
                    if(is_string($value)) {
                        $splValues =explode("|", $value);
                    }
                    foreach ($splValues as $splValue) {
                        $queryParams[] = $splValue."";
                        $subSplit[] = " ($multiKey = ?) ";
                    }
                    break;
                case "null":
                    $subSplit[] = " ($multiKey is null) ";
                    break;
                case "notnull":
                    $subSplit[] = " ($multiKey is not null) ";
                    break;
            }
        }
        if(count($subSplit)>0) {
            $querySplit[] = " ( " . join(" OR ", $subSplit) . " ) ";

        }

    }

    protected function createQuery($options)
    {
        $select = '';
        $fields = [];
        $searchQuery = $options;
        if ($options->query) {
            $select = $options->query;
        }
        if ($options->fields) {
            $fields = $options->fields;
        }

        $possibleSearchTerms = $this->getSearchTerms();
        $searchTerms = $searchQuery->searchTerms;
        $queryParams = array();
        $querySplit = array();
        foreach ($searchTerms as $fullKey => $value) {
            if ($value === null || $value==='' ||
                (is_array($value) && count($value)==0)
            ) continue;

            $splitKey = $this->splitExtension("_", $fullKey);


            $key = $splitKey[0];
            $type = "eq";
            if (count($splitKey) == 2) {
                $type = $splitKey[1];
            }
            if (array_key_exists($key,$possibleSearchTerms) ){//|| in_array($key,$possibleSearchTerms)) {
                $this->setupSingleWhereCondition($possibleSearchTerms, $key, $type, $value, $queryParams, $querySplit);
            }else{
                $this->setupSingleWhereCondition($possibleSearchTerms, $key, $type, $value, $queryParams, $querySplit);
            }
        }
        $tableName = $this->getTableName();
        if($options->join){
            $tableName = $options->join;
        }
        $whereSet = false;
        if ($select === '') {
            if (count($fields) == 0) {
                $select = "SELECT * FROM $tableName ";
            } else {
                $select = "SELECT " . join(",", $fields) . " FROM $tableName ";
            }
            if(count($querySplit)>0){
                $whereSet=true;
                $select .= " WHERE ".join("AND ", $querySplit)." ";
            }
        } else {
            if(count($querySplit)>0) {
                $whereSet=true;
                $select .= " WHERE " . join(" AND ", $querySplit);
            }
        }
        if(count($options->extraConditions)>0){
            if(!$whereSet)$select .= " WHERE ";

            $select .= " " . join(" AND ", $options->extraConditions);
        }
        if (!isset($searchQuery->orderBy) || $searchQuery->orderBy==='') {
            $searchQuery->orderBy = $this->getDefaultOrderBy();
        }
        if (isset($searchQuery->orderBy)) {
            $obs = [];
            if (Converter::isString($searchQuery->orderBy)) {
                $obs[] = preg_replace('/[^a-bA-B0-9]/', '', $searchQuery->orderBy);
            } else if (Converter::isArray($searchQuery->orderBy)) {
                if(count($searchQuery->orderBy)==0)$searchQuery->orderBy= $this->getDefaultOrderBy();
                foreach ($searchQuery->orderBy as $order) {
                    $obs[] = preg_replace('[^a-bA-B0-9 ]', '', $order);
                }
            }
            if (count($obs) > 0) {
                $select .= " ORDER BY " . join(" , ", $obs);
            }
        }
        if ($searchQuery->from === null) $searchQuery->from = 0;
        if ($searchQuery->count === null) $searchQuery->count = -1;

        $sf = $searchQuery->from;
        $st = $searchQuery->count;

        if($sf>0){
            $select.=" LIMIT $sf,18446744073709551615 ";
        }
        return array($queryParams, $select);
    }

    function getNextLongInternal($tableName){

        // Upsert logic: Insert if not exists with counter = 0, otherwise increment
        $sql = "
        INSERT INTO counters (table_name, counter)
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE counter = counter + 1
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tableName]);

        // Return the new counter value
        $stmt = $this->db->prepare("SELECT counter FROM counters WHERE table_name = ?");
        $stmt->execute([$tableName]);
        return $stmt->fetchColumn();
    }
    function getNextLong(&$data=null){
        $tableName = strtoupper($this->getTableName());
        return $this->getNextLongInternal($tableName);
    }

    protected function deleteChildrens(&$data)
    {

    }
}