<?php

$index_php_time = date("U");

require_once("def.php");
require_once("error.php");
require_once(DOC_ROOT . "/classes/AppServices.php");
require_once(DOC_ROOT . "/classes/DBConnectionService.php");
require_once(DOC_ROOT . "/classes/LoginRequest.php");
require_once(DOC_ROOT . "/classes/DocumentRequest.php");
require_once(DOC_ROOT . "/classes/LogoutRequest.php");
require_once(DOC_ROOT . "/classes/LoginService.php");
require_once(DOC_ROOT . "/classes/UploadRequest.php");
require_once(DOC_ROOT . "/classes/ServerRequest.php");

/*
 * Set up application context
 */

$services = new AppServices();
$timerService = $services->timerService;
$loginService = $services->loginService;
$dbConnectionService = new DBConnectionService($services);

$serverRequest = new ServerRequest($_GET,$_POST,$_COOKIE,$_FILES);

// Setup connection to database

$dbConnectionService->connect();

//
// There may be requested actions to do before loading
// the page. Check to see if these actions are in the post data.
//

// Check if we need to login, and if we need to log out.
$loginRequest = new LoginRequest($serverRequest, $services);
$loginRequest->execute();
$logoutRequest = new LogoutRequest($serverRequest);
$logoutRequest->execute();

// Get current login
$login = $loginService->getLogin($serverRequest);

// Check if we need to add a thot, or upload thoughts
$uploadRequest = new UploadRequest($serverRequest, $services, $login);
$uploadRequest->execute();

// Now write the appropriate document.
$documentRequest = new DocumentRequest($serverRequest, $services, $login);
$documentRequest->execute();

$dbConnectionService->close();

// Print running time
echo "<p>" . $timerService->read('total') . " seconds</p>\n";
