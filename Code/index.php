<?
	// session_cache_limiter(false);
	// session_start();
	define('ROOT_DIR', dirname(__FILE__));

/*
 ** Подключаем Slim-фреймворк и ядро приложения
 */
	require 'Slim/Slim.php';
	require 'Application/application.Config.php';
	require 'Application/application.Actions.php';
	require 'Slim/Middleware/JWT.php';
	require 'Application/application.Core.php';
	require 'Application/application.Models.php';
	require 'Application/application.Controllers.php';
	require 'Application/application.Autoloader.php';
	// phpinfo();
?>