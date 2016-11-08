<?php
// Routes

$app->get('/sender', \FLAPI\SenderController::class . ':getSenderliste');
