<?php

$app->post('/operation/identify', function () use ($app) {
	$user = new user();
	$body = $app->request()->getBody();
	$clientId = (isset($body['client_id'])) ? $body['client_id'] : null;
	$login = (isset($body['login'])) ? $body['login'] : null;
	$password = (isset($body['password'])) ? $body['password'] : null;
	$shedule = (isset($body['shedule'])) ? $body['shedule'] : null;
	$token = $user->identify($clientId, $login, $password, $shedule);
	echo json_encode($token);
});

$app->get('/operation/state/:clientId', function ($clientId) use ($app) {
	$user = new user();
	$state = $user->state($clientId);
	echo json_encode($state);
});

// $app->post('/operation/login/:login/:password/:shedule', function ($login, $password, $shedule) use ($app) {
// 	$user = new user();
// 	$token = $user->client_login($login, $password, $shedule);
// 	echo $token;
// });

$app->post('/operation/logout', function () use ($app) {
	$user = new user();
	$body = $app->request()->getBody();
	$shedule = (isset($body['shedule'])) ? $body['shedule'] : null;
	$res = $user->client_logout($shedule);
	echo $res;
});

$app->get('/operation/queue/count', function () use ($app) {
	$operation = new operation();
	$queue_count = $operation->queue_count();
	echo $queue_count;
});

$app->get('/operation/queue', function () use ($app) {
	$operation = new operation();
	$queue_operation = $operation->queue_operation();
	echo json_encode($queue_operation);
});

$app->get('/operation/personal/count', function () use ($app) {
	$operation = new operation();
	$personal_count = $operation->personal_count();
	echo $personal_count;
});

$app->get('/operation/personal', function () use ($app) {
	$operation = new operation();
	$personal_queue = $operation->personal_queue();
	echo json_encode($personal_queue);
});

$app->post('/operation/break/begin', function () use ($app) {

});

$app->post('/operation/break/end', function () use ($app) {

});

$app->post('/operation/invite', function () use ($app) {
	$operation = new operation();
	$body = $app->request()->getBody();
	$product_id = (isset($body['product_id'])) ? $body['product_id'] : null;
	$invite = $operation->invite($product_id);
	echo json_encode($invite);
});

$app->post('/operation/invite/:product_id', function ($product_id) use ($app) {
	$operation = new operation();
	$body = $app->request()->getBody();
	$invite = $operation->invite($product_id);
	echo json_encode($invite);
});

$app->post('/operation/start', function () use ($app) {
	$operation = new operation();
	$start = $operation->start();
	echo json_encode($start);
});

$app->post('/operation/reject', function () use ($app) {
	$operation = new operation();
	$reject = $operation->reject();
	echo json_encode($reject);
});

$app->post('/operation/complete', function () use ($app) {
	$operation = new operation();
	$complete = $operation->complete();
	echo json_encode($complete);
});

$app->post('/operation/callagain', function () use ($app) {
	$operation = new operation();
	$call_again = $operation->call_again();
	echo json_encode($call_again);
});

$app->post('/operation/hold', function () use ($app) {
	$operation = new operation();
	$hold = $operation->hold();
	echo json_encode($hold);
});

$app->get('/operation/menu', function () use ($app) {

});

$app->get('/operation/recent', function () use ($app) {
	$operation = new operation();
	$recent = $operation->recent();
	echo json_encode($recent);
});

$app->post('/operation/redirect/users/:productId', function ($productId) use ($app) {
	$product = new product();
	$u_p = $product->get_up_bindings($productId);
	echo json_encode($u_p);
});

$app->post('/operation/redirect/workPlaces/:productId', function ($productId) use ($app) {
	$product = new product();
	$c_p = $product->get_cp_bindings($productId);
	echo json_encode($c_p);
});

$app->post('/operation/redirect', function () use ($app) {
	$operation = new operation();
	$body = $app->request()->getBody();
	$productId = (isset($body['productId'])) ? $body['productId'] : null;
	$workPlaceId = (isset($body['workPlaceId'])) ? $body['workPlaceId'] : null;
	$userId = (isset($body['userId'])) ? $body['userId'] : null;
	$returnAfter = (isset($body['returnAfter'])) ? $body['returnAfter'] : false;
	$redirect = $operation->redirect($productId, $userId, $workPlaceId, $returnAfter);
	echo json_encode($redirect);
});

?>