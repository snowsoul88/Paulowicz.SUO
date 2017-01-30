<?
class Booking {
    
    private $core;
    private $user;
    private $shedule;
    private $product;
    private $service;

    function __construct() {
        $this->core = Core::getInstance();
        $this->user = new user();
        $this->shedule = new shedule();
        $this->product = new product();
        $this->service = new service();
    }

    public function booking() { // Returns all bookings from now till ∞
        $ticket = new ticket();
        $now = time() + (Config::getParam('app.utc_timezone')*60*60);
        $sql = "
            SELECT *
            FROM booking
            WHERE bookingtime>'".$now."'
            ORDER BY bookingtime ASC
        ";
        $res = $this->core->getRows($sql); // Get full data by product

        gmdate("d-m-Y", $now);
        $data = array();
        for ($i = 0; $i<count($res); $i++) {
            $r_gmdate = gmdate("d-m-Y", $res[$i]['bookingtime']);
            $data[$i] = array(
                'date'      => $r_gmdate,
                'tickets'   => array()
            );
            foreach ($res as $k=>$v) {
                if ($r_gmdate == gmdate("d-m-Y", $res[$k]['bookingtime'])) {
                    $data[$i]['tickets'][] = $ticket->ticket_full($res[$k]['tid']);
                    unset($res[$k]);
                }
            }
        }
        return $data;
    }

    public function days($products) { // Returns shedule for days of week
        $shedule = array();
        foreach ($products as $product) {
            $shedule[$product['product_id']]['product_scheme'] = $this->product->product($product['product_id']);
            $product_shedule = $this->shedule->binded_shedule($product['product_id']);
            if (!empty($product_shedule)) {
                $shedule[$product['product_id']]['product_shedule'] = $product_shedule;
            }
            $sql = "
                SELECT *
                FROM up_binding
                WHERE pid = '".$product['product_id']."'
            ";
            $up_bindings = $this->core->getRows($sql); // Get all timing by product
            foreach ($up_bindings as $binding) {
                $user_shedule = $this->shedule->binded_shedule($binding['uid']);
                if (!empty($user_shedule)) {
                    $shedule[$product['product_id']]['users_shedule'][$binding['uid']] = array(
                        'user_state'    => $this->user->user_state($binding['uid'])
                    );
                }
            }
        }
        ksort($shedule);
        return $shedule;
    }

    public function day($products, $date) {
        $shedule = array();
        $weekday = date('w', strtotime($date)); // note: first arg to date() is lower-case L  
        foreach ($products as $product) {
            $sql = "
                SELECT *
                FROM shedule
                WHERE bid = '".$product['product_id']."'
                AND weekday = '".$weekday."'
            ";
            $res = $this->core->getRows($sql); // Get all timing by product
            foreach ($res as $r) {
                $shedule[$product['product_id']][$r['weekday']] = array();
                foreach ($res as $w) {
                    if ($r['weekday'] == $w['weekday']) {
                        $shedule[$product['product_id']][$r['weekday']][] = array(
                            'begin' => $w['begin'],
                            'end'   => $w['end']
                            );
                    }
                }
            }
        }
        ksort($shedule);
        return $shedule;
    }

    public function menu($situationId = null) {
        $menu = array();
        if ($situationId == null || $situationId == 'null') {
            $sql = "
                SELECT *
                FROM product_situations
            ";
        } else {
            $sql = "
                SELECT *
                FROM product_situations
                WHERE id = '".$situationId."'
            ";
        }
        $product_situations = $this->core->getRows($sql);
        foreach ($product_situations as $situation) {
            $sql = "
                SELECT id
                FROM products
                WHERE id IN (SELECT pid FROM ps_binding WHERE sid = '".$situation['id']."')
            ";
            $pids = $this->core->getRows($sql);
            $sql = "
                SELECT value
                FROM settings
                WHERE parameter = '".$situation['id']."'
                AND entity = 'kioskmenu_expanded'
            ";
            $kioskmenu_expanded = $this->core->getRows($sql);
            if (!empty($pids)) {
                $products = array();
                foreach ($pids as $pid) {
                    $products[] = $this->product->product($pid['id']);
                }
                $menu[] = array(
                    'product_situation_name'    => $situation['name'],
                    'product_situation_id'      => $situation['id'],
                    'kioskmenu_expanded'        => (!empty($kioskmenu_expanded)) ? $kioskmenu_expanded[0]['value'] : 'false',
                    'products'                  => $products
                );
            }
        }
        return $menu;
    }

    public function schedule($products, $situation_id = null) {
        $now = time() + (Config::getParam('app.utc_timezone')*60*60);
        $schedule = array();
        $max_reserve_days = Config::getParam('app.max_reserve_days');
        $fbd_reserve_days = Config::getParam('app.fbd_reserve_days');

        for ($i=0; $i < $max_reserve_days; $i++) {

            $max_time = 0;
            foreach ($products as $product) {
                $product_data = $this->product->product($product);
                $max_time += ($product_data['product_timedelta'] > 0) ? $product_data['product_timedelta'] : $product_data['product_timereglament'];
            }
            $date = strtotime('+'.$i+$fbd_reserve_days.' day');

            $day_begin_H = Config::getParam('app.day_begin_H');
            $day_begin_i = Config::getParam('app.day_begin_i');
            $day_end_H = Config::getParam('app.day_end_H');
            $day_end_i = Config::getParam('app.day_end_i');

            $day_begin = mktime($day_begin_H, $day_begin_i, 0, date("m", $date)  , date("d", $date), date("Y", $date)); // Getting unix-timestamp of that day begin
            $day_end = mktime($day_end_H, $day_end_i, 0, date("m", $date)  , date("d", $date), date("Y", $date)); // Getting unix-timestamp of that day end

            $fullday = $day_end - $day_begin;
            $available_periods = $fullday / $max_time;

            $reserved = array();
            $time = array();
            $minutes = array();

            $sql = "
                SELECT id, not_before
                FROM tickets
                WHERE not_before >= '".$day_begin."'
                AND not_before <= '".$day_end."'
            ";
            $tickets = $this->core->getRows($sql);

            foreach ($tickets as $ticket) {
                $sql = "
                    SELECT id, timedelta, timereglament
                    FROM products
                    WHERE id IN (
                        SELECT pid
                        FROM tp_binding
                        WHERE tid = '".$ticket['id']."'
                    )
                ";
                $tps = $this->core->getRows($sql);

                $tpmt = 0;
                foreach ($tps as $tp) {
                    $tpmt += ($tp['timedelta'] > 0) ? $tp['timedelta'] : $tp['timereglament'];
                }
                $reserved[] = array(
                    'start' => $ticket['not_before'],
                    'end'   => $ticket['not_before'] + $tpmt
                );
            }

            for ($p = $day_begin; $p < $day_end - $max_time; $p = $p + $max_time) {
                foreach ($reserved as $cut) {
                    if (($cut['start'] < ($p + $max_time)) && ($cut['end'] > $p)) {
                        continue 2;
                    }
                }
                $H = date(date("H", $p));
                $minutes[$H][] = date(date("i", $p));
                $time[$H] = array(
                    'hour'      => $H,
                    'minutes'   => $minutes[$H]
                );
            }

            $schedule[] = array(
                'y'     => date("Y", $date),
                'd'     => date("d", $date),
                'm'     => date("m", $date),
                'date'  => $this->service->date(date("m", $date)).', '.date("d", $date),
                'time'  => array_values($time)
            );
        }
        return $schedule;
    }
}

?>