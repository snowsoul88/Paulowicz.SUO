<?
class Client {
    
    private $core;
    private $service;
    private $user;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->user = new user();
    }

    public function login($login, $password) {
        $sql = "
            SELECT id, password
            FROM users
            WHERE login='".$login."'
        ";
        $user = $this->core->getRows($sql); // Get user id and password
        $upassword = $user[0]['password'];
        $uid = $user[0]['id'];
        $token = $this->service->guid(); // Create new GUID for create token
        $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
        if (crypt($password, $upassword) == $upassword) { // Check input password and password from DB
            $sql = "
                INSERT INTO users_activity (id, last, token)
                VALUES ('".$uid."', '".$last."', '".$token."')
            ";
            $this->core->execQuery($sql); // Create token users_activity
            $_SESSION['user']['token'] = $token;
            $_SESSION['user']['last'] = $last;
            return $token;
        } else {
            return false;
        }
    }

    public function last() {
        if (isset($_SESSION['user']['last']) && isset($_SESSION['user']['token'])) {
            $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login (now)
            $token_lifespan = Config::getParam('app.token_lifespan'); // Get token lifespan from config
            if ($last-$_SESSION['user']['last'] > $token_lifespan) { // Compare. If lastlogin is more than config lifespan - return false
                return false;
            } else {
                $sql = "
                    UPDATE users_activity
                    SET last='".$last."'
                    WHERE token='".$_SESSION['user']['token']."'
                ";
                $this->core->execQuery($sql);
                $_SESSION['user']['last'] = $last;
                return true;
            }
        } else {
            return false;
        }
    }

    public function create($client_id, $role, $REMOTE_ADDR = null, $HTTP_X_FORWARDED_FOR = null) {
        $guid = $this->service->guid(); // Create new GUID for user
        $sql = "
            INSERT INTO clients (id, client_id, role, REMOTE_ADDR, HTTP_X_FORWARDED_FOR)
            VALUES ('".$guid."', '".$client_id."', '".$role."', '".$REMOTE_ADDR."', '".$HTTP_X_FORWARDED_FOR."')
        ";
        $this->core->execQuery($sql);
        return self::clients();
    }

    public function delete($id) {
        $sql = "
            DELETE FROM clients
            WHERE id = '".$id."'
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return true;
    }

    public function client_state($clientId) { // !Important! Here clientId means id (GUID), not client_id in table
        $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login (now)
        $token_lifespan = Config::getParam('app.token_lifespan'); // Get token lifespan from config
        $lifespan = $last-$token_lifespan;
        $user_state = array();
        $state = array();
        $sql = "
            SELECT id, client_id
            FROM clients
            WHERE id='".$clientId."'
        ";
        $client = $this->core->getRows($sql); // Get client id and other data
        if (!empty($client)) {
            $client_id = $client[0]['client_id'];
            $sql = "
                SELECT uid, cid, last, logout
                FROM clients_activity
                WHERE cid='".$clientId."'
                ORDER BY last DESC
            ";
            $client = $this->core->getRows($sql); // Get client id and other data
            if (!empty($client)) {
                $last = $client[0]['last'];
                $user_state = $this->user->user_state($client[0]['uid'], date("Y-m-d H:i:s"));
            }
            $state = array(
                'client_guid'    => $clientId,
                'client_id'      => $client_id,
                'user_state'     => $user_state
            );
        }
        return $state;
    }

    public function clients() {
        $sql = "
            SELECT *
            FROM clients
        ";
        $clients = $this->core->getRows($sql); // Get client id and other data
        return $clients;
    }

    public function workplace() {
        $workplace = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT *
                FROM workplaces
                WHERE cid = '".$token['cid']."'
            ";
            $workplace = $this->core->getRows($sql);
            $sql = "
                SELECT *
                FROM workplace_types
                WHERE type_id = '".$workplace[0]['workplace_type']."'
            ";
            $workplace = $workplace[0];
            $workplace_type = $this->core->getRows($sql);
            $workplace['workplace_type'] = $workplace_type[0];
        }
        return $workplace;
    }

    public function ionicmenu() {
        $ionicmenu = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT role
                FROM users
                WHERE id = '".$token['uid']."'
            ";
            $role = $this->core->getRows($sql);
            $config = Config::getParam('app.ionic_menu');
            $ionicmenu = $config[$role[0]['role']];
        }
        return $ionicmenu;
    }

    public function settings_entities_get() {
        $sql = "
            SELECT *
            FROM settings_entities
        ";
        $entities = $this->core->getRows($sql);
        return $entities;
    }

    public function settings_entities_set($entity, $description) {
        $sql = "
            INSERT INTO settings_entities (entity, description)
            VALUES ('".$entity."', '".$description."')
        ";
        $this->core->execQuery($sql);
        return true;
    }

    public function settings_entities_unset($entity) {
        $sql = "
            DELETE FROM settings_entities
            WHERE entity = '".$entity."'
            AND sys = '0'
        ";
        $this->core->execQuery($sql);
        return true;
    }

    public function settings_parameters_get($entity) {
        $sql = "
            SELECT *
            FROM settings
            WHERE entity = '".$entity."'
        ";
        $parameters = $this->core->getRows($sql);
        return $parameters;
    }

    public function settings_parameters_set($entity, $description, $parameter, $value) {
        $sql = "
            INSERT INTO settings (entity, description, parameter, value)
            VALUES ('".$entity."', '".$description."', '".$parameter."', '".$value."')
        ";
        $this->core->execQuery($sql);
        return true;
    }

    public function settings_parameters_unset($id) {
        $sql = "
            DELETE FROM settings
            WHERE id = '".$id."'
        ";
        $this->core->execQuery($sql);
        return true;
    }

    public function client_binded_situations($cid) {
        $sql = "
            SELECT *
            FROM product_situations
        ";
        $situations = $this->core->getRows($sql); // Get full data by product
        $sql = "
            SELECT sid
            FROM cs_binding
            WHERE cid = '".$cid."'
        ";
        $cs_binding = $this->core->getRows($sql); // Get full data by product
        $data = array();
        foreach ($situations as $situation) {
            $enabled = false;
            foreach ($cs_binding as $binding) {
                if ($binding['sid'] == $situation['id']) {
                    $enabled = true;
                }
            }
            $data[] = array(
                'situation_id'                => $situation['id'],
                'situation_name'              => $situation['name'],
                'situation_enabled'           => $enabled
            );
        }
        return $data;
    }

    public function client_product_binding($cid, $products) {
        $sql = "
            DELETE FROM cp_binding
            WHERE cid = '".$cid."'
        ";
        $this->core->execQuery($sql);
        foreach ($products as $pid) {
            $sql = "
                INSERT INTO cp_binding (cid, pid)
                VALUES ('".$cid."', '".$pid."')
            ";
            $this->core->execQuery($sql);
        }
    }

    public function client_situations_binding($cid, $situations) {
        $sql = "
            DELETE FROM cs_binding
            WHERE cid = '".$cid."'
        ";
        $this->core->execQuery($sql);
        foreach ($situations as $sid) {
            $sql = "
                INSERT INTO cs_binding (cid, sid)
                VALUES ('".$cid."', '".$sid."')
            ";
            $this->core->execQuery($sql);
        }
    }
}

?>