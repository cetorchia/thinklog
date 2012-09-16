<?php

//
// Responsible for querying for frequent keywords, and frequent
// keyword pairs.
//

class TagCloudService
{
	protected $store;

	function __construct($services)
	{
		$this->store = $services->tripleStoreService->getStore();
	}

	//
	// Returns the most common keywords for the thinker and thought, or the
	// most common keywords in general if no thinker is given.
	//
	function getKeywords($thinkerId, $thoughtId)
	{
		// Query for keywords related to this thought and thinker
		if($thinkerId) {
			$query = SPARQL_PREFIXES .
				"SELECT DISTINCT ?keyword WHERE { " .
				"  ?thought a thinklog:Thought; " .
				"           thinklog:author ?thinker; " .
				"           thinklog:mentions ?k. " .
				"  ?thinker a thinklog:Thinker; " .
				"           thinklog:thinkerId \"$thinkerId\". " .
				"  ?k thinklog:count ?cnt. " .
				"  ?k rdfs:label ?keyword. " .
				"} " .
				"ORDER BY DESC(?cnt) LIMIT 5 ";
		}
		else if($thoughtId) {
			$query = SPARQL_PREFIXES .
				"SELECT DISTINCT ?keyword WHERE { " .
				"  ?thought a thinklog:Thought; " .
				"           thinklog:thoughtId \"$thoughtId\"; " .
				"           thinklog:mentions ?k. " .
				"  ?k rdfs:label ?keyword. " .
				"} ";
		}
		else {
			$query = SPARQL_PREFIXES .
				"SELECT ?keyword WHERE { " .
				"  ?k a thinklog:Keyword. " .
				"  ?k thinklog:count ?cnt. " .
				"  ?k rdfs:label ?keyword. " .
				"} " .
				"ORDER BY DESC(?cnt) LIMIT 5 ";
		}
		$rows = $this->store->query($query, "rows");

		// Get the keywords
		$keywords = array();
		foreach($rows as $row) {
			$keywords[$row["keyword"]] = $row["keyword"];
		}

		return($keywords);
	}

	//
	// Returns the most common keyword pairs
	//
	function getKeywordPairs($thinkerId, $thoughtId)
	{
		// Query for keyword pairs related to this thought and thinker
		if($thinkerId) {
			$query = SPARQL_PREFIXES .
				"SELECT DISTINCT ?keyword1 ?keyword2 WHERE { " .
				"  ?thought a thinklog:Thought; " .
				"           thinklog:author ?thinker; " .
				"           thinklog:mentions ?k1; " .
				"           thinklog:mentions ?k2. " .
				"  ?thinker a thinklog:Thinker; " .
				"           thinklog:thinkerId \"$thinkerId\". " .
				"  ?rel a thinklog:PairCount. " .
				"  ?rel thinklog:keyword1 ?k1. " .
				"  ?rel thinklog:keyword2 ?k2. " .
				"  ?rel thinklog:count ?cnt. " .
				"  ?k1 rdfs:label ?keyword1. " .
				"  ?k2 rdfs:label ?keyword2. " .
				"  FILTER(?keyword1 < ?keyword2). " .
				"} " .
				"ORDER BY DESC(?cnt) LIMIT 5 ";
		}
		else if($thoughtId) {
			$query = SPARQL_PREFIXES .
				"SELECT DISTINCT ?keyword1 ?keyword2 WHERE { " .
				"  ?thought a thinklog:Thought; " .
				"           thinklog:thoughtId \"$thoughtId\"; " .
				"           thinklog:mentions ?k1; " .
				"           thinklog:mentions ?k2. " .
				"  ?k1 rdfs:label ?keyword1. " .
				"  ?k2 rdfs:label ?keyword2. " .
				"  FILTER(?keyword1 < ?keyword2). " .
				"} ";
		}
		else {
			$query = SPARQL_PREFIXES .
				"SELECT ?keyword1 ?keyword2 WHERE { " .
				"  ?rel a thinklog:PairCount. " .
				"  ?rel thinklog:keyword1 ?k1. " .
				"  ?rel thinklog:keyword2 ?k2. " .
				"  ?rel thinklog:count ?cnt. " .
				"  ?k1 rdfs:label ?keyword1. " .
				"  ?k2 rdfs:label ?keyword2. " .
				"  FILTER(?keyword1 < ?keyword2). " .
				"} " .
				"ORDER BY DESC(?cnt) LIMIT 5 ";
		}
		$rows = $this->store->query($query, "rows");

		// Get the keywords
		$keywordPairs = array();
		foreach($rows as $row) {
			$keywordPairs[$row["keyword1"]] = $row["keyword2"];
		}

		return($keywordPairs);
	}
}
