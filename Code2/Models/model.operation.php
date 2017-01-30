<?
class Operation {
    
    private $core;
    private $service;
    private $ticket;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->ticket = new ticket();
    }

    public function redirect($productId, $userId = null, $workPlaceId = null, $return = false) {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            var_dump($productId, $userId, $workPlaceId, $return);
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('WAIT', 'PCSS')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $return = ($return == false) ? 'RDRC':'RDRR';
                $this->ticket->ticket_action($tid[0]['tid'], $workPlaceId, $userId, $return);
                $ticket = $this->ticket->ticket_full($tid[0]['tid']);
            }
        }
        return $ticket;
    }

    public function personal_count() {
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT * FROM queue_actions
                WHERE cid = '".$token['cid']."'
            ";
            $queue = $this->core->getRows($sql);
            return count($queue);
        } else {
            $this->service->error_log('Клиент не идентифицирован');
            return false;
        }
    }

    public function personal_queue() {
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $personal_queue = array();
            $sql = "
                SELECT * FROM queue_actions
                WHERE cid = '".$token['cid']."'
            ";
            $queue = $this->core->getRows($sql);
            foreach ($queue as $q) {
                $personal_queue[] = $this->ticket->ticket_full($q['tid']);
            }
            return $personal_queue;
        } else {
            $this->service->error_log('Клиент не идентифицирован');
            return false;
        }
    }

    public function queue_count() {
        $queue = self::queue_operation();
        return count($queue);
    }

    public function queue_operation() {
        $queue_operation = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $day_begin_H = Config::getParam('app.day_begin_H');
            $day_begin_i = Config::getParam('app.day_begin_i');
            $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
            $now = time();
            // $sql = "
            //     SELECT tid FROM ticket_actions
            //     WHERE id IN (
            //         SELECT id FROM ticket_actions AS alias_i
            //         WHERE tid NOT IN (
            //             SELECT tid FROM ticket_actions AS alias_ii
            //             WHERE alias_i.action_time < alias_ii.action_time
            //         )
            //     ) AND action_id IN ('WAIT', 'PCSS', 'CANC', 'REFD')
            // ";
            // $result = $this->core->getRows($sql);
            // $ticket_actions = $this->service->implode_array('\',\'', $result, 'tid');
            /*
            -- Запрос ниже включает только талоны с первым статусом 'RSTR' за сегодня ------------------------
            */
            $sql = "
                SELECT tid FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('RSTR')
                AND action_time >= '".$day_begin_timestamp."'
            ";
            $result = $this->core->getRows($sql);
            $tickets_today = $this->service->implode_array('\',\'', $result, 'tid');

            $sql = "
                SELECT tid FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('RDRC', 'RDRR')
                AND uid != '".$token['uid']."'
                AND cid != '".$token['cid']."'
            ";
            $result = $this->core->getRows($sql);
            $ticket_actions_rdr = $this->service->implode_array('\',\'', $result, 'tid');

            $sql = "
                SELECT tid
                FROM tp_binding
                WHERE pid IN (
                    SELECT pid
                    FROM up_binding
                    WHERE uid = '".$token['uid']."'
                ) AND tid IN ('".$tickets_today."')
                OR pid IN (
                    SELECT pid
                    FROM cp_binding
                    WHERE cid = '".$token['cid']."'
                ) AND tid IN ('".$tickets_today."')
            ";
            $result = $this->core->getRows($sql);
            $tp_binding = $this->service->implode_array('\',\'', $result, 'tid');

            $sql = "
                SELECT tid
                FROM ts_binding
                WHERE sid IN (
                    SELECT sid
                    FROM cs_binding
                    WHERE cid = '".$token['cid']."'
                ) AND tid IN ('".$tickets_today."')
                OR sid IN (
                    SELECT sid
                    FROM us_binding
                    WHERE uid = '".$token['uid']."'
                ) AND tid IN ('".$tickets_today."')
            ";
            $result = $this->core->getRows($sql);
            $ts_binding = $this->service->implode_array('\',\'', $result, 'tid');
                    
            $sql = "
                SELECT id 
                FROM tickets
                WHERE not_before <= '".$now."'
                AND id NOT IN ('".$ticket_actions_rdr."')
                AND id IN ('".$tp_binding."')
                OR not_before <= '".$now."'
                AND id NOT IN ('".$ticket_actions_rdr."')
                AND id IN ('".$ts_binding."')
                ORDER BY created ASC
            "; 
            $queue = $this->core->getRows($sql);
            foreach ($queue as $q) {
                $queue_operation[] = $this->ticket->ticket($q['id']);
            }
        }
        return $queue_operation;
    }

    public function recent() {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('WAIT', 'PCSS')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $ticket = $this->ticket->ticket_full($tid[0]['tid']);
            }
        }
        return $ticket;
    }

    public function invite($product_id = null) {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $day_begin_H = Config::getParam('app.day_begin_H');
            $day_begin_i = Config::getParam('app.day_begin_i');
            $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
            $now = time();
            $sql = "
                SELECT tid
                FROM ticket_actions
                WHERE uid = '".$token['uid']."'
                AND cid = '".$token['cid']."'
                AND action_time >= '".$day_begin_timestamp."'
                AND action_id IN ('PCSS', 'WAIT')
                AND tid NOT IN (
                    SELECT tid
                    FROM ticket_actions
                    WHERE action_id IN ('CANC', 'REFD', 'RDRR', 'RDRC')
                    AND action_time >= '".$day_begin_timestamp."'
                )
            ";
            $unclosed = $this->core->getRows($sql);
            if (empty($unclosed)) {
                /*
                -- Запрос ниже исключает талоны с последними статусами 'WAIT', 'PCSS', 'CANC', 'REFD' -----------
                */
                // $sql = "
                //     SELECT tid FROM ticket_actions
                //     WHERE id IN (
                //         SELECT id FROM ticket_actions AS alias_i
                //         WHERE tid NOT IN (
                //             SELECT tid FROM ticket_actions AS alias_ii
                //             WHERE alias_i.action_time < alias_ii.action_time
                //         )
                //     ) AND action_id IN ('WAIT', 'PCSS', 'CANC', 'REFD')
                // ";
                // $result = $this->core->getRows($sql);
                // $ticket_actions = $this->service->implode_array('\',\'', $result, 'tid');
                /*
                -- Запрос ниже включает только талоны с первым статусом 'RSTR' за сегодня ------------------------
                */
                $sql = "
                    SELECT tid FROM ticket_actions
                    WHERE id IN (
                        SELECT id FROM ticket_actions AS alias_i
                        WHERE tid NOT IN (
                            SELECT tid FROM ticket_actions AS alias_ii
                            WHERE alias_i.action_time < alias_ii.action_time
                        )
                    )
                    AND action_id IN ('RSTR')
                    AND action_time >= '".$day_begin_timestamp."'
                ";
                $result = $this->core->getRows($sql);
                $tickets_today = $this->service->implode_array('\',\'', $result, 'tid');
                /*
                -- Запрос ниже исключает талоны, перенаправленные не текущему пользователю -----------------------
                */
                $sql = "
                    SELECT tid FROM ticket_actions
                    WHERE id IN (
                        SELECT id FROM ticket_actions AS alias_i
                        WHERE tid NOT IN (
                            SELECT tid FROM ticket_actions AS alias_ii
                            WHERE alias_i.action_time < alias_ii.action_time
                        )
                    )
                    AND action_id IN ('RDRC', 'RDRR')
                    AND uid != '".$token['uid']."'
                    AND cid != '".$token['cid']."'
                    AND action_time >= '".$day_begin_timestamp."'
                ";
                $result = $this->core->getRows($sql);
                $ticket_actions_rdr = $this->service->implode_array('\',\'', $result, 'tid');
                if ($product_id == null) { // Здесь нужно выбрать талоны, которые можно пригласить: соответствуют уровню пользователя либо рабочего места (готово) и не являются уже приглашенными или обрабатываемыми в данный момент, а также не являются перенаправленными и закрытыми

                    $sql = "
                        SELECT tid
                        FROM tp_binding
                        WHERE pid IN (
                            SELECT pid
                            FROM up_binding
                            WHERE uid = '".$token['uid']."'
                        ) AND tid IN ('".$tickets_today."')
                        OR pid IN (
                            SELECT pid
                            FROM cp_binding
                            WHERE cid = '".$token['cid']."'
                        ) AND tid IN ('".$tickets_today."')
                    ";
                    $result = $this->core->getRows($sql);
                    $tp_binding = $this->service->implode_array('\',\'', $result, 'tid');

                    $sql = "
                        SELECT tid
                        FROM ts_binding
                        WHERE sid IN (
                            SELECT sid
                            FROM cs_binding
                            WHERE cid = '".$token['cid']."'
                        ) AND tid IN ('".$tickets_today."')
                        OR sid IN (
                            SELECT sid
                            FROM us_binding
                            WHERE uid = '".$token['uid']."'
                        ) AND tid IN ('".$tickets_today."')
                    ";
                    $result = $this->core->getRows($sql);
                    $ts_binding = $this->service->implode_array('\',\'', $result, 'tid');
                            
                    $sql = "
                        SELECT id 
                        FROM tickets
                        WHERE not_before <= '".$now."'
                        AND id NOT IN ('".$ticket_actions_rdr."')
                        AND id IN ('".$tp_binding."')
                        OR not_before <= '".$now."'
                        AND id NOT IN ('".$ticket_actions_rdr."')
                        AND id IN ('".$ts_binding."')
                        ORDER BY created ASC
                        LIMIT 0, 1
                    ";
                    $tickets = $this->core->getRows($sql);
                } else {
                    $sql = "
                        SELECT tid
                        FROM tp_binding
                        WHERE pid IN ('".$product_id."')
                        AND tid IN ('".$tickets_today."')
                    ";
                    $result = $this->core->getRows($sql);
                    $tp_binding = $this->service->implode_array('\',\'', $result, 'tid');

                    $sql = "
                        SELECT id 
                        FROM tickets
                        WHERE not_before <= '".$now."'
                        AND id NOT IN ('".$ticket_actions_rdr."')
                        AND id IN ('".$tp_binding."')
                        ORDER BY created ASC
                        LIMIT 0, 1
                    ";
                    $tickets = $this->core->getRows($sql);
                }
                if (!empty($tickets)) {
                    $this->ticket->ticket_action($tickets[0]['id'], $token['cid'], $token['uid'], 'WAIT');
                    $ticket = $this->ticket->ticket_full($tickets[0]['id']);
                }
            }
        }
        return $ticket;
    }

    public function start() {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $now = time();
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('WAIT')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $this->ticket->ticket_action($tid[0]['tid'], $token['cid'], $token['uid'], 'PCSS');
                $ticket = $this->ticket->ticket_full($tid[0]['tid']);
            }
        }
        return $ticket;
    }

    public function reject() {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $now = time();
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('WAIT', 'PCSS')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $this->ticket->ticket_action($tid[0]['tid'], $token['cid'], $token['uid'], 'REFD');
                $ticket = $this->ticket->ticket_full($tid[0]['tid']);
            }
        }
        return $ticket;
    }

    public function complete() {
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $now = time() + (Config::getParam('app.utc_timezone')*60*60);
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('PCSS')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $this->ticket->ticket_action($tid[0]['tid'], $token['cid'], $token['uid'], 'CANC');
                $ticket = $this->ticket->ticket_full($tid[0]['tid']);
            }
        }
        return $ticket;
    }

    public function call_again() {
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $now = time();
            $sql = "
                SELECT * FROM ticket_actions
                WHERE id IN (
                    SELECT id FROM ticket_actions AS alias_i
                    WHERE tid NOT IN (
                        SELECT tid FROM ticket_actions AS alias_ii
                        WHERE alias_i.action_time < alias_ii.action_time
                    )
                )
                AND action_id IN ('WAIT')
                AND cid = '".$token['cid']."'
                AND uid = '".$token['uid']."'
            ";
            $tid = $this->core->getRows($sql);
            if (!empty($tid)) {
                $this->ticket->ticket_action($tid[0]['tid'], $token['cid'], $token['uid'], 'WAIT');
            }
        }
    }
}

?>