<?
class Workplace {
    
    private $core;
    private $service;
    private $client;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->client = new client();
    }

    public function create($workplace_number, $workplace_name = null, $workplace_type, $cid = null) {
        $sql = "
            INSERT INTO workplaces (workplace_number, workplace_name, workplace_type, cid)
            VALUES ('".$workplace_number."', '".$workplace_name."', '".$workplace_type."', '".$cid."')
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return self::workplaces();
    }

    public function delete($workplace_id) {
        $sql = "
            DELETE FROM workplaces
            WHERE wid = '".$workplace_id."'
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return true;
    }

    public function workplaces() {
        $workplaces = array();
        $sql = "
            SELECT * FROM workplaces
        ";
        $res = $this->core->getRows($sql);
        foreach ($res as $workplace) {
            $workplace['workplace_id'] = $workplace['wid'];
            $workplace['workplace_type'] = self::workplace_type($workplace['workplace_type']);
            $workplace['workplace_client'] = $this->client->client_state($workplace['cid']);
            $workplaces[] = $workplace;
        }
        return $workplaces;
    }

    public function types() {
        $sql = "
            SELECT * FROM workplace_types
        ";
        $types = $this->core->getRows($sql);
        return $types;
    }

    public function workplace_type($type_id) {
        $workplace_type = array();
        $sql = "
            SELECT * FROM workplace_types
            WHERE type_id = '".$type_id."'
        ";
        $type = $this->core->getRows($sql);
        if (!empty($type)) {
            $workplace_type = array(
                'type_id'           => $type[0]['type_id'],
                'type_name'         => $type[0]['type_name'],
                'type_description'  => $type[0]['type_description']
            );
        }
        return $workplace_type;
    }

    public function workplace_state($workplace_id) {
        $workplace_state = array();
        $sql = "
            SELECT * FROM workplaces
            WHERE wid = '".$workplace_id."'
        ";
        $workplace = $this->core->getRows($sql);
        if (!empty($workplace)) {
            $client_state = $workplace[0]['cid'] ? $this->client->client_state($workplace[0]['cid']): array();
            $workplace_state = array(
                "workplace_type"            => self::workplace_type($workplace[0]['workplace_type']),
                "workplace_id"              => $workplace_id,
                "workplace_number"          => $workplace[0]['workplace_number'],
                "client_state"              => $client_state
            );
        }
        return $workplace_state;
    }

    public function workplace_state_plasma($workplace_id) {
        $workplace_state = array();
        $sql = "
            SELECT * FROM workplaces
            WHERE wid = '".$workplace_id."'
        ";
        $workplace = $this->core->getRows($sql);
        if (!empty($workplace)) {
            $workplace_state = array(
                "workplace_type"            => self::workplace_type($workplace[0]['workplace_type']),
                "workplace_id"              => $workplace_id,
                "workplace_number"          => $workplace[0]['workplace_number']
            );
        }
        return $workplace_state;
    }
}

?>