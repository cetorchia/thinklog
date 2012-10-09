<?php

require_once("def.php");
require_once(DOC_ROOT . "classes/DBConnectionService.php");
require_once(DOC_ROOT . "classes/SentimentService.php");

class SentimentServiceTest
{
	public function run()
	{
		$sentimentService = new SentimentService(null);
		$keywords = array(array("keyword" => "flower", "cnt" => 3),
		                  array("keyword" => "joy", "cnt" => 1));
		if ($sentimentService->getAverageSentiment($keywords) != 0.125) {
			echo "FAILED: average sentiment should be 0.125\n";
		}
	}
}

$connService = new DBConnectionService(null);
$connService->connect();

$test = new SentimentServiceTest();
$test->run();

$connService->close();
