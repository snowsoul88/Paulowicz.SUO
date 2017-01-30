<?php

$app->post('/queue/admin/user/new', function () use ($app) { // Not documented
	$user = new user();
	$body = $app->request()->getBody();
	$login = (isset($body['login'])) ? $body['login'] : null;
	$password = (isset($body['password'])) ? $body['password'] : null;
	$fname = (isset($body['fname'])) ? $body['fname'] : null;
	$sname = (isset($body['sname'])) ? $body['sname'] : null;
	$mname = (isset($body['mname'])) ? $body['mname'] : null;
	$role = (isset($body['role'])) ? $body['role'] : null;
	$res = $user->create($login, $password, $fname, $sname, $mname, $role);
	echo json_encode($res);

});

$app->post('/queue/admin/user/delete', function () use ($app) { // Not documented
	$user = new user();
	$body = $app->request()->getBody();
	$user_id = (isset($body['user_id'])) ? $body['user_id'] : null;
	$res = $user->delete($user_id);
	echo json_encode($res);

});

$app->get('/queue/admin/users', function () use ($app) {
	$user = new user();
	$users = $user->users();
	echo json_encode($users);

});

$app->get('/queue/admin/workplaces', function () use ($app) {
	$workplace = new workplace();
	$workplaces = $workplace->workplaces();
	echo json_encode($workplaces);

});

$app->post('/queue/admin/user/up', function () use ($app) {
	$user = new user();
	$body = $app->request()->getBody();
	$user_id = (isset($body['user_id'])) ? $body['user_id'] : null;
	$products = (isset($body['products'])) ? $body['products'] : array();
	$up = $user->user_product_binding($user_id, $products);
	echo json_encode($up);
});

$app->post('/queue/admin/user/us', function () use ($app) {
	$user = new user();
	$body = $app->request()->getBody();
	$user_id = (isset($body['user_id'])) ? $body['user_id'] : null;
	$situations = (isset($body['situations'])) ? $body['situations'] : array();
	$us = $user->user_situations_binding($user_id, $situations);
	echo json_encode($us);
});

$app->post('/queue/admin/client/cp', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$cid = (isset($body['cid'])) ? $body['cid'] : null;
	$products = (isset($body['products'])) ? $body['products'] : array();
	$cp = $client->client_product_binding($cid, $products);
	echo json_encode($cp);
});

$app->post('/queue/admin/client/cs', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$cid = (isset($body['cid'])) ? $body['cid'] : null;
	$situations = (isset($body['situations'])) ? $body['situations'] : array();
	$cs = $client->client_situations_binding($cid, $situations);
	echo json_encode($cs);
});

$app->post('/queue/admin/workplace/new', function () use ($app) {
	$workplace = new workplace();
	$body = $app->request()->getBody();
	$workplace_number = (isset($body['workplace_number'])) ? $body['workplace_number'] : null;
	$workplace_name = (isset($body['workplace_name'])) ? $body['workplace_name'] : null;
	$workplace_type = (isset($body['workplace_type'])) ? $body['workplace_type'] : null;
	$cid = (isset($body['cid'])) ? $body['cid'] : null;
	$new_workplace = $workplace->create($workplace_number, $workplace_name, $workplace_type, $cid);
	echo json_encode($new_workplace);
});

$app->post('/queue/admin/workplace/delete', function () use ($app) {
	$workplace = new workplace();
	$body = $app->request()->getBody();
	$workplace_id = (isset($body['workplace_id'])) ? $body['workplace_id'] : null;
	$delete = $workplace->delete($workplace_id);
	echo json_encode($delete);
});

$app->post('/queue/admin/client/new', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$client_id = (isset($body['client_id'])) ? $body['client_id'] : null;
	$role = (isset($body['role'])) ? $body['role'] : null;
	$REMOTE_ADDR = (isset($body['REMOTE_ADDR'])) ? $body['REMOTE_ADDR'] : null;
	$HTTP_X_FORWARDED_FOR = (isset($body['HTTP_X_FORWARDED_FOR'])) ? $body['HTTP_X_FORWARDED_FOR'] : null;
	$new_client = $client->create($client_id, $role, $REMOTE_ADDR, $HTTP_X_FORWARDED_FOR);
	echo json_encode($new_client);
});

$app->post('/queue/admin/client/delete', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$id = (isset($body['id'])) ? $body['id'] : null;
	$res = $client->delete($id);
	echo json_encode($res);
});

