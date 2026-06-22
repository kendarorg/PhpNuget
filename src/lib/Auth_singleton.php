<?php

class Auth{
    private $db;
    private $audit;
    private $user;
    private $log;
    public function __construct()
    {

        $this->log  = LogManager::getLogger("GlobalErrors");
        $this->db = getDbConnection();
        $this->audit = GlobalRegistry::get('Audit');
    }

    public function getUser(){
        if($this->user == null){
            if (!$this->isUserLoggedIn()) {
                throw new Exception("User is not logged in");
            }

            try {
                $db = $this->db;

                // Get user by ID
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();

                if ($user) {
                    // Remove password from user data before returning
                    unset($user['password']);
                }
                $this->user = $user;
            } catch (Exception $e) {
                $this->log->error("Get Current User Error",$e);
                throw new Exception("User is not logged in");
            }
        }
        return $this->user;
    }

    private function cleanupOldRecords() {
        $sql = "DELETE FROM accesses 
                WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                AND is_blocked = FALSE";

        $this->db->exec($sql);
    }
    public function checkUser($username) {
        // Clean up old records first
        $this->cleanupOldRecords();

        // Check if user is currently blocked
        $sql = "SELECT blocked_until FROM accesses 
                WHERE username = ? AND is_blocked = TRUE 
                AND blocked_until > NOW() 
                ORDER BY blocked_until DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $result = $stmt->fetch();

        // If blocked and block period hasn't expired, return false
        if ($result) {
            return false;
        }

        // If block period expired, unblock the user
        $this->unlockUser($username);

        return true;
    }

    public function unlockUser($username) {
        $sql = "UPDATE accesses 
                SET is_blocked = FALSE, blocked_until = NULL 
                WHERE username = ? AND is_blocked = TRUE";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
    }

    function authenticateUser($username, $password)
    {
        try {
            $db = $this->db;
            // Get user by username
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND locked = 0");

            $stmt->execute([$username]);
            $this->user = $stmt->fetch();
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Check if user exists and password is correct
            if ($this->user && password_verify($password, $this->user['password'])) {
                // Remove password from user data before returning
                unset($this->user['password']);
                return $this->loginUser();
            }

            return false;
        } catch (Exception $e) {
            $this->log->error("Authentication Error",$e);
            return false;
        }
    }

    function simulate($userToSimulate)
    {
        if (!$userToSimulate || !isset($userToSimulate['id'])) {
            return false;
        }
        unset($userToSimulate['password']);
        $this->user = $userToSimulate;
        $this->cachedPermssions = [];
        unset($_SESSION['permissions']);
        unset($_SESSION['extra_permissions']);
        return $this->loginUser();
    }

    function logoutUser()
    {
        // Unset all session variables
        //$_SESSION = [];
        $this->user=null;

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        // Finally, destroy the session
        session_destroy();
        $_SESSION = [];
    }

    function loginUser()
    {
        if (!$this->user || !isset($this->user['id'])) {
            return false;
        }
        // Set session variables
        $_SESSION['user_id'] = $this->user['id']."";
        $_SESSION['username'] = $this->user['username'];
        $_SESSION['user_role'] = $this->user['role'];
        $jear=[];

        $je = json_decode($this->user['permissions']);
        foreach ($je as $item) {
            $jear[$item->id] = $item->permissions;
        }
        $_SESSION['permissions']=[];
        $_SESSION['extra_permissions'] = $jear;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['permissions'] = [];
        $db = $this->db;
        // Get user by username

        $result=[];
        $part = $this->audit->fetchAll("LOGIN","LOGIN",$db,"SELECT id,permissions FROM permissions WHERE role = ?",$this->user['role']);
       ("PRIMA ".json_encode($part));
        foreach ($part as $row) {
            $result[$row['id']] = $row['permissions'];
        }
        if(isset($_SESSION['extra_permissions']) !== null) {
            foreach ($_SESSION['extra_permissions'] as $key => $value) {
                if(isset($result[$key])){
                    $result[$key] .= $value;
                }else{
                    $result[$key] = $value;
                }
            }

        }
        $_SESSION['permissions']=$result;

        return true;
    }

    public function isUserLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    public function userMustBeLoggedIn()
    {
        if(!$this->isUserLoggedIn()) {
            throw new Exception();
        }
    }

    var $cachedPermssions = [];

    function loadPermissions($permission)
    {

        if (!$this->isUserLoggedIn() || !isset($_SESSION['user_role'])) {
            return new Permissions('',$permission);
        }
        if(isset($this->cachedPermssions[$permission])) {
            return $this->cachedPermssions[$permission];
        }

        $userRole = $_SESSION['user_role'];

        // SuperAdmin has all permissions
        if ($userRole === 'SuperAdmin') {
            return new Permissions('CRUDO',$permission);
        }
        if (!isset($_SESSION['permissions'])) {
            $_SESSION['permissions'] = [];
            $db = $this->db;
            // Get user by username

            $result=[];
            $tmp =  $this->audit->fetchAll("LOGIN","PERMISSIONS",$db,"SELECT id,permissions FROM permissions WHERE role = ?",$userRole);

            foreach ($tmp as $row) {
                $result[$row['id']] = $row['permissions'];
            }
            if(isset($_SESSION['extra_permissions']) !== null) {
                foreach ($_SESSION['extra_permissions'] as $key => $value) {
                    if(isset($result[$key])){
                        $result[$key] .= $value;
                    }else{
                        $result[$key] = $value;
                    }
                }

            }
            $_SESSION['permissions']=$result;

        }


        if (isset($_SESSION['permissions'][$permission])) {
            $this->cachedPermssions[$permission]= new Permissions($_SESSION['permissions'][$permission],$permission);
        }else{
            $this->cachedPermssions[$permission] = new Permissions('',$permission);
        }

        return $this->cachedPermssions[$permission];
    }
}
