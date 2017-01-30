<?
class Ticket {
    
    private $core;
    private $service;
    private $product;
    private $actions;
    private $client;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->product = new product();
        $this->actions = new Actions();
        $this->client = new client();
    }

    public function ticket_situation($id) {

    }

    public function ticket_workplaces($id) {
        $tp_binding = self::tp_binding($id);
        // $sql = "
        //     SELECT tid
        //     FROM tp_binding
        //     WHERE pid IN (
        //         SELECT pid
        //         FROM up_binding
        //         WHERE uid = '".$token['uid']."'
        //     )
        //     OR pid IN (
        //         SELECT pid
        //         FROM cp_binding
        //         WHERE cid = '".$token['cid']."'
        //     )
        // ";
    }

    public function ticket($id) { // Rerurns data of ticket
        $ticket = array();
        $sql = "
            SELECT *
            FROM tickets
            WHERE id='".$id."'
        ";
        $res = $this->core->getRows($sql); // Get full data by ticket
        if (!empty($res)) {
            $ticket = array(
                'ticket_id'                 => $res[0]['id'],
                'ticket_visitor'            => $res[0]['visitor'],
                'ticket_created'            => $res[0]['created'],
                'ticket_created_readable'   => gmdate('d-m-Y H:i', $res[0]['created']),
                'ticket_pincode'            => $res[0]['pincode'],
                'ticket_not_before'         => ($res[0]['not_before'] == 0) ? 'В порядке живой очереди' : gmdate('d-m-Y H:i', $res[0]['not_before']),
                'ticket_prefix'             => $res[0]['prefix'],
                'ticket_number'             => $res[0]['number'],
                'ticket_full_number'        => $res[0]['prefix'].$res[0]['number'],
                'ticket_booknote'           => ($res[0]['not_before'] == 0) ? '' : Config::getParam('app.booknote'),
                'ticket_situation'          => self::ticket_situation($id),
                'queue_recent_state'        => self::queue_recent_state($id)
            );
        }
        return $ticket;
    }

    public function tickets($include_closed=false) { // Rerurns all tickets for that day
        $day_begin_H = Config::getParam('app.day_begin_H');
        $day_begin_i = Config::getParam('app.day_begin_i');
        $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
        $now = time() + (Config::getParam('app.utc_timezone')*60*60);
        //$include_closed = ($include_closed == true) ? 'state=state':'state!=0';
        $sql = "
            SELECT *
            FROM (
                SELECT *
                FROM tickets
                ORDER BY created DESC
            ) A
            WHERE created BETWEEN '".$day_begin_timestamp."' AND '".$now."'
            ORDER BY created DESC
        ";

        $res = $this->core->getRows($sql); // Get all tickets
        $tickets = array();
        foreach ($res as $ticket) {
            $tickets[] = self::ticket_full($ticket['id']); // Get full data of this ticket for returning
        }
        return $tickets;
    }

    public function tp_binding($tid) { // Returns all bindings ticket+product
        $sql = "
            SELECT *
            FROM tp_binding
            WHERE tid='".$tid."'
            ORDER BY priority ASC
        ";
        $res = $this->core->getRows($sql); // Get all bindings of that ticket
        return $res;
    }

    public function ticket_full($ticketId) {
        $ticket = self::ticket($ticketId); // Get data of this ticket for returning
        $tp_binding = self::tp_binding($ticketId); // Get all bindings of this ticket for returning
        foreach ($tp_binding as $b) {
            $ticket['products'][] = $this->product->product($b['pid']); // Get product full data and insert into array for returning
        }
        return $ticket;
    }
    
    public function create($products, $situation_id = null, $visitor = null, $not_before = 0) { // Creating new ticket
        $ticket = array();
        if (!empty($products) || ($situation_id != null)) { // Check is has products in new ticket
            if (isset($GLOBALS['Bearer'])) {
                $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
                $day_begin_H = Config::getParam('app.day_begin_H');
                $day_begin_i = Config::getParam('app.day_begin_i');
                $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
                $guid = $this->service->guid(); // Create new GUID for ticket
                $pincode = mt_rand(100000, 999999);

                $now = time() + (Config::getParam('app.utc_timezone')*60*60);
                
                $prefixes = array();
                $ticket_products = array();
                $situation_name = null;
                if ($situation_id == null) {
                    foreach ($products as $product_id) { // 
                        $sql = "
                            INSERT INTO tp_binding (tid, pid)
                            VALUES ('".$guid."', '".$product_id."')
                        ";
                        $this->core->execQuery($sql); // Create binding for ticket+products
                        $sql = "
                            SELECT sid
                            FROM ps_binding
                            WHERE pid = '".$product_id."'
                        ";
                        $sids = $this->core->getRows($sql);
                        foreach ($sids as $sid) {
                            $sql = "
                                SELECT prefix
                                FROM product_situations
                                WHERE id = '".$sid['sid']."'
                            ";
                            $situation = $this->core->getRows($sql);
                            if (!empty($situation)) {
                                $prefixes[] = $situation[0]['prefix'];
                            }
                        }
                        $ticket_products[] = $this->product->product($product_id); // Add full prod data to array for ticket
                    }
                } else {
                    if (!empty($products)) {
                        foreach ($products as $product_id) { // 
                            $sql = "
                                INSERT INTO tp_binding (tid, pid)
                                VALUES ('".$guid."', '".$product_id."')
                            ";
                            $this->core->execQuery($sql); // Create binding for ticket+products
                            $ticket_products[] = $this->product->product($product_id); // Add full prod data to array for ticket
                        }
                    }
                    $sql = "
                        INSERT INTO ts_binding (tid, sid)
                        VALUES ('".$guid."', '".$situation_id."')
                    ";
                    $this->core->execQuery($sql); // Create binding for ticket+products
                    $sql = "
                        SELECT prefix, name
                        FROM product_situations
                        WHERE id = '".$situation_id."'
                    ";
                    $situation = $this->core->getRows($sql);
                    if (!empty($situation)) {
                        $prefixes[] = $situation[0]['prefix'];
                        $situation_name = $situation[0]['name'];
                    }
                }
                $prefixes = array_count_values($prefixes);
                arsort($prefixes);
                if (empty($prefixes)) {
                    $prefix = null;
                } else {
                    $prefix = array_keys($prefixes);
                    $prefix = $prefix[0];
                }

                if ($not_before != 0) {
                    $not_before = $not_before + ((Config::getParam('app.utc_timezone') + 1) * 60 * 60);
                    $day_begin_H = Config::getParam('app.day_begin_H');
                    $day_begin_i = Config::getParam('app.day_begin_i');
                    $day_end_H = Config::getParam('app.day_end_H');
                    $day_end_i = Config::getParam('app.day_end_i');
                    $day_begin = mktime($day_begin_H, $day_begin_i, 0, date("m", $not_before)  , date("d", $not_before), date("Y", $not_before)); // Getting unix-timestamp of that day begin
                    $day_end = mktime($day_end_H, $day_end_i, 0, date("m", $not_before)  , date("d", $not_before), date("Y", $not_before)); // Getting unix-timestamp of that day end
                    $sql = "
                        SELECT number, prefix
                        FROM tickets
                        WHERE prefix = '".$prefix."'
                        AND not_before BETWEEN '".$day_begin."' AND '".$day_end."'
                        ORDER BY number DESC
                    ";
                } else {
                    $sql = "
                        SELECT number, prefix
                        FROM tickets
                        WHERE prefix = '".$prefix."'
                        AND created BETWEEN '".$day_begin_timestamp."' AND '".$now."'
                        ORDER BY number DESC
                    ";
                }
                $tickets = $this->core->getRows($sql); // Get all tickets

                $number = (empty($tickets)) ? 1 : (int)$tickets[0]['number'] + 1;

                $sql = "
                    INSERT INTO tickets (id, created, pincode, prefix, number, visitor, not_before)
                    VALUES ('".$guid."', '".$now."', '".$pincode."', '".$prefix."', '".$number."', '".$visitor."', '".$not_before."')
                ";
                $this->core->execQuery($sql); // Create new ticket
                $state = ($not_before == 0) ? 'RSTR' : 'BOOK';
                self::ticket_action($guid, null, null, $state);
                $ticket = self::ticket($guid); // Get data of this ticket for returning
                $ticket['products'] = $ticket_products;
                $ticket['situation_name'] = $situation_name;
            }
        }
        return $ticket;
    }

    public function submit($pincode) { // Submitting booked ticket for terminal
        $sql = "
            SELECT id
            FROM tickets
            WHERE pincode = '".$pincode."'
        ";
        $ticket = $this->core->getRows($sql);
        if (!empty($ticket)) {
            $now = time() + (Config::getParam('app.utc_timezone')*60*60);
            $sql = "
                INSERT INTO ticket_actions (tid, cid, uid, action_id, action_time)
                VALUES ('".$ticket[0]['id']."', '', '', 'RSTR', '".$now."')
            ";
            $this->core->execQuery($sql);
            $ticket = self::ticket($ticket[0]['id']); // Get data of this ticket for returning
            return $ticket;
        }
    }

    public function ticket_check($pincode = null, $prefix = null, $number = null, $visitor = null) {
        $data = array(
            'ticket_exists' => false
        );
        if ($pincode != null) {
            $sql = "
                SELECT id
                FROM tickets
                WHERE pincode = '".$pincode."'
            ";
            $ticket = $this->core->getRows($sql);
        }
        if (!empty($ticket)) {
            $ticket = self::ticket($ticket[0]['id']); // Get data of this ticket for returning
            $template = self::from_template($ticket);
            $data = $ticket;
            $data['ticket_exists'] = true;
            $data['ticket_template'] = $template;
        }
        return $data;
    }

    public function from_template($ticket) {
        $sql = "
            SELECT *
            FROM settings
            WHERE entity = 'ticket'
            AND parameter = 'template'
        ";
        $template = $this->core->getRows($sql);
        if (empty($template)) {
            $template[0]['value'] = '<html><table face="Arial" width="6.5cm" height="6cm"><tr><th colspan="2" class="organization-name" style="font-size:6px; text-align:center;">{organization.name}
</th></tr><tr><td style="font-size:6px; text-align:center;">{ticket.numheader}</td><td style="font-size:6px; text-align:center;">{ticket.regheader}</td></tr><tr><td rowspan="3" style="font-size:16px; text-align:center; font-weight:600;" class="ticket-number">{ticket.prefix} {ticket.number}</td><td style="font-size:6px; text-align:center; font-weight:600;">{ticket.created}</td></tr><tr><td style="font-size:6px; text-align:center;">{ticket.bookheader}</td></tr><tr><td style="font-size:6px; text-align:center; font-weight:600;">{ticket.notbefore}</td></tr><tr><td style="font-size:6px; text-align:center;">{ticket.callheader}</td><td style="font-size:6px; text-align:center;">{ticket.visheader}</td></tr><tr><td style="font-size:6px; text-align:center; font-weight:600;">{organization.callcenter}</td><td style="font-size:6px; text-align:center; font-weight:600;">{ticket.visitor}</td></tr><tr><td colspan="2" style="font-size:6px; text-align:center;">{ticket.sitheader}</td></tr><tr><td colspan="2" style="font-size:6px; text-align:center;">{ticket.situation}</td></tr><tr><td colspan="2" style="font-size:6px; text-align:center;">{ticket.booknote}</td></tr><tr><td colspan="2" style="font-size:6px; text-align:center;">{ticket.pincode}</td></tr></table></html>';
        }
        preg_match_all('/{(.*?)}/', $template[0]['value'], $matches);
        foreach ($matches[1] as $match) {
            $ep = explode('.', $match);
            $sql = "
                SELECT *
                FROM settings
                WHERE entity = '".$ep[0]."'
                AND parameter = '".$ep[1]."'
            ";
            $value = $this->core->getRows($sql);
            if (!empty($value)) {
                $template[0]['value'] = preg_replace('/{'.$match.'}/', $value[0]['value'], $template[0]['value']);
                preg_match_all('/\[([^\]]*)\]/', $value[0]['value'], $loc_matches);
                foreach ($loc_matches[1] as $loc_match) {
                    if (isset($ticket[$loc_match])) {
                        $template[0]['value'] = preg_replace('/\[('.$loc_match.']*)\]/', $ticket[$loc_match], $template[0]['value']);
                    } else {
                        $template[0]['value'] = preg_replace('/\[('.$loc_match.']*)\]/', '', $template[0]['value']);
                    }
                }
            }
        }
        return $template[0]['value'];
    }

    public function create_kiosk($products, $situation_id = null, $visitor = null, $not_before = null) { // Creating new ticket for terminal
        $ticket = self::create($products, $situation_id, $visitor, $not_before);
        $template = self::from_template($ticket);
        return $template;
    }

    public function submit_kiosk($pincode) { // Creating new ticket for terminal
        $ticket = self::submit($pincode);
        $template = self::from_template($ticket);
        return $template;
    }

    public function addProduct($ticketId, $productId, $priority = 0) { // Add product for ticket
        $ticket = array();
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $now = time() + (Config::getParam('app.utc_timezone')*60*60);
            $sql = "
                INSERT INTO tp_binding (tid, pid, priority)
                VALUES ('".$ticketId."', '".$productId."', '".$priority."')
            ";
            $this->core->execQuery($sql); // Create binding for ticket+products
            self::ticket_action($ticketId, $token['cid'], $token['uid'], 'CNGD');
            $ticket = self::ticket_full($ticketId); // Get data of this ticket for returning
        }
        return $ticket;
    }

    public function delProduct($ticketId, $ticketProductId) { // Delete binding for ticket+products
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                DELETE FROM tp_binding
                WHERE tid='".$ticketId."' AND pid='".$ticketProductId."'
            ";
            $this->core->execQuery($sql); // Delete binding for ticket+products
            self::ticket_action($ticketId, $token['cid'], $token['uid'], 'CNGD');
            return true;
        }
    }

    public function setPriority($ticketId, $ticketProductId, $priority) { // Delete binding for ticket+products
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)$this->service->jwt_decode($GLOBALS['Bearer']);
            $sql = "
                UPDATE tp_binding
                SET priority='".$priority."'
                WHERE tid='".$ticketId."' AND pid='".$ticketProductId."'
            ";
            $this->core->execQuery($sql); // Delete binding for ticket+products
            self::ticket_action($ticketId, $token['cid'], $token['uid'], 'CNGD');
            return true;
        }
    }

    public function ticket_action($ticketId, $cid = null, $uid = null, $actionId) {
        $now = time() + (Config::getParam('app.utc_timezone')*60*60);
        $sql = "
            INSERT INTO ticket_actions (tid, cid, uid, action_time, action_id)
            VALUES ('".$ticketId."', '".$cid."', '".$uid."', '".$now."', '".$actionId."')
        ";
        $this->core->execQuery($sql); // Create binding for ticket
        return true;
    }

    public function queue_recent_state($tid) {
        $queue_recent_state = array();
        $sql = "
            SELECT * FROM ticket_actions
            WHERE tid = '".$tid."'
            ORDER BY action_time DESC
            LIMIT 0,1
        ";
        $queue = $this->core->getRows($sql);
        if (!empty($queue)) {
            $actions = $this->actions->getParam($queue[0]['action_id']);
            $queue_recent_state = array(
                "action_description" => $actions['action_description'],
                "action_name"        => $actions['action_name'],
                "action_id"          => $queue[0]['action_id'],
                "client_state"       => $this->client->client_state($queue[0]['cid'])
            );
        }
        return $queue_recent_state;
    }
}

?>