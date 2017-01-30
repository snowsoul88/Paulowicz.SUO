<?php

class Core {
    
    public $dbh; // handle of the db connexion
    private static $instance;
    
    private function __construct() {
    
        $host = Config::getParam('db.host');
        $user = Config::getParam('db.user');
        $password = Config::getParam('db.password');        
        $base = Config::getParam('db.base');
        $encode = Config::getParam('db.encode');
    
        $this->dbh = mysql_connect($host, $user, $password) or die ("Не могу создать соединение");
        mysql_select_db($base) or die (mysql_error());
        mysql_set_charset($encode, $this->dbh);
    }
    
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public function execQuery($sql) {
        mysql_query($sql) or trigger_error(mysql_error()." in ".$sql); 
    }
    
    public function getRows($sql) {
        $arr = array();
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
            $arr[] = $row;
        }
        return $arr;
    }
}