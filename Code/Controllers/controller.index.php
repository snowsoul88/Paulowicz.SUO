<?php

$app->get('/', function () use ($app) {
	$app->render('ionic/index.php', array());
});

?>