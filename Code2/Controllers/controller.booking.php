<?php

$app->get('/booking/menu', function () use ($app) {
	$booking = new booking();
	$situationId = (isset($_GET['situationId'])) ? $_GET['situationId'] : null;
	$menu = $booking->menu($situationId);
	echo json_encode($menu);
});

$app->get('/booking/product/:productId', function ($productId) use ($app) {
	$product = new product();
	$product = $product->product($productId);
	echo json_encode($product);
});

$app->get('/booking/schedule/days', function () use ($app) {
	$booking = new booking();
	$products = array(
		array('product_id'=>'86181291-C240-F823-D67C-BCC6CBD15121'),
		array('product_id'=>'86181291-C240-F823-D67C-BCC6CBD15122')
	);
	$days = $booking->days($products);
	echo json_encode($days);
});

$app->get('/booking/schedule/days/:date', function ($date) use ($app) {
	$booking = new booking();
	$products = array(
		array('product_id'=>'86181291-C240-F823-D67C-BCC6CBD15121'),
		array('product_id'=>'86181291-C240-F823-D67C-BCC6CBD15122')
	);
	$day = $booking->day($products, $date);
	echo json_encode($day);
});

$app->post('/booking/schedule', function () use ($app) {
	$booking = new booking();
	$body = $app->request()->getBody();
	$products = (isset($body['product_id'])) ? $body['product_id'] : array();
	$situation_id = (isset($body['situation_id'])) ? $body['situation_id'] : null;
	$schedule = $booking->schedule($products, $situation_id);
	echo json_encode($schedule);
});

$app->post('/booking/tickets/new', function () use ($app) {
	$ticket = new ticket();
	$body = $app->request()->getBody();
	$products = (isset($body['product_id'])) ? $body['product_id'] : array();
	$situation_id = (isset($body['situation_id'])) ? $body['situation_id'] : null;
	$visitor = (isset($body['visitor'])) ? $body['visitor'] : null;
	$not_before = (isset($body['not_before'])) ? strtotime($body['not_before']) : 0;
	$ticket_data = $ticket->create_kiosk($products, $situation_id, $visitor, $not_before);
	echo $ticket_data;
});

$app->post('/booking/tickets/submit', function () use ($app) {
	$ticket = new ticket();
	$body = $app->request()->getBody();
	$pincode = (isset($body['pincode'])) ? $body['pincode'] : null;
	$ticket_data = $ticket->submit_kiosk($pincode);
	echo $ticket_data;
});

$app->post('/booking/tickets/check', function () use ($app) {
	$ticket = new ticket();
	$body = $app->request()->getBody();
	$pincode = (isset($body['pincode'])) ? $body['pincode'] : null;
	$result = $ticket->ticket_check($pincode);
	echo json_encode($result);
});

$app->post('/booking/login/:userIdentity', function ($userIdentity) use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

$app->get('/booking/:dateTime', function ($dateTime) use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

$app->get('/booking/tickets/:ticketId/confirm', function ($ticketId) use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

$app->get('/booking/tickets/:ticketId/cancel', function ($ticketId) use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

$app->get('/booking/tickets', function () use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

$app->get('/booking/tickets/:ticketId', function ($ticketId) use ($app) {
	
	// $smev = new smev();
	// $cl = $smev->getClients();
	
	// $app->render('view.smev.php', array(
	// 	'clients'	=>$cl
	// 	)
	// );
});

?>