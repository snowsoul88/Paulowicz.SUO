<?
class Shedule {
    
    private $core;
    private $service;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
    }

    public function binded_shedule($bid, $date = null) {
        $shedule = array();
        if ($date == null) {
            $sql = "
                SELECT *
                FROM shedule
                WHERE bid = '".$bid."'
            ";
        } else {
            $weekday = date('w', strtotime($date));
            $sql = "
                SELECT *
                FROM shedule
                WHERE bid = '".$bid."'
                AND weekday = '".$weekday."'
            ";
        }
        $res = $this->core->getRows($sql); // Get all timing by product
        foreach ($res as $r) {
            $shedule[] = array(
                'begin'         => $r['begin'],
                'end'           => $r['end'],
                'weekday'       => $r['weekday'],
                'monthday'      => $r['monthday'],
                'shedule_type'  => self::shedule_type($r['shedule_type'])
            );
        }
        return $shedule;
    }

    public function create_type($type_name, $type_description = null, $type_operable) {
        $sql = "
            INSERT INTO shedule_types (type_name, type_description, type_operable)
            VALUES ('".$type_name."', '".$type_description."', '".$type_operable."')
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return self::shedule_types();
    }

    public function delete_type($type_id) {
        $sql = "
            DELETE FROM shedule_types
            WHERE type_id = '".$type_id."'
        ";
        $this->core->execQuery($sql); // Create token users_activity
        return true;
    }

    public function shedule_type($type_id) {
        $sql = "
            SELECT *
            FROM shedule_types
            WHERE type_id = '".$type_id."'
        ";
        $shedule_type = $this->core->getRows($sql);
        $shedule_type = (!empty($shedule_type)) ? $shedule_type[0] : array();
        return $shedule_type;
    }

    public function shedule_types($operable = null) {
        if ($operable != null) {
            $sql = "
                SELECT *
                FROM shedule_types
                WHERE type_operable = '".$operable."'
            ";
        } else {
            $sql = "
                SELECT *
                FROM shedule_types
            ";
        }
        $shedule_types = $this->core->getRows($sql);
        return $shedule_types;
    }
}

?>