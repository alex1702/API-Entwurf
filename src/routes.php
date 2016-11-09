<?php
// Routes

$app->get('/sender', \FLAPI\SenderController::class . ':getSenderliste');
$app->get('/sender/{abbr}', \FLAPI\SendungController::class . ':getSenderAllData')->setName('senderFull');
$app->get('/sender/{abbr}/{timeframe}', \FLAPI\SendungController::class . ':getSenderSpecialData');
