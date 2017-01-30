<?
class Notifications {
    
    private $core;
    private $service;
    private $ticket;
    private $actions;
    private $client;
    private $workplace;

    function __construct() {
        $this->core = Core::getInstance();
        $this->service = new service();
        $this->ticket = new ticket();
        $this->actions = new Actions();
        $this->client = new client();
        $this->workplace = new workplace();
    }

    public function queue_notifications() {
        $queue_notifications = array();
        $ticket_full = array();
        $day_begin_H = Config::getParam('app.day_begin_H');
        $day_begin_i = Config::getParam('app.day_begin_i');
        $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
        $closed_workplace_state = Config::getParam('app.closed_workplace_state'); // 'Закрыто'
        $opened_workplace_state = Config::getParam('app.opened_workplace_state'); // 'Свободно'
        $sql = "
            SELECT *
            FROM (
                SELECT *
                FROM workplaces
                WHERE workplace_operable = '1'
                ORDER BY workplace_number ASC
            ) A
            ORDER BY workplace_type ASC, workplace_number ASC
        ";
        $workplaces = $this->core->getRows($sql);
        foreach ($workplaces as $workplace) {
            $workplace_state = $this->workplace->workplace_state_plasma($workplace['wid']);
            $client_state = $workplace['cid'] ? $this->client->client_state($workplace['cid']): array();

            $action = array();
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
                AND action_time >= '".$day_begin_timestamp."'
                AND cid = '".$workplace['cid']."'
            ";
            $queue = $this->core->getRows($sql);

            if (!empty($queue)) {
                $ticket = $this->ticket->ticket($queue[0]['tid']);
                $actions = $this->actions->getParam($queue[0]['action_id']);
                $action = array(
                    "action_name"        => $actions['action_name'],
                    "action_id"          => $queue[0]['action_id'],
                    "ticket"             => array(
                        'ticket_prefix'     => $ticket['ticket_prefix'],
                        'ticket_number'     => $ticket['ticket_number']
                    )
                );
            } else {
                if (!empty($client_state['user_state'])) {
                    $type_operable = (!empty($client_state['user_state']['user_recent_state'])) ? $client_state['user_state']['user_recent_state']['type_operable'] : 0;
                    if ($type_operable == 1) {
                        $type_name = $opened_workplace_state;
                    } else {
                        if (!empty($client_state['user_state']['user_recent_state'])) {
                            $type_name = $client_state['user_state']['user_recent_state']['type_name'];
                            foreach ($client_state['user_state']['user_shedule'] as $el) {
                                if ($el['shedule_type']['type_id'] == $client_state['user_state']['user_recent_state']['type_id']) {
                                    $type_name = $type_name.' с'.$el['begin'].' по '.$el['end'];
                                }
                            }
                        } else {
                            $type_name = $closed_workplace_state;
                        }
                    }
                } else {
                    $type_name = $closed_workplace_state;
                }
                
                $action = array(
                    "action_name"        => $type_name,
                    "action_id"          => null,
                    "ticket"             => array(
                        'ticket_prefix'     => null,
                        'ticket_number'     => null
                    )
                );
            }

            $queue_notifications[] = array(
                "workplace_state"           => $workplace_state,
                "action"                    => $action
            );
        }
        return $queue_notifications;
    }

    public function queue_recent_notification() {
        $day_begin_H = Config::getParam('app.day_begin_H');
        $day_begin_i = Config::getParam('app.day_begin_i');
        $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
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
            AND action_time >= '".$day_begin_timestamp."'
            ORDER BY action_time DESC
            LIMIT 0, 1
        ";
        $tid = $this->core->getRows($sql);
        if (!empty($tid)) {
            $sql = "
                SELECT *
                FROM workplaces
                WHERE cid = '".$tid[0]['cid']."'
                ORDER BY workplace_number ASC
            ";
            $wid = $this->core->getRows($sql);
            $workplace = (empty($wid)) ? array() : $this->workplace->workplace_state($wid[0]['wid']);
            $ticket = $this->ticket->ticket($tid[0]['tid']);
            $recent['ticket_prefix'] = $ticket['ticket_prefix'];
            $recent['ticket_number'] = $ticket['ticket_number'];
            $recent['queue_recent_state']['action_id'] = $ticket['queue_recent_state']['action_id'];
            $recent['workplace_state']['workplace_type'] = (empty($wid)) ? null : $workplace['workplace_type'];
            $recent['workplace_state']['workplace_number'] = (empty($wid)) ? null : $workplace['workplace_number'];
            $recent['hash'] = hash('crc32', $tid[0]['tid']+$tid[0]['action_time']);
            return $recent;
        }
    }

