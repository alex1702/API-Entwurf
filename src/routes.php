<?php
// Routes

$app->get('/sender', \FLAPI\SenderController::class . ':getSenderliste');
$app->get('/sender/{abbr}', \FLAPI\SenderController::class . ':getSenderAllData')->setName('senderFull');