$app->post('/queue/admin/shedule/type/new', function () use ($app) {
	$shedule = new shedule();
	$body = $app->request()->getBody();
	$type_name = (isset($body['type_name'])) ? $body['type_name'] : null;
	$type_description = (isset($body['type_description'])) ? $body['type_description'] : null;
	$type_operable = (isset($body['type_operable'])) ? $body['type_operable'] : null;
	$type = $shedule->create_type($type_name, $type_description, $type_operable);
	echo json_encode($type);
});

$app->post('/queue/admin/shedule/type/delete', function () use ($app) {
	$shedule = new shedule();
	$body = $app->request()->getBody();
	$type_id = (isset($body['type_id'])) ? $body['type_id'] : null;
	$res = $shedule->delete_type($type_id);
	echo json_encode($res);
});

$app->get('/queue/admin/settings/entities', function () use ($app) {
	$client = new client();
	$settings_entities_get = $client->settings_entities_get();
	echo json_encode($settings_entities_get);

});

$app->post('/queue/admin/settings/entities/set', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$entity = (isset($body['entity'])) ? $body['entity'] : null;
	$description = (isset($body['description'])) ? $body['description'] : null;
	$settings_entities_set = $client->settings_entities_set($entity, $description);
	echo json_encode($settings_entities_set);

});

$app->post('/queue/admin/settings/entities/unset', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$entity = (isset($body['entity'])) ? $body['entity'] : null;
	$settings_entities_unset = $client->settings_entities_unset($entity);
	echo json_encode($settings_entities_unset);

});

$app->get('/queue/admin/settings/parameters', function () use ($app) {
	$client = new client();
	$entity = (isset($_GET['entity'])) ? $_GET['entity'] : null;
	$settings_parameters_get = $client->settings_parameters_get($entity);
	echo json_encode($settings_parameters_get);

});

$app->post('/queue/admin/settings/parameters/set', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$entity = (isset($body['entity'])) ? $body['entity'] : null;
	$description = (isset($body['description'])) ? $body['description'] : null;
	$parameter = (isset($body['parameter'])) ? $body['parameter'] : null;
	$value = (isset($body['value'])) ? $body['value'] : null;
	$settings_parameters_set = $client->settings_parameters_set($entity, $description, $parameter, $value);
	echo json_encode($settings_parameters_set);

});

$app->post('/queue/admin/settings/parameters/unset', function () use ($app) {
	$client = new client();
	$body = $app->request()->getBody();
	$id = (isset($body['id'])) ? $body['id'] : null;
	$settings_parameters_unset = $client->settings_parameters_unset($id);
	echo json_encode($settings_parameters_unset);

});

$app->post('/queue/admin/settings/products/unset', function () use ($app) {
	$product = new product();
	$body = $app->request()->getBody();
	$id = (isset($body['id'])) ? $body['id'] : null;
	$settings_products_unset = $product->settings_products_unset($id);
	echo json_encode($settings_products_unset);

});

$app->post('/queue/admin/settings/products/add', function () use ($app) {
	$product = new product();
	$body = $app->request()->getBody();
	$action_type = (isset($body['action_type'])) ? $body['action_type'] : null;
	$product_recordlimit = (isset($body['product_recordlimit'])) ? $body['product_recordlimit'] : null;
	$product_description = (isset($body['product_description'])) ? $body['product_description'] : null;
	$product_timereglament = (isset($body['product_timereglament'])) ? $body['product_timereglament'] : null;
	$product_name = (isset($body['product_name'])) ? $body['product_name'] : null;
	$situations = (isset($body['situations'])) ? $body['situations'] : array();
	$res = $product->settings_products_add($action_type, $product_recordlimit, $product_description, $product_timereglament, $product_name, $situations);
	echo json_encode($res);
});

$app->post('/queue/admin/settings/situation/add', function () use ($app) {
	$product = new product();
	$body = $app->request()->getBody();
	$situation_name = (isset($body['situation_name'])) ? $body['situation_name'] : null;
	$situation_prefix = (isset($body['situation_prefix'])) ? $body['situation_prefix'] : null;
	$kioskmenu_expanded = (isset($body['kioskmenu_expanded'])) ? $body['kioskmenu_expanded'] : 0;
	$products = (isset($body['products'])) ? $body['products'] : array();
	$res = $product->settings_situation_add($situation_name, $situation_prefix, $kioskmenu_expanded, $products);
	echo json_encode($res);
});

// $app->post('/queue/admin/login/:login/:password/:shedule', function ($login, $password, $shedule) use ($app) {
// 	$user = new user();
// 	$token = $user->login($login, $password, $shedule);
// 	echo $token;
// });

