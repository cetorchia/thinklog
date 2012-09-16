<?php

//
// Background process that updates thought triples
//

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
$commonKeywordService = $services->commonKeywordService;
$wikipediaKeywordService = $services->wikipediaKeywordService;
$mentionsService = $services->mentionsService;
$relatedKeywordService = $services->relatedKeywordService;
$tripleStoreService = $services->tripleStoreService;
$store = $tripleStoreService->getStore();

// Stop it from killing itself if it runs too long
set_time_limit(0);

/*
 * Update schema
 */
$tripleStoreService->loadSchema();

/*
 * Triplify each thought
 */

//
// Update knowledgebase for keyword counts, and for 
// any keywords that have become common.
//

$commonKeywordService->update();
$wikipediaKeywordService->update();	// TODO: really slow

//
// Update knowledgebase for any keywords thoughts mention
//

$mentionsService->update();

//
// Update the knowledgebase for any relationships between keywords arising
// from thoughts. We do this after the mentions because the relationships
// between keywords depends on them.
//

$relatedKeywordService->update();

// Print running time
echo "Total " . $timerService->read('total') . " seconds\n";
