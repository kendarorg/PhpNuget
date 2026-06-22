<?php

class Audit{
    private $log;
    private $db;
    private $globalErrors;
    public function __construct()
    {
        $this->log = LogManager::getLogger("Audit");
        $this->db = getDbConnection();
        $this->globalErrors = GlobalRegistry::get("GlobalErrors");
    }

    function audit($type,$operation,$data){
        try {
            if($type==null){
                $type = "UNKNOWN";
            }
            if(!is_string($type)){
                if (is_object($type)) {
                    $type = get_class($type);
                }else{
                    $type = gettype($type);
                }
            }
            $userId=0;
            if(isset($_SESSION['user_id'])){
                $userId=$_SESSION['user_id'];
            }
            $currentOperation = GlobalRegistry::get('CURRENT_OPERATION');
            $db= $this->db;
            if(!is_string($data)){
                $data = json_encode($data);
            }
            $id = "";
            if(isset($_GET['id'])){
                $id="Id:".$_GET['id']." Data:\n";
            }else if(isset($_POST['id'])){
                $id="Id:".$_POST['id']." Data:\n";
            }
            $prevDate = date('Y-m-d', strtotime("-10 days"));
            $sql = "INSERT INTO operations_log (operationId,type,operation, user_id, data) VALUES (?,?,?,?,?);";
            $stmt = $db->prepare($sql);
            $stmt->execute([$currentOperation,$type, $operation, $userId, $id.$data]);
            //$db->exec("DELETE FROM operations_log where created_at < '$prevDate'");
        }catch (Exception $e){
            $this->log->error("AUDIT: 001",$e);
        }
    }

    function auditError($type,$operation,$data=null,$db=null){
        try {
            if(!is_string($type)){
                $type=get_class($type);
            }
            $userId=-1;
            $type=$type."_ERROR";
            if(isset($_SESSION['user_id'])){
                $userId=$_SESSION['user_id'];
            }
            $currentOperation = GlobalRegistry::get('CURRENT_OPERATION');
            $db= $this->db;
            $result =[
                'globalErrors'=>$this->globalErrors->getGlobalErrors(),
                'data'=>$data
            ];

            $data = json_encode($result);

            $prevDate = date('Y-m-d', strtotime("-2 days"));
            $sql = "INSERT INTO operations_log (operationId,type,operation, user_id, data) VALUES (?,?,?,?,?);";
            $stmt = $db->prepare($sql);
            $stmt->execute([$currentOperation,$type, $operation, $userId, $data]);

            //$db->exec("DELETE FROM operations_log where created_at < '$prevDate'");
        }catch (Exception $e){
            $this->log->error("AUDIT: 002 %s",$operation,$e);
        }
    }

    function getGlobalErrors(){
        return $this->globalErrors->getGlobalErrors();
    }

