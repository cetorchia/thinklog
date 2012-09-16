<?php

// Responsible for inserting keyword instances in knowledge base,
// and for extracting keywords from text.

class KeywordService
{
	protected $store;
	protected $keywords = array();

	function __construct($services)
	{
		// Get context
		$this->store = $services->tripleStoreService->getStore();
	}

	// Adds a keyword node with this label to the database
	public function addKeywords($keywords)
	{
		// Check if they're in the cache
		foreach($keywords as $keyword) {
			if($this->keywords[$keyword])
			{
				echo "  $keyword is cached as a keyword\n";
				unset($keywords[$keyword]);
			}
		}
		if(empty($keywords)) {
			return;
		}

		// The rest of the keywords need to be cached
		foreach($keywords as $keyword) {
			echo "  caching $keyword as a keyword\n";
			$this->keywords[$keyword] = $keyword;
		}

		// Look to see if they are already in the knowledgebase
		$query = SPARQL_PREFIXES .
		         "SELECT ?k " .
		         "WHERE { " .
		         "  ?w a thinklog:Keyword. " .
		         "  ?w rdfs:label ?k. " .
		            $this->getKeywordsFilter("?k",$keywords) .
		         "} ";
		$rows = $this->store->query($query,"rows");

		// Filter out the ones that are already there
		foreach($rows as $row) {
			unset($keywords[$row["k"]]);
		}

		// If any keywords are not in the cache and not in the kb, add them
		if($keywords)
		{
			$query = SPARQL_PREFIXES .
			         "INSERT INTO <" . THINKLOG_GRAPH . "> " .
			         "CONSTRUCT { ";
			foreach($keywords as $keyword)
			{
				$query .=
			         " _:keyword_$keyword a thinklog:Keyword ; " .
			         "                    rdfs:label \"$keyword\". ";
			}
			$query .= "} ";
			echo "  adding these keywords\n";
			$this->store->query($query,"raw");
		}
	}

	function addKeywordCounts($counts)
	{
		foreach($counts as $keyword => $cnt)
		{
			// Delete the current count into the triplestore
			$query = SPARQL_PREFIXES .
				"DELETE FROM <" . THINKLOG_GRAPH . "> " .
				"CONSTRUCT { " .
				"  ?w thinklog:count ?cnt. " .
				"} " .
				"WHERE { " .
				"  ?w a thinklog:Keyword. " .
				"  ?w rdfs:label \"$keyword\". " .
				"  ?w thinklog:count ?cnt. " .
				"} ";
			$this->store->query($query, "raw");

			// Insert the count into the triplestore
			$query = SPARQL_PREFIXES .
				"INSERT INTO <" . THINKLOG_GRAPH . "> " .
				"CONSTRUCT { " .
				"  ?w thinklog:count \"$cnt\". " .
				"} " .
				"WHERE { " .
				"  ?w a thinklog:Keyword. " .
				"  ?w rdfs:label \"$keyword\". " .
				"} ";
			$this->store->query($query, "raw");
		}
	}

	// Adds the words as common keywords
	function addCommonKeywords($words)
	{
		foreach($words as $word)
		{
			// Insert them
			$query = SPARQL_PREFIXES .
			         "INSERT INTO <" . THINKLOG_GRAPH . "> " .
			         "CONSTRUCT { " .
			         "  ?w a thinklog:CommonKeyword. " .
			         "} " .
			         "WHERE { " .
			         "  ?w a thinklog:Keyword. " .
			         "  ?w rdfs:label \"$word\". " .
			         " } ";
			$this->store->query($query,"raw");
		}
	}

	// Removes words that are already common keywords from the given set
	function removeCommonKeywords(&$words)
	{
		foreach($words as $word)
		{
			echo "  Looking up $word\n";
			$query = SPARQL_PREFIXES .
				"SELECT ?k WHERE { ?w a thinklog:CommonKeyword; rdfs:label ?k. " .
				"  FILTER(?k = \"$word\"). " .
				" } ";
			$rows = $this->store->query($query,"rows");
			foreach($rows as $row) {
				$word = $row["k"];
				echo "  $word is already a common keyword\n";
				unset($words[$word]);
			}
		}
	}

