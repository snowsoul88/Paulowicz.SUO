<?
class User {
    
    private $core;
    private $service;
    private $shedule;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->shedule = new shedule();
    }

    private function check_permissions($role, $clientId) {
        $errors = array();
        $sql = "
            SELECT id, client_id, role, REMOTE_ADDR, HTTP_X_FORWARDED_FOR
            FROM clients
            WHERE client_id='".$clientId."'
        ";
        $client = $this->core->getRows($sql); // Get client id and other data
        if (!empty($client)) {
            $cid = $client[0]['id'];
            $crole = $client[0]['role'];
            $CREMOTE_ADDR = $client[0]['REMOTE_ADDR'];
            $CHTTP_X_FORWARDED_FOR = $client[0]['HTTP_X_FORWARDED_FOR'];
            $validation_counter = 1;
            $validation_passed = 0;
            if ($CHTTP_X_FORWARDED_FOR) {
                $validation_counter = $validation_counter + 1;
                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] === $CHTTP_X_FORWARDED_FOR) {
                    $validation_passed = $validation_passed + 1;
                } else {
                    $errors[] = 'Запрещен доступ для данного HTTP_X_FORWARDED_FOR';
                }
            }
            if ($CREMOTE_ADDR) {
                $validation_counter = $validation_counter + 1;
                if (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === $CREMOTE_ADDR) {
                    $validation_passed = $validation_passed + 1;
                } else {
                    $errors[] = 'Запрещен доступ для данного REMOTE_ADDR';
                }
            }
            if ($crole === $role) {
                $validation_passed = $validation_passed + 1;
            } else {
                $errors[] = 'Запрещен доступ для пользователя в роли '.$role;
            }
            if ($validation_counter === $validation_passed) {
                return $cid;
            } else {
                foreach ($errors as $error) {
                    $this->service->error_log($error);
                }
                return false;
            }
        } else {
            return false;
        }
    }
    
    public function login($login, $password, $shedule) {
        $jwt = array('Bearer' => null);
        $sql = "
            SELECT id, password, role
            FROM users
            WHERE login='".$login."'
        ";
        $user = $this->core->getRows($sql); // Get user id and password
        if (!empty($user)) {
            $upassword = $user[0]['password'];
            $uid = $user[0]['id'];
            $rol = $user[0]['role'];
            $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
            if (crypt($password, $upassword) == $upassword) { // Check input password and password from DB
                $token = $this->service->jwt_encode($uid, $rol); // Create new token
                $sql = "
                    UPDATE users
                    SET token='".$token."'
                    WHERE id='".$uid."'
                ";
                $this->core->execQuery($sql);
                $sql = "
                    INSERT INTO users_activity (id, last, shedule_action_type)
                    VALUES ('".$uid."', '".$last."', '".$shedule."')
                ";
                $this->core->execQuery($sql); // Create token users_activity
                $jwt['Bearer'] = $token;
            }
        }
        return $jwt;
    }

    public function identify($clientId, $login, $password, $shedule) {
        $jwt = array('Bearer' => null);
        $sql = "
            SELECT id, password, role
            FROM users
            WHERE login='".$login."'
        ";
        $user = $this->core->getRows($sql); // Get user id and password
        if (!empty($user)) {
            $upassword = $user[0]['password'];
            if (crypt($password, $upassword) == $upassword) { // Check input password and password from DB
                $uid = $user[0]['id'];
                $rol = $user[0]['role'];
                $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
                $cid = self::check_permissions($rol, $clientId);
                if ($cid) {
                    $token = $this->service->jwt_encode($uid, $rol, $cid); // Create new GUID for create token
                    $sql = "
                        UPDATE clients_activity
                        SET logout = '".$last."'
                        WHERE uid = '".$uid."'
                        AND ISNULL(logout)
                    ";
                    $this->core->execQuery($sql);
                    $sql = "
                        UPDATE users
                        SET token='".$token."'
                        WHERE id='".$uid."'
                    ";
                    $this->core->execQuery($sql);
                    $sql = "
                        INSERT INTO users_activity (id, last, shedule_action_type)
                        VALUES ('".$uid."', '".$last."', '".$shedule."')
                    ";
                    $this->core->execQuery($sql); // Create token users_activity
                    $sql = "
                        INSERT INTO clients_activity (cid, uid, last)
                        VALUES ('".$cid."', '".$uid."', '".$last."')
                    ";
                    $this->core->execQuery($sql); // Create token users_activity
                    $jwt['Bearer'] = $token;
                } else {
                    return $jwt; // Return false if permission denied (by IP or role)
                }
            } else {
                $this->service->error_log('Пароль пользователя '.$login.' введен не верно');
            }
        } else {
            $this->service->error_log('Пользователь с логином '.$login.' не найден');
        }
        return $jwt;
    }

    public function logout($shedule, $uid) {
        $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
        $sql = "
            INSERT INTO users_activity (id, last, logout, shedule_action_type)
            VALUES ('".$uid."', '".$last."', '".$last."', '".$shedule."')
        ";
        $this->core->execQuery($sql);
    }

    public function client_logout($shedule) {
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
            $sql = "
                INSERT INTO clients_activity (uid, cid, last, logout)
                VALUES ('".$token['uid']."', '".$token['cid']."', '".$last."', '".$last."')
            ";
            $this->core->execQuery($sql);
            self::logout($shedule, $token['uid']);
            return true;
        } else {
            return false;
        }
    }

    public function create($login, $password, $fname, $sname, $mname, $role) {
        $user = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login
            $guid = $this->service->guid(); // Create new GUID for user
            $password = crypt($password); // Crypt password
            $sql = "
                INSERT INTO users (id, login, password, creator, fname, sname, mname, role)
                VALUES ('".$guid."', '".$login."', '".$password."', '".$token['uid']."', '".$fname."', '".$sname."', '".$mname."', '".$role."')
            ";
            $this->core->execQuery($sql);
            $user = self::user_state($guid);
        }
        return $user;
    }

    public function delete($user_id) {
        $sql = "
            DELETE FROM users
            WHERE id = '".$user_id."'
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return true;
    }

    public function users() {
        $users = array();
        if (isset($GLOBALS['Bearer'])) {
            $sql = "
                SELECT id
                FROM users
            ";
            $allusers = $this->core->getRows($sql);
            foreach ($allusers as $user) {
                $users[] = self::user_state($user['id']);
            }
        }
        return $users;
    }

    public function state($clientId) {
        $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login (now)
        $token_lifespan = Config::getParam('app.token_lifespan'); // Get token lifespan from config
        $lifespan = $last-$token_lifespan;
        $sql = "
            SELECT id, client_id
            FROM clients
            WHERE client_id='".$clientId."'
        ";
        $client = $this->core->getRows($sql); // Get client id and other data
        $sql = "
            SELECT uid, cid, last
            FROM clients_activity
            WHERE cid='".$client[0]['id']."'
            ORDER BY last DESC
        ";
        $client = $this->core->getRows($sql); // Get client id and other data
        $uid = $client[0]['uid'];
        $last = $client[0]['last'];
        if ($last > $lifespan) {
            $TurnOn = true;
        } else {
            $TurnOn = false;
        }

        $sql = "
            SELECT id, login, fname, sname, mname, integration_id, role, created, creator, server_id, disabled
            FROM users
            WHERE id='".$uid."'
        ";
        $user = $this->core->getRows($sql); // Get user data

        $sql = "
            SELECT id, last, logout
            FROM users_activity
            WHERE id='".$uid."'
            AND last > '".$lifespan."'
            ORDER BY last DESC
        ";
        $auth = $this->core->getRows($sql); // Get user data

        if (empty($auth) || $auth[0]['logout']) {
            $IsActive = false;
        } else {
            $IsActive = true;
        }
        /* Эталон информации пользователя от Google
        {
          "email": "johnfoo@gmail.com",
          "email_verified": true,
          "family_name": "Foo",
          "gender": "male",
          "given_name": "John",
          "identities": [
            {
              "access_token": "ya29.AsaS6ZQgRHlCHqzZ3....sFFBpQYpVVieSWur-7tmZbzEtwMkA",
              "provider": "google-oauth2",
              "user_id": "103547991597142817347",
              "connection": "google-oauth2",
              "isSocial": true
            }
          ],
          "locale": "en",
          "name": "John Foo",
          "nickname": "matiasw",
          "picture": "https://lh4.googleusercontent.com/-OdsbOXom9qE/AAAAAAAAAAI/AAAAAAAAADU/_j8SzYTOJ4I/photo.jpg",
          "user_id": "google-oauth2|103547991597142817347"
        }
        */
        if (!empty($user)) {
            $User = array(
                'user_name'           => $user[0]['sname'].' '.$user[0]['fname'],
                'user_given_name'     => $user[0]['sname'],
                'user_family_name'    => $user[0]['fname'],
                'user_login'          => $user[0]['login'],
                'user_integration_id' => $user[0]['integration_id'],
                'user_role'           => $user[0]['role'],
                'user_disabled'       => $user[0]['disabled'],
                'user_is_active'      => $IsActive,
                'user_id'             => $user[0]['id'],
                'user_creator'        => $user[0]['creator'],
                'user_created'        => $user[0]['created'],
                'user_server_id'      => $user[0]['server_id']
            );
        } else {
            $User = array();
        }

        $state = array(
            'TurnOn'        => $TurnOn,
            'BeginBreak'    => false,
            'User'          => $User
        );
        return $state;
    }

    public function user_state($uid, $date = null) {
        $last = time() + (Config::getParam('app.utc_timezone')*60*60); // Time of last login (now)
        $token_lifespan = Config::getParam('app.token_lifespan'); // Get token lifespan from config
        $lifespan = $last-$token_lifespan;
        $user_recent_state = array();
        $sql = "
            SELECT id, login, fname, sname, mname, role, created, creator, disabled
            FROM users
            WHERE id='".$uid."'
        ";
        $user = $this->core->getRows($sql); // Get user data

        $sql = "
            SELECT id, last, logout, shedule_action_type
            FROM users_activity
            WHERE id='".$uid."'
            ORDER BY last DESC
            LIMIT 0,1
        ";
        $auth = $this->core->getRows($sql); // Get user data
        if (!empty($auth)) {
            $user_recent_state = $this->shedule->shedule_type($auth[0]['shedule_action_type']);
        }
        if (!empty($user)) {
            $user_state = array(
                'user_name'           => $user[0]['sname'].' '.$user[0]['fname'],
                'user_given_name'     => $user[0]['sname'],
                'user_family_name'    => $user[0]['fname'],
                'user_login'          => $user[0]['login'],
                'user_role'           => $user[0]['role'],
                'user_disabled'       => $user[0]['disabled'],
                'user_recent_state'   => $user_recent_state,
                'user_id'             => $user[0]['id'],
                'user_creator'        => $user[0]['creator'],
                'user_created'        => $user[0]['created'],
                'user_shedule'        => $this->shedule->binded_shedule($user[0]['id'], $date)
            );
        } else {
            $user_state = array();
        }
        return $user_state;
    }

    public function user_product_binding($uid, $products) {
        $sql = "
            DELETE FROM up_binding
            WHERE uid = '".$uid."'
        ";
        $this->core->execQuery($sql);
        foreach ($products as $pid) {
            $sql = "
                INSERT INTO up_binding (uid, pid)
                VALUES ('".$uid."', '".$pid."')
            ";
            $this->core->execQuery($sql);
        }
    }

    public function user_situations_binding($uid, $situations) {
        $sql = "
            DELETE FROM us_binding
            WHERE uid = '".$uid."'
        ";
        $this->core->execQuery($sql);
        foreach ($situations as $sid) {
            $sql = "
                INSERT INTO us_binding (uid, sid)
                VALUES ('".$uid."', '".$sid."')
            ";
            $this->core->execQuery($sql);
        }
    }

    public function user_binded_situations($uid) {
        $sql = "
            SELECT *
            FROM product_situations
        ";
        $situations = $this->core->getRows($sql); // Get full data by product
        $sql = "
            SELECT sid
            FROM us_binding
            WHERE uid = '".$uid."'
        ";
        $us_binding = $this->core->getRows($sql); // Get full data by product
        $data = array();
        foreach ($situations as $situation) {
            $enabled = false;
            foreach ($us_binding as $binding) {
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
}

?>