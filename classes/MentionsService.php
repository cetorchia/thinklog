<?php

// For rescanning thoughts for keywords after new keywords
// have turned up in the knowledgebase

class MentionsService
{
	protected $keywordService;
	protected $timerService;
	protected $queryService;
	protected $thoughtService;

	function __construct(&$services)
	{
		// Get context
		$this->keywordService = $services->keywordService;
		$this->timerService = $services->timerService;
		$this->thoughtService = $services->thoughtService;
		$this->queryService = $services->queryService;
	}

	//
	// Update knowledgebase for any keywords thoughts mention
	// Keywords were extracted when thoughts were created, but
	// more keywords probably have come into existence. Better
	// check the old thoughts for these new keywords and tell
	// the knowledgebase about them.
	//

	function update()
	{
		$num = 10;
		$offset = 0;
		do {
			//
			// Retrieve thoughts
			//
			$this->timerService->start('query');
			$result = $this->queryService->getAllThoughts(null, $offset, $num);
			echo "Retrieved $num thoughts in " . $this->timerService->read('query') . " seconds\n";

			//
			// Try to get previously unseen "mentions" for these thoughts
			//
			$n = 0;
			while ($row = mysql_fetch_array($result)) {
				$n = $n + 1;
				if ($n > $num)
					break;

				// Retrieve thought data
				$thought = $this->thoughtService->getFromRow($row);
				echo "Thought: " . $thought->getId() . " (" . date("r",$thought->getDate()) . ")\n";

				// Search for mentions
				$this->timerService->start('query');
				$this->mentions($thought,true);
				echo "  mentions updated in " . $this->timerService->read('query') . " seconds\n";
			}

			usleep(100000);				// One tenth of a second
			$offset = $offset + $num;

		} while($n > $num);
	}

	//
	// Find the keywords that this thought mentions and add these relationships
	//

	function mentions($thought,$doEcho = false)
	{
		$thoughtId = $thought->getId();
		$keywords = $this->keywordService->getKeywords($thought->getBody());
		if (!$keywords) {
			return;
		}

		// Add the keywords to the DB if necessary
		$this->keywordService->addKeywords($keywords, $doEcho);
		$keywordList = $this->keywordService->getKeywordsSQL($keywords);
		if ($doEcho) {
			echo "    mentioning $keywordList\n";	// Tell the user
		}

		// Add records for the thought mentioning each of these keywords.
		$query = "INSERT IGNORE INTO mentions (thought_id, keyword_id) " .
		         "SELECT $thoughtId, keyword_id FROM keywords " .
		         "WHERE keyword IN ($keywordList)";
		if (!mysql_query($query)) {
			if ($doEcho) {
				echo "  warning: " . mysql_error() . "\n";
			}
		}
	}
}