	// Gets the keywords out of some text
	function getKeywords($text)
	{
		$keywords = array();
		$words = $this->getWords($text);

		$this->getHashTags($text,$keywords);
		$this->getCommonKeywords($words,$keywords);

		return($keywords);
	}

	// Gets the hash keywords of a thought
	function getHashTags($text,&$keywords)
	{
		// Get keywords from hash tags
		if(preg_match_all(HASH_TAG_REGEX, $text, $matches, PREG_SET_ORDER))
		{
			foreach($matches as $match)
			{
				$word = strtolower($match[1]);
				$keywords[$word] = $word;
			}
		}
	}

	// Gets the common keywords of your set of keywords
	function getCommonKeywords($words,&$keywords)
	{
		// Look these words up in the kb, and return their count
		$query = SPARQL_PREFIXES .
		         "SELECT ?k WHERE { " .
		         "  ?w a thinklog:CommonKeyword. " .
		         "  ?w rdfs:label ?k. " .
		            $this->getKeywordsFilter("?k",$words) .
		         "} " .
		         "GROUP BY ?k ";
		$rows = $this->store->query($query,"rows");
		foreach($rows as $row)
		{
			$word = $row["k"];
			$keywords[$word] = $word;
			$this->keywords[$word] = $word;
			echo "  $word is a common keyword\n";
		}
	}

	// Returns a SPARQL filter for letting variable $k be equal to
	// one of the keywords.
	function getKeywordsFilter($k,$keywords)
	{
		$filter = "FILTER(";
		$first = true;
		foreach($keywords as $keyword)
		{
			if($first) {
				$first = false;
			}
			else {
				$filter .= " || ";
			}
			$filter .= "$k = \"$keyword\"";
		}
		$filter .= ") ";
		return($filter);
	}

	// Splits the thought text up into lowercase PHRASES (words)
	// Note: we don't distinguish between words and phrases. Words can have spaces in them,
	//       and these are replaced by underscores.
	function getWords($text)
	{
		$sentences = $this->getSentences($text);
		$words = array();

		foreach($sentences as $text)
		{
			// Get the words of this sentence
			$sentenceWords = $this->getOneWordPhrases($text);

			// For each word
			for($i = 0; $i <= count($sentenceWords) - 1; $i = $i + 1)
			{
				// For each interval of words starting at that word, add it as a phrase
				$word = $sentenceWords[$i];
				$words[$word] = $word;
				for($j = $i + 1; $j <= count($sentenceWords) - 1; $j = $j + 1)
				{
					$word = $word . "_" . $sentenceWords[$j];	// add the next word to the phrase
					$words[$word] = $word;						// add the phrase to the set
				}
			}
		}

		return($words);
	}

	// Gets all words (that don't have spaces in them)
	function getOneWordPhrases($text)
	{
		// Filter out nonword letters and trim the whitespace
		$text = preg_replace('/[^_\w\s]+/',' ',$text);		// Only take words and whitespace
		$text = preg_replace('/(?:^\s+|\s+$)/','',$text);	// Trim whitespace from beginning and end
		$text = strtolower($text);

		// Get the words between the whitespace
		if($text) {
			$sentenceWords = preg_split('/\s+/', $text);
		}
		else {
			$sentenceWords = array();
		}

		return($sentenceWords);								// Re-index from 0 to n-1 and return
	}

	// Splits the thought text up into sentences (array of strings)
	// (TODO: move into text utilities library?)
	function getSentences($text)
	{
		return(preg_split('/(?:[\;\:\"\'\,\.\!\?]|\n\n+)/',$text));
	}
}
