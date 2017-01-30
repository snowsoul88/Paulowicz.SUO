<?
class Redoc {
    
    private $core;
    private $service;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
    }

    private function curl($uri, $method, $data, $post=false) {
        $ch = curl_init();
        if ($post === true) {
            curl_setopt($ch, CURLOPT_URL, $uri.$method);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_URL, $uri.$method.'?'.http_build_query($data));
        }
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close ($ch);
        return $output;
    }
    
    public function categories() { // Returns all categories from Re:Doc
        $uri = Config::getParam('redoc.uri');
        $method = Config::getParam('redoc.method.ServiceCategory.List');
        $output = self::curl($uri, $method, array(), false);
        $output = json_decode($output); // Decoding JSON from Re:Doc
        foreach ($output as $category) {
            $sql = "
                INSERT INTO product_categories (id, name)
                VALUES('".$category->id."', '".$category->name."')
                ON DUPLICATE KEY UPDATE name=VALUES(name)
            ";
            $this->core->execQuery($sql); // Insert values or update if dublicate
        }
        return $output;
    }

    public function situations() { // Returns all categories from Re:Doc
        $uri = Config::getParam('redoc.uri');
        $method = Config::getParam('redoc.method.LifeSituation.List');
        $output = self::curl($uri, $method, array(), false);
        $output = json_decode($output); // Decoding JSON from Re:Doc
        foreach ($output as $situation) {
            $sql = "
                INSERT INTO product_situations (id, name)
                VALUES('".$situation->id."', '".$situation->name."')
                ON DUPLICATE KEY UPDATE name=VALUES(name)
            ";
            $this->core->execQuery($sql); // Insert values or update if dublicate
        }
        return $output;
    }

    public function services($date=0) { // Returns all categories from Re:Doc
        $uri = Config::getParam('redoc.uri');
        $method = Config::getParam('redoc.method.ServiceInfo.Count');
        $count = self::curl($uri, $method, array(), false); // Get serv count from Re:Doc
        $count = json_decode($count);

        $uri = Config::getParam('redoc.uri');
        $method = Config::getParam('redoc.method.ServiceInfo.List');
        $list = self::curl($uri, $method, array('start'=>0, 'count'=>$count, 'date'=>$date), false); // Get serv list from Re:Doc
        $list = json_decode($list);
        foreach ($list as $service) {
            $sql = "
                INSERT INTO products (id, name, description)
                VALUES('".$service->RecId."', '".$service->ServiceFullName."', '".$service->ServiceDescription."')
                ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description)
            ";
            $this->core->execQuery($sql); // Insert values or update if dublicate
            $sql = "DELETE FROM pc_binding WHERE pid='".$service->RecId."'";
            $this->core->execQuery($sql); // Delete bindings if exists
            $sql = "DELETE FROM ps_binding WHERE pid='".$service->RecId."'";
            $this->core->execQuery($sql); // Delete bindings if exists
            foreach ($service->CategoryList as $category) {
                $sql = "
                    INSERT INTO pc_binding (pid, cid)
                    VALUES('".$service->RecId."', '".$category."')
                ";
                $this->core->execQuery($sql); // Create new bindings
            }
            foreach ($service->LifeSituationList as $situation) {
                $sql = "
                    INSERT INTO ps_binding (pid, sid)
                    VALUES('".$service->RecId."', '".$situation."')
                ";
                $this->core->execQuery($sql); // Create new bindings
            }
        }
        return $list;
    }
}

?>