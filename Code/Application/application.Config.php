<?php

class Config {

    static $confArray;

    public static function getParam($param) {
        return self::$confArray[$param];
    }

    public static function setParam($param, $value) {
        self::$confArray[$param] = $value;
    }
}

// DB Config
Config::setParam('db.host', 	'localhost');
Config::setParam('db.user', 	'suo_test');
Config::setParam('db.password', 'd1gysuud');
Config::setParam('db.base', 	'suo_db');
Config::setParam('db.encode', 	'utf8');
// App Config
Config::setParam('app.host', 'http://localhost');
Config::setParam('app.token_key', '70ECDE14-960C-B828-3B11-DF2F28CE9360');
Config::setParam('app.token_lifespan', 86400*365);
Config::setParam('app.day_begin_H', '08');
Config::setParam('app.day_begin_i', '00');
Config::setParam('app.day_end_H', '17');
Config::setParam('app.day_end_i', '00');
Config::setParam('app.fbd_reserve_days', 0);
Config::setParam('app.max_reserve_days', 5);
Config::setParam('app.booknote', 'Внимание! Вашу бронь необходимо подтвердить на терминале за 5 минут до назначенного времени.');
Config::setParam('app.utc_timezone', 3);
Config::setParam('app.closed_workplace_state', 'Закрыто');
Config::setParam('app.opened_workplace_state', 'Свободно');
Config::setParam('app.public_routes', array(
	'/', 
	'/notifications/queue', 
	'/notifications/queue/last', 
	'/config/products', 
	'/operation/identify', 
	'/operation/identify/.+/.+/.+/.+',
	'/config/clients',
	'/config/shedule',
	'/booking/shedule/.+',
	'/notifications/queue/audio'
	)
);
// Доступ по ролям
Config::setParam('app.private_routes', array(
	array(
		'route'	=> '/operation/logout',
		'roles' => array(0, 1, 2, 3)
	),array(
		'route'	=> '/operation/.+',
		'roles' => array(1)
	),
	array(
		'route'	=> '/config/.+',
		'roles' => array(0, 1, 2, 3)
	),
	array(
		'route'	=> '/queue/admin/.+',
		'roles' => array(0)
	),
	array(
		'route'	=> '/booking/.+',
		'roles' => array(0, 2)
	),
	array(
		'route'	=> '/queue/statistics/.+',
		'roles' => array(0)
	),
	array(
		'route'	=> '/notifications/queue/.+',
		'roles' => array(0, 1, 2, 3)
	),
));
// Настройка меню приложения
Config::setParam('app.ionic_menu', array(
	'0' => array(
		array(
			'text'				=> 'Администратор зала',
			'iconClass' 		=> 'icon ion-ios7-speedometer-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Услуги',
			'iconClass' 		=> 'icon ion-ios7-glasses-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/services',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Ситуации',
			'iconClass' 		=> 'icon ion-ios7-keypad-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/situations',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Статистика',
			'iconClass' 		=> 'icon ion-ios7-pie-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/statistics',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Пользователи',
			'iconClass' 		=> 'icon ion-ios7-people-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/users',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Рабочие места',
			'iconClass' 		=> 'icon ion-ios7-filing-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/workplaces',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Клиентские аккаунты',
			'iconClass' 		=> 'icon ion-ios7-barcode-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/clients',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Расписание',
			'iconClass' 		=> 'icon ion-ios7-calendar-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/shedule',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Установки',
			'iconClass' 		=> 'icon ion-ios7-cog-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'admin/settings',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Индикатор очереди',
			'iconClass' 		=> 'icon ion-ios7-pulse',
			'rightIconClass'	=> 'icon ion-ios7-upload-outline',
			'link'				=> 'plasma',
			'target'			=> '_blank',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Инфокиоск',
			'iconClass' 		=> 'icon ion-ios7-monitor-outline',
			'rightIconClass'	=> 'icon ion-ios7-upload-outline',
			'link'				=> 'kiosk/booking',
			'target'			=> '_blank',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Завершение работы',
			'iconClass' 		=> 'icon ion-ios7-locked-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'logout',
			'target'			=> 'self',
			'directive'			=> 'suo-logout'
		)
	),
	'1' => array(
		array(
			'text'				=> 'Пульт оператора',
			'iconClass' 		=> 'icon ion-ios7-barcode-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'operator',
			'target'			=> 'self',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Индикатор оператора',
			'iconClass' 		=> 'icon ion-ios7-albums-outline',
			'rightIconClass'	=> 'icon ion-ios7-upload-outline',
			'link'				=> 'led',
			'target'			=> '_blank',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Индикатор очереди',
			'iconClass' 		=> 'icon ion-ios7-pulse',
			'rightIconClass'	=> 'icon ion-ios7-upload-outline',
			'link'				=> 'plasma',
			'target'			=> '_blank',
			'directive'			=> ''
		),
		array(
			'text'				=> 'Завершение работы',
			'iconClass' 		=> 'icon ion-ios7-locked-outline',
			'rightIconClass'	=> 'icon',
			'link'				=> 'logout',
			'target'			=> 'self',
			'directive'			=> 'suo-logout'
		)
	),
	'2' => array(),
	'3' => array()
));
// Re:Doc Config
Config::setParam('redoc.uri', 'http://test.redoc.ru:29929/');
Config::setParam('redoc.method.ServiceCategory.List', 'WebInterfaceModule/Categories/List');
Config::setParam('redoc.method.ServiceInfo.Count', 'WebInterfaceModule/Services/Count');
Config::setParam('redoc.method.ServiceInfo.List', 'WebInterfaceModule/Services/List');
Config::setParam('redoc.method.LifeSituation.List', 'WebInterfaceModule/Situations/List');