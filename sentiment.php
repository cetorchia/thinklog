<?php

require_once("def.php");
require_once(DOC_ROOT . "/classes/DBConnectionService.php");
require_once(DOC_ROOT . "/classes/SentimentService.php");

$connService = new DBConnectionService(null);
$connService->connect();

$sentService = new SentimentService(null);
$sentService->read();
$sentService->insert();

$connService->close();
