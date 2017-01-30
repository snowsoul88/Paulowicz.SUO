<?
class Service {
    
    private $core;
    private $jwt;

    function __construct() {
        $this->core = Core::getInstance();
        $this->jwt = new JWT();
    }

    public function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid =  substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);
            return $uuid;
        }
    }

    public function implode_array($glue, $pieces, $key) {
        $tmp = array();
        foreach ($pieces as $piece) {
            $tmp[] = $piece[$key];
        }
        $tmp = array_unique($tmp);
        $str = implode($glue, $tmp);
        return $str;
    }

    public function date($m) {
        $F = array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
        return $F[$m-1];
    }

    public function int_to_phoneme($int) {
        $splitted = str_split($int);
        $phoneme = array();
        if ($int>0 && $int<20) {
            $phoneme[] = (string)$int;
        }
        if ($int>19 && $int<100) {
            $phoneme[] = $splitted[0].'0';
            $phoneme[] = $splitted[1];
        }
        if ($int>99 && $int<1000) {
            $phoneme[] = $splitted[0].'00';
            if ($splitted[1]>1) {
                $phoneme[] = $splitted[1].'0';
                $phoneme[] = $splitted[2];
            } else {
                $phoneme[] = $splitted[1].$splitted[2];
            }
        }
        return $phoneme;
    }

    public function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function phonemes_to_translit($phonemes) {
        $ret = array();
        $translit = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'jo',
            'ж' => 'j',
            'з' => 'z',
            'и' => 'i',
            'й' => 'iy',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'cz',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => 'hs',
            'ы' => 'y',
            'ь' => 'ss',
            'э' => 'ae',
            'ю' => 'ju',
            'я' => 'ja'
        );
        foreach ($phonemes as $p) {
            $ret[] = $translit[$p];
        }
        return $ret;
    }

    public function error_log($error) {
        if (isset($GLOBALS['Bearer'])) {
            $token = (array)self::jwt_decode($GLOBALS['Bearer']);
            $uid = $token['uid'];
        } else {
            $uid = null;
        }
        $datetime = time() + (Config::getParam('app.utc_timezone')*60*60); // Time
        $sql = "
            INSERT INTO error_log (REMOTE_ADDR, uid, error_description, datetime)
            VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$uid."', '".$error."', '".$datetime."')
        ";
        $this->core->execQuery($sql);
    }

    public function jwt_encode($uid, $rol, $cid = null) {
        $key = Config::getParam('app.token_key');
        $iss = Config::getParam('app.host');
        $now = time() + (Config::getParam('app.utc_timezone')*60*60);
        $token_lifespan = Config::getParam('app.token_lifespan'); // Get token lifespan from config
        $lifespan = $now+$token_lifespan;
        $jwtoken = array(
            "iss" => $iss,
            "iat" => $now,
            "exp" => $lifespan,
            "jti" => self::guid(),
            "uid" => $uid,
            "cid" => $cid,
            "rol" => $rol
        );
        $jwt = $this->jwt->encode($jwtoken, $key);
        return $jwt;
    }

    public function jwt_decode($jwt) {
        $key = Config::getParam('app.token_key');
        $decoded = $this->jwt->decode($jwt, $key);
        return $decoded;
    }

    public function secs_to_h($secs) {
        $units = array(
            "нед."      => 7*24*3600,
            "дн."       =>   24*3600,
            "час."      =>      3600,
            "мин."      =>        60,
            "сек."      =>         1,
        );
        // specifically handle zero
        if ( $secs == 0 ) return "0 секунд";
        $s = "";
        foreach ( $units as $name => $divisor ) {
            if ( $quot = intval($secs / $divisor) ) {
                $s .= "$quot $name";
                $s .= (abs($quot) > 1 ? "" : "") . ", ";
                $secs -= $quot * $divisor;
            }
        }
        return substr($s, 0, -2);
    }
}

?>