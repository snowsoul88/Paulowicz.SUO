<?
class Product {
    
    private $core;
    private $service;
    private $user;
    private $client;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->user = new user();
        $this->client = new client();
    }

    public function product($product) {
        $data = array();
        $sql = "
            SELECT *
            FROM products
            WHERE id='".$product."'
        ";
        $res = $this->core->getRows($sql); // Get full data by product
        if (!empty($res)) {
            $data = array(
                'product_id'                => $res[0]['id'],
                'product_name'              => $res[0]['name'],
                'product_action_type'       => $res[0]['action_type'],
                'product_recordlimit'       => $res[0]['recordlimit'],
                'product_timereglament'     => $res[0]['timereglament'],
                'product_timedelta'         => $res[0]['timedelta'],
                'product_description'       => $res[0]['description']
            );
        }
        return $data;
    }

    public function products() {
        $sql = "
            SELECT *
            FROM products
        ";
        $products = $this->core->getRows($sql); // Get full data by product
        $data = array();
        foreach ($products as $product) {
            $sql = "
                SELECT *
                FROM pc_binding
                WHERE pid='".$product['id']."'
            ";
            $pcb = $this->core->getRows($sql); // Get all binded categories
            $categorylist = array();
            foreach ($pcb as $binding) {
                $sql = "
                    SELECT *
                    FROM product_categories
                    WHERE id='".$binding['cid']."'
                ";
                $pc = $this->core->getRows($sql);
                $categorylist[] = (isset($pc[0])) ? $pc[0] : array();
            }
            $sql = "
                SELECT *
                FROM ps_binding
                WHERE pid='".$product['id']."'
            ";
            $psb = $this->core->getRows($sql); // Get all binded situations
            $lifesituationlist = array();
            foreach ($psb as $binding) {
                $sql = "
                    SELECT *
                    FROM product_situations
                    WHERE id='".$binding['sid']."'
                ";
                $ps = $this->core->getRows($sql);
                $lifesituationlist[] = (isset($ps[0])) ? $ps[0] : array();
            }
            $data[] = array(
                'product_id'                => $product['id'],
                'product_name'              => $product['name'],
                'product_action_type'       => $product['action_type'],
                'product_recordlimit'       => $product['recordlimit'],
                'product_categories'        => $categorylist,
                'product_situations'        => $lifesituationlist
            );
        }
        return $data;
    }

    public function user_binded_products($uid) {
        $sql = "
            SELECT *
            FROM products
        ";
        $products = $this->core->getRows($sql); // Get full data by product
        $sql = "
            SELECT pid
            FROM up_binding
            WHERE uid = '".$uid."'
        ";
        $up_binding = $this->core->getRows($sql); // Get full data by product
        $data = array();
        foreach ($products as $product) {
            $enabled = false;
            foreach ($up_binding as $binding) {
                if ($binding['pid'] == $product['id']) {
                    $enabled = true;
                }
            }
            $data[] = array(
                'product_id'                => $product['id'],
                'product_name'              => $product['name'],
                'product_enabled'           => $enabled
            );
        }
        return $data;
    }

    public function client_binded_products($cid) {
        $sql = "
            SELECT *
            FROM products
        ";
        $products = $this->core->getRows($sql); // Get full data by product
        $sql = "
            SELECT pid
            FROM cp_binding
            WHERE cid = '".$cid."'
        ";
        $cp_binding = $this->core->getRows($sql); // Get full data by product
        $data = array();
        foreach ($products as $product) {
            $enabled = false;
            foreach ($cp_binding as $binding) {
                if ($binding['pid'] == $product['id']) {
                    $enabled = true;
                }
            }
            $data[] = array(
                'product_id'                => $product['id'],
                'product_name'              => $product['name'],
                'product_enabled'           => $enabled
            );
        }
        return $data;
    }

    public function get_up_bindings($productId) {
        $up_bindings = array();
        $sql = "
            SELECT *
            FROM up_binding
            WHERE pid='".$productId."'
        ";
        $res = $this->core->getRows($sql); // Get all binded products to users
        foreach ($res as $r) { // Get all users who can to work with that product
            $up_bindings[] = $this->user->user_state($r['uid']);
        }
        return $up_bindings;
    }

    public function get_cp_bindings($productId) {
        $cp_bindings = array();
        $sql = "
            SELECT *
            FROM cp_binding
            WHERE pid='".$productId."'
        ";
        $res = $this->core->getRows($sql); // Get all binded products to users
        foreach ($res as $r) { // Get all users who can to work with that product
            $cp_bindings[] = $this->client->client_state($r['cid']);
        }
        return $cp_bindings;
    }

    public function settings_products_unset($id) {
        $sql = "
            DELETE FROM products
            WHERE id = '".$id."'
        ";
        $this->core->execQuery($sql);
        $sql = "
            DELETE FROM up_binding
            WHERE pid = '".$id."'
        ";
        $this->core->execQuery($sql);
        $sql = "
            DELETE FROM cp_binding
            WHERE pid = '".$id."'
        ";
        $this->core->execQuery($sql);
        $sql = "
            DELETE FROM ps_binding
            WHERE pid = '".$id."'
        ";
        $this->core->execQuery($sql);
    }

    public function settings_products_add($action_type, $product_recordlimit, $product_description, $product_timereglament, $product_name, $situations) {
        $guid = $this->service->guid(); // Create new GUID for user
        $sql = "
            INSERT INTO products (id, action_type, recordlimit, description, timereglament, name)
            VALUES ('".$guid."', '".$action_type."', '".$product_recordlimit."', '".$product_description."', '".$product_timereglament."', '".$product_name."')
        ";
        $this->core->execQuery($sql);
        foreach ($situations as $sid) {
            $sql = "
                INSERT INTO ps_binding (pid, sid)
                VALUES ('".$guid."', '".$sid."')
            ";
            $this->core->execQuery($sql);
        }
    }

    public function situations() {
        $sql = "
            SELECT *
            FROM product_situations
        ";
        $res = $this->core->getRows($sql); // Get all binded products to users
        return $res;
    }

    public function settings_situation_add($situation_name, $situation_prefix, $kioskmenu_expanded, $products) {
        $guid = $this->service->guid(); // Create new GUID for user
        $sql = "
            INSERT INTO product_situations (id, name, prefix)
            VALUES ('".$guid."', '".$situation_name."', '".$situation_prefix."')
        ";
        $this->core->execQuery($sql);
        foreach ($products as $pid) {
            $sql = "
                INSERT INTO ps_binding (pid, sid)
                VALUES ('".$pid."', '".$guid."')
            ";
            $this->core->execQuery($sql);
        }
        if ($kioskmenu_expanded == 1) {
            $sql = "
                INSERT INTO settings (entity, parameter, value, description)
                VALUES ('kioskmenu_expanded', '".$guid."', 'true', 'Многоуровневое меню киоска')
            ";
            $this->core->execQuery($sql);
        }
    }
}

?>