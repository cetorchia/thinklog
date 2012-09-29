<?php

// Responsible for inserting keyword instances in knowledge base,
// and for extracting keywords from text.

class KeywordService
{
	protected $keywords = array();

	function __construct($services)
	{
		// Get context
	}

	// Adds a keyword node with this label to the database
	public function addKeywords($keywords, $doEcho=false)
	{
		// Check if they're in the cache
		foreach($keywords as $keyword) {
			if($this->keywords[$keyword])
			{
				unset($keywords[$keyword]);
			}
		}
		if(empty($keywords)) {
			return;
		}

		// The rest of the keywords need to be cached
		foreach($keywords as $keyword) {
			$this->keywords[$keyword] = $keyword;
		}

		// Try to put the so-far unseen keywords in the database
		$query = "INSERT IGNORE INTO keywords (keyword) " .
		         "VALUES " . $this->getKeywordTuplesSQL($keywords);
		if (!mysql_query($query)) {
			if ($doEcho) {
				echo "  warning: could not add these keywords:\n"; 
				echo "           " . mysql_error() . "\n";
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
		$query = "SELECT keyword FROM common_keywords, keywords " .
		         "WHERE keywords.keyword_id = common_keywords.keyword_id " .
		         "  AND keyword IN (" . $this->getKeywordsSQL($words) . ")";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
			$word = $row["keyword"];
			$keywords[$word] = $word;
			$this->keywords[$word] = $word;
		}
	}

	// Removes common keywords from these words
	function removeCommonKeywords(&$words) {
		
		$commonKeywords = array();
		$this->getCommonKeywords($words, $commonKeywords);
		foreach ($commonKeywords as $keyword) {
			unset($words[$keyword]);
		}
	}

	// Returns the list of keywords as a string of the form "('keyword1'), ('keyword2'), ..."
	function getKeywordTuplesSQL($keywords) {
		$first = true;
		$output = "";
		foreach ($keywords as $keyword) {
			if ($first) {
				$output = "('$keyword')";
				$first = false;
			} else {
				$output .= ", ('$keyword')";
			}
		}
		return $output;
	}

	// Returns the list of keywords as a string of the form "'keyword1', 'keyword2', ..."
	function getKeywordsSQL($keywords) {
		$first = true;
		$output = "";
		foreach ($keywords as $keyword) {
			if ($first) {
				$output = "'$keyword'";
				$first = false;
			} else {
				$output .= ", '$keyword'";
			}
		}
		return $output;
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
