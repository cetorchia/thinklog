<?php

//
// Responsible for inserting and querying with regard to keywords being
// related.
//

class RelatedKeywordService
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
	// Update the knowledgebase for any relationships between keywords arising
	// from thoughts.
	//

	function update()
	{
		// Put keyword pairs that occur enough times together
		$this->timerService->start('query');
		$query = "INSERT INTO related_keywords " .
		         "SELECT keyword1, keyword2 " .
		         "FROM keyword_pair_count " .
		         "WHERE cnt > " . KEYWORD_THRESHOLD;

		if (!mysql_query($query)) {
			echo "  warning: could not add new related keyword pairs:\n";
			echo "           ".mysql_error()."\n";
		}
		echo "Updated related keywords in " . $this->timerService->read('query') . " seconds\n";
	}
}