$app->get('/queue/admin/tickets', function () use ($app) {
	$ticket = new ticket();
	$tickets = $ticket->tickets($include_closed=true);
	echo json_encode($tickets);

});

$app->get('/queue/admin/booking', function () use ($app) {
	$booking = new booking();
	$data = $booking->booking();
	echo json_encode($data);

});

$app->get('/queue/admin/tickets/:ticketId', function ($ticketId) use ($app) {
	$ticket = new ticket();
	$data = $ticket->ticket_full($ticketId);
	echo json_encode($data);
});

$app->post('/queue/admin/tickets/new', function () use ($app) {
	$ticket = new ticket();
	$body = $app->request()->getBody();
	$products = (isset($body['product_id'])) ? $body['product_id'] : array();
	$ticket_data = $ticket->create($products);
	echo json_encode($ticket_data);
});

$app->post('/queue/admin/tickets/:ticketId/addProduct/:productId', function ($ticketId, $productId) use ($app) {
	$priority = (isset($body['priority'])) ? $body['priority'] : 0;
	$ticket = new ticket();
	$ticket_data = $ticket->addProduct($ticketId, $productId, $priority);
	echo json_encode($ticket_data);

});

$app->post('/queue/admin/tickets/:ticketId/:ticketProductId/cancel', function ($ticketId, $ticketProductId) use ($app) {
	$ticket = new ticket();
	$ticket_data = $ticket->delProduct($ticketId, $ticketProductId);
	echo json_encode($ticket_data);
});

$app->post('/queue/admin/tickets/:ticketId/:ticketProductId/setPriority', function ($ticketId, $ticketProductId) use ($app) {
	$priority = (isset($body['priority'])) ? $body['priority'] : 0;
	$ticket = new ticket();
	$ticket_data = $ticket->setPriority($ticketId, $ticketProductId, $priority);
	echo json_encode($ticket_data);
});

$app->post('/queue/admin/redirect/users/:productId', function ($productId) use ($app) {
	$product = new product();
	$u_p = $product->get_up_bindings($productId);
	echo json_encode($u_p);
});

$app->post('/queue/admin/redirect/workPlaces/:productId', function ($productId) use ($app) {
	$product = new product();
	$c_p = $product->get_cp_bindings($productId);
	echo json_encode($c_p);
});

$app->post('/queue/admin/redirect/:productId', function ($productId) use ($app) {
	$operation = new operation();
	$userId = (isset($body['userId'])) ? $body['userId'] : '875BB2DB-0D6D-B86A-9007-5B082FF4B53D';
	$workPlaceId = (isset($body['workPlaceId'])) ? $body['workPlaceId'] : null;
	$return = (isset($body['return'])) ? $body['return'] : false;
	$priority = (isset($body['priority'])) ? $body['priority'] : null;
	$redirect = $operation->redirect($productId, $userId, $workPlaceId, $return, $priority);
	echo json_encode($redirect);
});

$app->get('/queue/statistics/timing', function () use ($app) {
	$statistics = new statistics();
	$body = $app->request()->getBody();
	$timing = $statistics->timing();
	echo json_encode($timing);
});

$app->get('/queue/statistics/counting', function () use ($app) {
	$statistics = new statistics();
	$body = $app->request()->getBody();
	$counting = $statistics->counting();
	echo json_encode($counting);
});

$app->get('/queue/statistics/visitors', function () use ($app) {
	$statistics = new statistics();
	$body = $app->request()->getBody();
	$visitors = $statistics->visitors();
	echo json_encode($visitors);
});

$app->get('/queue/statistics/categories', function () use ($app) {
	$statistics = new statistics();
	$body = $app->request()->getBody();
	$categories = $statistics->categories();
	echo json_encode($categories);
});

$app->post('/queue/admin/sync', function () use ($app) { // Sync categories with Re:Doc
	$redoc = new redoc();
	$redoc->categories();
	$redoc->situations();
	$services = $redoc->services();
	echo json_encode($services);
});

$app->post('/queue/admin/sync/categories', function () use ($app) { // Sync categories with Re:Doc
	$redoc = new redoc();
	$categories = $redoc->categories();
	echo json_encode($categories);
});

$app->post('/queue/admin/sync/situations', function () use ($app) { // Sync situations with Re:Doc
	$redoc = new redoc();
	$situations = $redoc->situations();
	echo json_encode($situations);
});

?>