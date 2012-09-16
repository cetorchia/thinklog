<?php

// Update ontology for Wikipedia keywords

class WikipediaKeywordService
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
		$words = array();
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
			// Collect the words from the thoughts
			//
			$n = 0;
			foreach($rows as $row)
			{
				$n = $n + 1; if($n > $num) break;

				// Retrieve thought data
				$thought = $this->thoughtService->getFromRow($row);
				echo "Thought: " . $thought->getId() . " (" . date("r",$thought->getDate()) . ")\n";
				$myWords = $this->keywordService->getWords($thought->getBody());
				foreach($myWords as $word) {
					$words[$word] = $word;
				}
			}

			// Need sleep!
			usleep(100000);				// One tenth of a second
			$offset = $offset + $num;

		} while(count($rows) > $num);

		// Remove already-present keywords, so we don't
		// do this again!
		$this->timerService->start('query');
		$this->keywordService->removeCommonKeywords($words);
		echo "Looked up existing common keywords in " . $this->timerService->read('query') . " seconds\n";

		// Query Wikipedia
		foreach($words as $word) {
			$this->timerService->start('query');
			if(!$this->isWikipediaKeyword($word)) {
				unset($words[$word]);
			}
			else {
				echo "  $word is a Wikipedia keyword\n";
			}
			echo "Looked up keyword in Wikipedia in " . $this->timerService->read('query') . " seconds\n";
		}

		// Add them
		if(count($words) > 0) {
			// Add them as keywords and as common keywords
			$this->timerService->start('query');
			$this->keywordService->addKeywords($words);
			$this->keywordService->addCommonKeywords($words);
			echo "Added common keywords in " . $this->timerService->read('query') . " seconds\n";
		}
	}

	// Sees if the word is in Wikipedia
	function isWikipediaKeyword($word) { return ($word == "computer" || $word == "hello"); }
	function isWikipediaKeyword2($word)
	{
		// Get the Wikipedia API result
		$data = "action=query&list=search&srlimit=1&srprop=timestamp&srsearch=".urlencode($word)."&format=xml";
		$dataLength = strlen($data);
		$opts = array("http" => array(
			"method"  => "POST",
			"header"  => "User-Agent: Thinklog\r\n" .
			             "Content-Length: $dataLength\r\n" .
			             "Content-Type: application/x-www-form-urlencoded\r\n",
			"content" => $data,
		));
		$context = stream_context_create($opts);
		$url = "http://en.wikipedia.org/w/api.php";
		$src = file_get_contents($url,false,$context);

		// Extract the title from the response
		$doc = new DOMDocument();
		$doc->loadXML($src);
		$el = $doc->getElementsByTagName("p");
		if(!isset($el) ||
			!($el = $el->item(0)) ||
			!($title = $el->getAttribute("title")))
		{
			return(false);
		}

		// Convert name to same format that we use
		$name = strtolower(preg_replace('/\s+/','_',$title));

		return($name == $word);
	}
}
