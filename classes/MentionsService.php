<?php

// For managing keyword triples

class MentionsService
{
	protected $store;
	protected $keywordService;
	protected $timerService;
	protected $queryService;
	protected $thoughtService;

	function __construct($services)
	{
		// Get context
		$this->store = $services->tripleStoreService->getStore();
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
		$keywords = $this->keywordService->getKeywords($thought->getBody());

		// Add the keywords to the DB if necessary
		$this->keywordService->addKeywords($keywords);

		if($doEcho) {
			foreach($keywords as $keyword)
			{
				echo "    mentioning $keyword\n";	// Tell the user
			}
		}

		// Add triples for the thought mentioning each of these keywords.
		$query = SPARQL_PREFIXES .
		         "INSERT INTO <" . THINKLOG_GRAPH . "> " .
		         "CONSTRUCT { " .
		         "  ?thought thinklog:mentions ?keyword. " .
		         "} " .
		         "WHERE { " .
		         "  ?thought thinklog:thoughtId \"" . $thought->getId() . "\". " .
		         "  ?keyword rdfs:label ?k. " .
		            $this->keywordService->getKeywordsFilter("?k",$keywords) .
		         "} ";
		$this->store->query($query,"raw");
	}
}
