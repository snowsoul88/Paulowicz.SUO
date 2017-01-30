<?php

$app->get('/config/products', function () use ($app) {
	$product = new product();
	$products = $product->products();
	echo json_encode($products);
});

$app->get('/config/products/user/:uid', function ($uid) use ($app) {
	$product = new product();
	$products = $product->user_binded_products($uid);
	echo json_encode($products);
});

$app->get('/config/situations/user/:uid', function ($uid) use ($app) {
	$user = new user();
	$situations = $user->user_binded_situations($uid);
	echo json_encode($situations);
});

$app->get('/config/products/client/:cid', function ($cid) use ($app) {
	$product = new product();
	$products = $product->client_binded_products($cid);
	echo json_encode($products);
});

$app->get('/config/situations/client/:cid', function ($cid) use ($app) {
	$client = new client();
	$situations = $client->client_binded_situations($cid);
	echo json_encode($situations);
});

$app->get('/config/situations', function () use ($app) {
	$product = new product();
	$situations = $product->situations();
	echo json_encode($situations);
});

$app->get('/config/shedule', function () use ($app) {
	$shedule = new shedule();
	$operable = (isset($_GET['operable'])) ? $_GET['operable'] : null;
	$shedule = $shedule->shedule_types($operable);
	echo json_encode($shedule);
});

$app->get('/config/clients', function () use ($app) {
	$client = new client();
	$clients = $client->clients();
	echo json_encode($clients);
});

$app->get('/config/shedule/:type_id', function ($type_id) use ($app) {
	$shedule = new shedule();
	$shedule = $shedule->shedule_type($type_id);
	echo json_encode($shedule);
});

$app->get('/config/workplace', function () use ($app) {
	$client = new client();
	$workplace = $client->workplace();
	echo json_encode($workplace);
});

$app->get('/config/workplaces', function () use ($app) {
	$workplace = new workplace();
	$workplaces = $workplace->workplaces();
	echo json_encode($workplaces);
});

$app->get('/config/workplaces/types', function () use ($app) {
	$workplace = new workplace();
	$types = $workplace->types();
	echo json_encode($types);
});

$app->get('/config/ionicmenu', function () use ($app) {
	$client = new client();
	$ionicmenu = $client->ionicmenu();
	echo json_encode($ionicmenu);
});

$app->get('/config/menus/:menuId', function ($menuId) use ($app) {
	
});

?>