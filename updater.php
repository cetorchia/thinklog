<?php

//
// Background process that updates knowledge about thoughts
// periodically, particularly about thought keywords.
// TODO: Deduce more about thoughts than that!
//

require_once("def.php");
require_once("error.php");
require_once(DOC_ROOT . "/classes/AppServices.php");
require_once(DOC_ROOT . "/classes/DBConnectionService.php");

/*
 * Set up application context
 */

$services = new AppServices();
$timerService = $services->timerService;
$commonKeywordService = $services->commonKeywordService;
$mentionsService = $services->mentionsService;
$relatedKeywordService = $services->relatedKeywordService;

// Stop it from killing itself if it runs too long
set_time_limit(0);

// Connect to the database
$dbConnectionService = new DBConnectionService($services);
$dbConnectionService->connect();

//
// Update knowledgebase for any keywords thoughts mention.
// This can change as keywords become common keywords.
//

$mentionsService->update();

// Print running time
echo "Total " . $timerService->read('total') . " seconds\n";
