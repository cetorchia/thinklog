<?php

// For managing keyword triples

class MentionsService
{
	protected $keywordService;
	protected $timerService;
	protected $queryService;
	protected $thoughtService;

	function __construct($services)
	{
		// Get context
		$this->keywordService = $services->keywordService;
		$this->timerService = $services->timerService;
		$this->thoughtService = $services->thoughtService;
		$this->queryService = $services->queryService;
	}

	//
	// Update knowledgebase for any keywords thoughts mention
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
			$rows = $this->queryService->getAllThoughts($offset, $num);
			echo "Retrieved thoughts in " . $this->timerService->read('query') . " seconds\n";

			//
			// Triplify mentions for these thoughts
			//
			$n = 0;
			foreach($rows as $row)
			{
				$n = $n + 1; if($n > $num) break;

				// Retrieve thought data
				$thought = $this->thoughtService->getFromRow($row);
				echo "Thought: " . $thought->getId() . " (" . date("r",$thought->getDate()) . ")\n";
				echo "  \"".$thought->getBody()."\"\n";

				// Triplify it
				$this->timerService->start('query');
				$this->mentions($thought,true);
				echo "  mentions triplified in " . $this->timerService->read('query') . " seconds\n";
			}

			usleep(100000);				// One tenth of a second
			$offset = $offset + $num;

		} while(count($rows) > $num);
	}

	//
	// Find the keywords that this thought mentions and add these relationships
	//

	function mentions($thought,$doEcho = false)
	{
		$thoughtId = $thought->getId();
		$keywords = $this->keywordService->getKeywords($thought->getBody());

		// Add the keywords to the DB if necessary
		$this->keywordService->addKeywords($keywords);

		if($doEcho) {
			foreach($keywords as $keyword)
			{
				echo "    mentioning $keyword\n";	// Tell the user
			}
		}

		// Add records for the thought mentioning each of these keywords.
		$query = "INSERT INTO mentions (thought_id, keyword_id) " .
		         "SELECT $thoughtId, keyword_id FROM keywords " .
		         "WHERE keyword IN (" . $this->keywordService->getKeywordsSQL($keywords) .")";
		if (!mysql_query($query)) {
			echo "  warning: could not add mentions for thought $thoughtId:\n";
			echo "           " . mysql_error() . "\n";
		}
	}
}
