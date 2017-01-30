<?php

$app->get('/notifications/queue', function () use ($app) {
	$notifications = new notifications();
	$queue_notifications = $notifications->queue_notifications();
	echo json_encode($queue_notifications);
});

$app->get('/notifications/queue/recent', function () use ($app) {
	$notifications = new notifications();
	$queue_recent_notification = $notifications->queue_recent_notification();
	echo json_encode($queue_recent_notification);
});

$app->get('/notifications/queue/audio', function () use ($app) {
	$notifications = new notifications();
	$queue_audio_notification = $notifications->queue_audio_notification();
	echo $queue_audio_notification;
});

$app->get('/notifications/queue/marquee', function () use ($app) {
	$notifications = new notifications();
	$marquee = $notifications->marquee();
	echo $marquee;
});

?>