    function findPossibleIps()
    {
        $ips = [];

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ips[] = $_SERVER['HTTP_CLIENT_IP'] . "_HTTP_CLIENT_IP";
        }
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            # when behind cloudflare
            $ips[] = $_SERVER['HTTP_CF_CONNECTING_IP'] . "_HTTP_CLIENT_IP";
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips[] = $_SERVER['HTTP_X_FORWARDED_FOR'] . "_HTTP_X_FORWARDED_FOR";
        }
        if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ips[] = $_SERVER['HTTP_X_FORWARDED'] . "_HTTP_X_FORWARDED";
        }
        if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ips[] = $_SERVER['HTTP_FORWARDED_FOR'] . "_HTTP_FORWARDED_FOR";
        }
        if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ips[] = $_SERVER['HTTP_FORWARDED'] . "_HTTP_FORWARDED";
        }
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ips[] = $_SERVER['REMOTE_ADDR'] . "_REMOTE_ADDR";
        }
        return $ips;

    }

    function approximateQuery($query, $params)
    {
        $offset = 0;

        foreach ($params as $key => $value) {
            // Format replacement value
            if (is_null($value)) {
                $replacement = 'NULL';
            } elseif (is_bool($value)) {
                $replacement = $value ? '1' : '0';
            } elseif (is_numeric($value)) {
                $replacement = (string) $value;
            } elseif(is_array($value)) {
                $replacement = [];
                for($i = 0; $i < count($value); $i++) {
                    $replacement[]="'" . str_replace("'", "''", $value[$i]) . "'";
                }
                $replacement = join(",", $replacement);
            } else {
                $replacement = "'" . str_replace("'", "''", $value) . "'";
            }

            if (is_string($key)) {
                // Named parameters (:name)
                if (preg_match(
                    '/:' . preg_quote($key, '/') . '\b/',
                    $query,
                    $matches,
                    PREG_OFFSET_CAPTURE,
                    $offset
                )) {
                    $pos = $matches[0][1];
                    $len = strlen($matches[0][0]);

                    $query = substr_replace($query, $replacement, $pos, $len);
                    $offset = $pos + strlen($replacement);
                }
            } else {
                // Positional parameters (?)
                $pos = strpos($query, '?', $offset);

                if ($pos === false) {
                    break;
                }

                $query = substr_replace($query, $replacement, $pos, 1);
                $offset = $pos + strlen($replacement);
            }
        }

        return $query;
    }


    function fetchOne($type,$operation,$db,$sql,...$params){
        try {
            $fetchId = $this->fetchLoop($type,$operation,$db,$sql,$params);
            while($row = $this->fetchLoopItem($fetchId)){
                unset($this->stmts[$fetchId]);
                return $row;
            }
            unset($this->stmts[$fetchId]);
            return null;
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql, $params),
                'function'=>'fetchAll'
            ]);
            throw $e;
        }
    }


    function fetchAll($type,$operation,$db,$sql,...$params){
        try {
            if(!is_string($type)){
                $type=get_class($type);
            }
            if($params!==null && count($params)==1){
                if(is_array($params[0]) && !is_string($params[0])){
                    $params=$params[0];
                }
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql, $params),
                'function'=>'fetchAll'
            ]);
            throw $e;
        }
    }

    var $stmts=[];

    function fetchLoopItem($md5,$fetchType=null){
        if($fetchType===null)$fetchType=PDO::FETCH_ASSOC;
        return $this->stmts[$md5]->fetch($fetchType);
    }

    function fetchLoop($type,$operation,$db,$sql,...$params){
        try {
            if(!is_string($type)){
                $type=get_class($type);
            }
            if($params!==null && count($params)==1){
                if(is_array($params[0]) && !is_string($params[0])){
                    $params=$params[0];
                }
            }
            $md5 = md5($type.$operation.$sql.json_encode($params));
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $this->stmts[$md5]=$stmt;
            return $md5;
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql, $params),
                'function'=>'fetchAll'
            ]);
            throw $e;
        }
    }

    function fetch($type,$operation,$db,$sql,...$params){
        if(!is_string($type)){
            $type=get_class($type);
        }
        try {
            if($params!==null && count($params)==1){
                if(is_array($params[0]) && !is_string($params[0])){
                    $params=$params[0];
                }
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql, $params),
                'function'=>'fetch'
            ]);
            throw $e;
        }
    }

    function execute($type,$operation,$db,$sql,...$params){
        if(!is_string($type)){
            $type=get_class($type);
        }
        try {
            $stmt = $db->prepare($sql);
            if($params!==null && count($params)==1){
                if(is_array($params[0]) && !is_string($params[0])){
                    $params=$params[0];
                }
            }
            return $stmt->execute($params);
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql,$params),
                'function'=>'execute'
            ]);
            throw $e;
        }
    }

    /*function auditExecute($type,$operation,$stmt,$sql,...$params){
        try {
            if($params!==null && count($params)==1){
                if(is_array($params[0]) && !is_string($params[0])){
                    $params=$params[0];
                }
            }
            return $stmt->execute($params);
        }catch (Exception $e){
            $this->auditError($type,$operation,[
                'error'=>$e->getMessage(),
                'sql'=>$this->approximateQuery($sql,$params),
                'function'=>'execute'
            ]);
            throw $e;
        }
    }*/
}