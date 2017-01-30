<?php

$app->get('/survey/:clientId/state', function ($clientId) use ($app) {
	// $smev = new smev();
	// $cl = $smev->getClient($id);

	// $app->render('view.smev.data.php', array(
	// 	'cl'	=>$cl
	// 	)
	// );
});

$app->post('/survey/:clientId/begin', function ($clientId) use ($app) {
	// $smev = new smev();
	// $cl = $smev->getClient($id);

	// $app->render('view.smev.data.php', array(
	// 	'cl'	=>$cl
	// 	)
	// );
});

$app->post('/survey/:clientId/end', function ($clientId) use ($app) {
	// $smev = new smev();
	// $cl = $smev->getClient($id);

	// $app->render('view.smev.data.php', array(
	// 	'cl'	=>$cl
	// 	)
	// );
});

$app->post('/survey/:clientId/answer', function ($clientId) use ($app) {
	// $smev = new smev();
	// $cl = $smev->getClient($id);

	// $app->render('view.smev.data.php', array(
	// 	'cl'	=>$cl
	// 	)
	// );
});

?>