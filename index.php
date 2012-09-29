<?php

require_once("def.php");
require_once("error.php");
require_once("messages.php");
require_once(DOC_ROOT . "/classes/AppServices.php");
require_once(DOC_ROOT . "/classes/DBConnectionService.php");
require_once(DOC_ROOT . "/classes/LoginRequest.php");
require_once(DOC_ROOT . "/classes/DocumentRequest.php");
require_once(DOC_ROOT . "/classes/LogoutRequest.php");
require_once(DOC_ROOT . "/classes/LoginService.php");
require_once(DOC_ROOT . "/classes/AddRequest.php");
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

// Check if we need to add thoughts
$addRequest = new AddRequest($serverRequest, $services, $login);
$addRequest->execute();

// Now write the appropriate document.
$documentRequest = new DocumentRequest($serverRequest, $services, $login);
$documentRequest->execute();

$dbConnectionService->close();
