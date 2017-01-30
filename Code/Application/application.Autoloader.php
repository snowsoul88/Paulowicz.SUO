<?php

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$app->contentType('text/html; charset=utf-8');
$app->add(new \Slim\Middleware\ContentTypes());
/*
* Хак для IIS при работе с PHP <= 5.3
* Замена функции apache_request_headers()
*/
if( !function_exists('apache_request_headers') ) {
	function apache_request_headers() {
	  	$arh = array();
	  	$rx_http = '/\AHTTP_/';
	  	foreach($_SERVER as $key => $val) {
	    	if( preg_match($rx_http, $key) ) {
	      		$arh_key = preg_replace($rx_http, '', $key);
	      		$rx_matches = array();
	      		// do some nasty string manipulations to restore the original letter case
	      		// this should work in most cases
	      		$rx_matches = explode('_', $arh_key);
	      		if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
	        		foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
	        		$arh_key = implode('-', $rx_matches);
	      		}
	      		$arh[$arh_key] = $val;
	    	}
	  	}
	  	return( $arh );
	}
}
/*
* Авторизация по наличию токена в заголовке для ресурсов, не являющихся публичными
* Формат OAuth 2.0
*/
$app->hook('slim.before.dispatch', function() use ($app) {
	$service = new service();
	$core = Core::getInstance();
	$public_routes = Config::getParam('app.public_routes');
	$current_route = $app->request->getPathInfo();
	$public_route = false;
	foreach ($public_routes as $route) {
		$route_pattern = '@^'.$route.'$@';
		if (preg_match($route_pattern, $current_route) === 1) {
			$public_route = true;
		}
	}
	if ($public_route === false) {
        $response = $app->response();
        $request_headers = apache_request_headers();
        if (isset($request_headers['Authorization'])) {
	        $Authorization = $request_headers['Authorization'];
	        if ($Authorization == null) {
	            $app->halt(401);
	        } else {
		        // validate the token
		        $token = str_replace('Bearer ', '', $Authorization);
		        $GLOBALS['Bearer'] = $token;
		        $decoded_token = null;
		        $sql = "
		            SELECT id, role, disabled
		            FROM users
		            WHERE token='".$token."'
		        ";
		        $user = $core->getRows($sql); // Get user id
		        if (!empty($user)) {
		            if ($user[0]['disabled'] == 0) {
		            	$permission = false;
		            	$private_routes = Config::getParam('app.private_routes');
						foreach ($private_routes as $route) {
							$route_pattern = '@^'.$route['route'].'$@';
							if (preg_match($route_pattern, $current_route) === 1) {
								if (in_array($user[0]['role'], $route['roles'])) {
									$permission = true;
								}
							}
						}
		            	if ($permission) {
			                try {
					            $decoded_token = $service->jwt_decode($token);
					        } catch (ExpiredException $ex) {
					            $app->halt(401);
					        } catch (SignatureInvalidException $ex) {
					            $app->halt(401);
					        }
					    } else {
					    	$app->halt(401);
					    }
		            } else {
		            	$app->halt(401);
		            }
		        } else {
		        	$app->halt(401);
		        }
		    }
	    } else {
	    	$app->halt(401);
	    }
	}
});

$ApplicationPath = $app->request()->getPathInfo();
$ApplicationPathArr = explode('/', $ApplicationPath);

if ( count($ApplicationPathArr) > 4 ) {
    $ApplicationPath = '/'.$ApplicationPathArr[1].'/'.$ApplicationPathArr[2].'/'.$ApplicationPathArr[3];
}
$ControllerName = Controllers::getCurrentControllerName();
$ControllerPath = 'Controllers/controller.'.$ControllerName.'.php';

if(file_exists($ControllerPath)) {
    require $ControllerPath;
}

$view = $app->view();

$app->run();