<?
class Statistics {
    
    private $core;
    private $service;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
    }

    private function compare_action_id($action_id_i, $action_id_ii, $tp_binding = null) {
        $tp_binding = ($tp_binding == null) ? '':"AND tid IN ('".$tp_binding."')";
        $avg = 0;
        $sql = "
            SELECT tid, action_time, action_id
            FROM ticket_actions
            WHERE action_id IN ('".$action_id_i."', '".$action_id_ii."')
            ".$tp_binding."
            GROUP BY tid, action_id
        ";
        $actions = $this->core->getRows($sql);
        if (!empty($actions)) {
            $tmp = array();
            foreach ($actions as $action_i) {
                foreach ($actions as $action_ii) {
                    if ($action_i['tid'] == $action_ii['tid']) {
                        if ($action_i['action_id'] == $action_id_i && $action_ii['action_id'] == $action_id_ii) {
                            $tmp[$action_i['tid']][$action_id_i] = $action_i['action_time'];
                            $tmp[$action_i['tid']][$action_id_ii] = $action_ii['action_time'];
                            $avg = $avg + ($action_ii['action_time'] - $action_i['action_time']);
                        }
                    }
                }
            }
            $avg = $avg / count($actions);
            $avg = $this->service->secs_to_h($avg);
        }
        return $avg;
    }

    public function timing() { // Rerurns data of ticket
    /*
    −   среднее время ожидания в очереди (час:мин:сек);
    −   среднее время для получения консультации (час:мин:сек);
    −   среднее время на подачу документов (час:мин:сек);
    −   среднее время выдачи документов (час:мин:сек);
    */
        $timing = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $action_types = array('CONS', 'DOCR', 'DOCP');
            foreach ($action_types as $action_type) {
                $sql = "
                    SELECT tid
                    FROM tp_binding
                    WHERE pid IN (
                        SELECT id
                        FROM products
                        WHERE action_type = '".$action_type."'
                    )
                ";
                $result = $this->core->getRows($sql);
                $tp_binding = $this->service->implode_array('\',\'', $result, 'tid');
                $action_name = Actions::getParam($action_type);
                $timing[] = array(
                    'description'   => $action_name['action_name'],
                    'value'         => self::compare_action_id('PCSS', 'CANC', $tp_binding)
                );
            }

            $timing[] = array(
                'description'   => 'Cреднее время ожидания в очереди',
                'value'         => self::compare_action_id('RSTR', 'PCSS')
            );
        }
        return $timing;
    }

    public function counting() { // Rerurns data of ticket
    /*
    −   количество обращений в МФЦ за консультациями (ед.);
    −   количество обращений в МФЦ по приему документов (ед.);
    −   количество обращений в МФЦ по получению (выдаче) документов (ед.);
    −   количество обращений граждан по предварительной записи в МФЦ (ед.);
    */
        $counting = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $action_types = array('CONS', 'DOCR', 'DOCP');
            foreach ($action_types as $action_type) {
                $sql = "
                    SELECT tid
                    FROM tp_binding
                    WHERE pid IN (
                        SELECT id
                        FROM products
                        WHERE action_type = '".$action_type."'
                    )
                ";
                $result = $this->core->getRows($sql);
                $action_name = Actions::getParam($action_type);
                $counting[] = array(
                    'description'   => $action_name['action_name'],
                    'value'         => count($result)
                );
            }

            $sql = "
                SELECT tid
                FROM ticket_actions
                WHERE action_id = 'BOOK'
            ";
            $actions = $this->core->getRows($sql);

            $counting[] = array(
                'description'   => 'Предварительная запись',
                'value'         => count($actions)
            );
        }
        return $counting;
    }

    public function categories() { 
    /*
    −   количество предоставленных заявителям услуг по категориям;
    */
        $categories = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT *
                FROM product_categories
            ";
            $product_categories = $this->core->getRows($sql);
            foreach ($product_categories as $category) {
                $sql = "
                    SELECT tid
                    FROM tp_binding
                    WHERE pid IN (
                        SELECT pid
                        FROM pc_binding
                        WHERE cid = '".$category['id']."'
                    )
                ";
                $tp_binding = $this->core->getRows($sql);

                $categories[] = array(
                    'description'   => $category['name'],
                    'value'         => count($tp_binding)
                );
            }
        }
        return $categories;
    }

    public function visitors() { 
    /*
    −   количество обслуженных центрами заявителей; (талонов)
    −   количество предоставленных заявителям услуг;
    −   количество окон приема заявителей;
    −   количество обслуженных центрами заявителей на 1 окно; (талонов)
    −   количество предоставленных заявителям услуг на 1 окно.
    */
        $visitors = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT wid
                FROM workplaces
            ";
            $workplaces = $this->core->getRows($sql);
            $sql = "
                SELECT id
                FROM tickets
            ";
            $tickets = $this->core->getRows($sql);
            $sql = "
                SELECT tid
                FROM tp_binding
            ";
            $tp_binding = $this->core->getRows($sql);
            $visitors[] = array(
                'description'   => 'Количество окон приема заявителей',
                'value'         => count($workplaces)
            );
            $visitors[] = array(
                'description'   => 'Количество обслуженных центрами заявителей',
                'value'         => count($tickets)
            );
            $visitors[] = array(
                'description'   => 'Количество обслуженных центрами заявителей на 1 окно',
                'value'         => floor(count($tickets) / count($workplaces))
            );
            $visitors[] = array(
                'description'   => 'Количество предоставленных заявителям услуг',
                'value'         => count($tp_binding)
            );
            $visitors[] = array(
                'description'   => 'Количество предоставленных заявителям услуг на 1 окно',
                'value'         => floor(count($tp_binding) / count($workplaces))
            );
        }
        return $visitors;
    }
}

?>