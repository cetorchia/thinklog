<?php

// For registering common keywords
// Also we use this to add keyword counts into the knowledgebase

class CommonKeywordService
{
	protected $store;
	protected $keywordService;
	protected $timerService;

	function __construct($services)
	{
		// Get context
		$this->store = $services->tripleStoreService->getStore();
		$this->keywordService = $services->keywordService;
		$this->timerService = $services->timerService;
	}

	//
	// Update knowledgebase for any common keywords we find
	//

	function update()
	{
		// Find keywords and their counts
		$query = SPARQL_PREFIXES .
		         "SELECT ?k COUNT(?t) AS ?cnt WHERE { " .
		         "  ?w a thinklog:Keyword. " .
		         "  ?w rdfs:label ?k. " .
		         "  ?t thinklog:mentions ?w. " .
		         "} " .
		         "GROUP BY ?k ";
		$this->timerService->start('query');
		$rows = $this->store->query($query,"rows");
		echo "Found keyword counts in " . $this->timerService->read('query') . " seconds\n";

		// Filter common ones, save keyword counts
		$words = array();
		$counts = array();
		foreach($rows as $row)
		{
			$word = $row["k"];
			$cnt = $row["cnt"];

			// Record count
			$counts[$word] = $cnt;

			// If we passed the threshold, it's a "common keyword"
			if($row["cnt"] >= KEYWORD_THRESHOLD)
			{
				$words[$word] = $word;
				echo "  $word is a common keyword\n";
			}
		}

		// Insert these words as common keywords
		$this->timerService->start('query');
		$this->keywordService->addCommonKeywords($words);
		echo "Updated common keywords in " . $this->timerService->read('query') . " seconds\n";

		// Update keyword counts
		$this->timerService->start('query');
		$this->keywordService->addKeywordCounts($counts);
		echo "Updated keyword counts in " . $this->timerService->read('query') . " seconds\n";
	}
}