    public function queue_audio_notification() {
        $audio = array(
            'sound_1'         => array('src' => 'Audio/1/1_1.mp3'),
            'sound_2'         => array('src' => 'Audio/2/2_1.mp3'),
            'sound_3'         => array('src' => 'Audio/3/3_1.mp3'),
            'sound_4'         => array('src' => 'Audio/4/4_1.mp3'),
            'sound_5'         => array('src' => 'Audio/5/5_1.mp3'),
            'sound_6'         => array('src' => 'Audio/6/6_1.mp3'),
            'sound_7'         => array('src' => 'Audio/7/7_1.mp3'),
            'sound_8'         => array('src' => 'Audio/8/8_1.mp3'),
            'sound_9'         => array('src' => 'Audio/9/9_1.mp3'),
            'sound_10'        => array('src' => 'Audio/10/10_1.mp3'),
            'sound_11'        => array('src' => 'Audio/11/11_1.mp3'),
            'sound_12'        => array('src' => 'Audio/12/12_1.mp3'),
            'sound_13'        => array('src' => 'Audio/13/13_1.mp3'),
            'sound_14'        => array('src' => 'Audio/14/14_1.mp3'),
            'sound_15'        => array('src' => 'Audio/15/15_1.mp3'),
            'sound_16'        => array('src' => 'Audio/16/16_1.mp3'),
            'sound_17'        => array('src' => 'Audio/17/17_1.mp3'),
            'sound_18'        => array('src' => 'Audio/18/18_1.mp3'),
            'sound_19'        => array('src' => 'Audio/19/19_1.mp3'),
            'sound_20'        => array('src' => 'Audio/20/20_1.mp3'),
            'sound_30'        => array('src' => 'Audio/30/30_1.mp3'),
            'sound_40'        => array('src' => 'Audio/40/40_1.mp3'),
            'sound_50'        => array('src' => 'Audio/50/50_1.mp3'),
            'sound_60'        => array('src' => 'Audio/60/60_1.mp3'),
            'sound_70'        => array('src' => 'Audio/70/70_1.mp3'),
            'sound_80'        => array('src' => 'Audio/80/80_1.mp3'),
            'sound_90'        => array('src' => 'Audio/90/90_1.mp3'),
            'sound_100'       => array('src' => 'Audio/100/100_1.mp3'),
            'sound_200'       => array('src' => 'Audio/200/200_1.mp3'),
            'sound_300'       => array('src' => 'Audio/300/300_1.mp3'),
            'sound_400'       => array('src' => 'Audio/400/400_1.mp3'),
            'sound_500'       => array('src' => 'Audio/500/500_1.mp3'),
            'sound_600'       => array('src' => 'Audio/600/600_1.mp3'),
            'sound_700'       => array('src' => 'Audio/700/700_1.mp3'),
            'sound_800'       => array('src' => 'Audio/800/800_1.mp3'),
            'sound_900'       => array('src' => 'Audio/900/900_1.mp3'),
            'sound_1000'      => array('src' => 'Audio/1000/1000_1.mp3'),
            'sound_ticket'    => array('src' => 'Audio/Talon/Talon1.mp3'),
            'sound_number'    => array('src' => 'Audio/Nomer/Nomer1.mp3'),
            'sound_window'    => array('src' => 'Audio/Okno/Okno1.mp3'),
            'sound_cashdesk'  => array('src' => 'Audio/Kassa/Kassa1.mp3'),
            'sound_cabinet'   => array('src' => 'Audio/Kabinet/Kabinet1.mp3'),
            'sound_a'         => array('src' => 'Audio/Rus_1/sound_a.mp3'),
            'sound_b'         => array('src' => 'Audio/Rus_1/sound_b.mp3'),
            'sound_v'         => array('src' => 'Audio/Rus_1/sound_v.mp3'),
            'sound_g'         => array('src' => 'Audio/Rus_1/sound_g.mp3'),
            'sound_d'         => array('src' => 'Audio/Rus_1/sound_d.mp3'),
            'sound_e'         => array('src' => 'Audio/Rus_1/sound_e.mp3'),
            'sound_jo'        => array('src' => 'Audio/Rus_1/sound_jo.mp3'),
            'sound_j'         => array('src' => 'Audio/Rus_1/sound_j.mp3'),
            'sound_z'         => array('src' => 'Audio/Rus_1/sound_z.mp3'),
            'sound_i'         => array('src' => 'Audio/Rus_1/sound_i.mp3'),
            'sound_iy'        => array('src' => 'Audio/Rus_1/sound_iy.mp3'),
            'sound_k'         => array('src' => 'Audio/Rus_1/sound_k.mp3'),
            'sound_l'         => array('src' => 'Audio/Rus_1/sound_l.mp3'),
            'sound_m'         => array('src' => 'Audio/Rus_1/sound_m.mp3'),
            'sound_n'         => array('src' => 'Audio/Rus_1/sound_n.mp3'),
            'sound_o'         => array('src' => 'Audio/Rus_1/sound_o.mp3'),
            'sound_p'         => array('src' => 'Audio/Rus_1/sound_p.mp3'),
            'sound_r'         => array('src' => 'Audio/Rus_1/sound_r.mp3'),
            'sound_s'         => array('src' => 'Audio/Rus_1/sound_s.mp3'),
            'sound_t'         => array('src' => 'Audio/Rus_1/sound_t.mp3'),
            'sound_u'         => array('src' => 'Audio/Rus_1/sound_u.mp3'),
            'sound_f'         => array('src' => 'Audio/Rus_1/sound_f.mp3'),
            'sound_h'         => array('src' => 'Audio/Rus_1/sound_h.mp3'),
            'sound_ts'        => array('src' => 'Audio/Rus_1/sound_ts.mp3'),
            'sound_cz'        => array('src' => 'Audio/Rus_1/sound_cz.mp3'),
            'sound_sh'        => array('src' => 'Audio/Rus_1/sound_sh.mp3'),
            'sound_sch'       => array('src' => 'Audio/Rus_1/sound_sch.mp3'),
            'sound_hs'        => array('src' => 'Audio/Rus_1/sound_hs.mp3'),
            'sound_y'         => array('src' => 'Audio/Rus_1/sound_y.mp3'),
            'sound_ss'        => array('src' => 'Audio/Rus_1/sound_ss.mp3'),
            'sound_ae'        => array('src' => 'Audio/Rus_1/sound_ae.mp3'),
            'sound_ju'        => array('src' => 'Audio/Rus_1/sound_ju.mp3'),
            'sound_ja'        => array('src' => 'Audio/Rus_1/sound_ja.mp3')
        );

        $day_begin_H = Config::getParam('app.day_begin_H');
        $day_begin_i = Config::getParam('app.day_begin_i');
        $day_begin_timestamp = mktime($day_begin_H, $day_begin_i, 0, date("m")  , date("d"), date("Y")); // Getting unix-timestamp of that day begin
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
            AND action_time >= '".$day_begin_timestamp."'
            ORDER BY action_time DESC
            LIMIT 0, 1
        ";
        $tid = $this->core->getRows($sql);
        if (!empty($tid)) {
            $sql = "
                SELECT *
                FROM workplaces
                WHERE cid = '".$tid[0]['cid']."'
                ORDER BY workplace_number ASC
            ";
            $wid = $this->core->getRows($sql);
            
            $ticket = $this->ticket->ticket($tid[0]['tid']);
            $recent['ticket_prefix'] = $ticket['ticket_prefix'];
            $recent['ticket_number'] = $ticket['ticket_number'];
            $recent['hash'] = hash('crc32', $tid[0]['tid']+$tid[0]['action_time']);
            if (!empty($wid)) {
                $workplace = $this->workplace->workplace_state($wid[0]['wid']);
                switch ($workplace['workplace_type']['type_id']) {
                    case '1':
                        $workplace_type = 'window';
                        break;
                    case '2':
                        $workplace_type = 'cashdesk';
                        break;
                    case '3':
                        $workplace_type = 'cabinet';
                        break;
                    default:
                        $workplace_type = 'window';
                        break;
                }
                $workplace_number = $this->service->int_to_phoneme($workplace['workplace_number']);
                $ticket_number = $this->service->int_to_phoneme($ticket['ticket_number']);
                $ticket_prefix = $this->service->str_split_unicode(mb_strtolower($ticket['ticket_prefix'], 'UTF-8'));
                $ticket_prefix = $this->service->phonemes_to_translit($ticket_prefix);

                $phonemes = array_merge(
                    array('ticket', 'number'),
                    $ticket_prefix, 
                    $ticket_number, 
                    array($workplace_type), 
                    $workplace_number
                );

                $temp = tempnam(sys_get_temp_dir(), 'sound_');
                foreach ($phonemes as $el) {
                    $sound = file_get_contents($audio['sound_'.$el]['src']);
                    file_put_contents($temp, $sound, FILE_APPEND);
                }
                $audio_notification = base64_encode(file_get_contents($temp));
                unlink($temp);
                // return '<audio controls src="'.'data:audio/mpeg;base64,'.$audio_notification.'"/>';
                return $audio_notification;
            }
        }
    }

    public function marquee() {
        $sql = "
            SELECT value FROM settings
            WHERE entity = 'plasma'
            AND parameter = 'marquee'
        ";
        $result = $this->core->getRows($sql);
        $marquee = $this->service->implode_array(' --- ', $result, 'value');
        return $marquee;
    }
}

?>