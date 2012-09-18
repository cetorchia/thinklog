<?php

// For registering common keywords
// Also we use this to add keyword counts into the knowledgebase

class CommonKeywordService
{
	protected $keywordService;
	protected $timerService;

	function __construct($services)
	{
		// Get context
		$this->keywordService = $services->keywordService;
		$this->timerService = $services->timerService;
	}

	//
	// Update knowledgebase for any common keywords we find
	//

	function update()
	{
		// Put keywords that occur enough times
		$this->timerService->start('query');
		$query = "INSERT INTO common_keywords " .
		         "SELECT keyword_id from keyword_count " .
		         "WHERE cnt > " . KEYWORD_THRESHOLD;
		if (!mysql_query($query)) {
			echo "  warning: could not add new common keywords:\n";
			echo "           ".mysql_error()."\n";
		}
		echo "Updated common keywords in " . $this->timerService->read('query') . " seconds\n";
	}
}
