<?php

//
// Functions that (hopefully) turn your typical microblog into a thinklog.
//
// (c) 2010 Carlos E. Torchia 
// Licensed under GNU GPL v2, see fsf.org
//

class QueryService
{
	protected $thoughtService;
	protected $keywordService;
	protected $store;

	//
	// We'll need a few services to get data from the database
	//

	public function __construct($services)
	{
		$this->thoughtService = $services->thoughtService;
		$this->keywordService = $services->keywordService;
		$this->store = $services->tripleStoreService->getStore();
	}

	//
	// Gets articles related to the thought
	//

	public function getRelated($login, $start, $num, $thought)
	{
		$thoughtId = $thought->getId();
		$related = array();

		$rows = $this->getRelatedByKeywords($thoughtId, $start, $num);
		$thoughts = array();
		$this->retrieveVisibleThoughts($login, null, $num, $rows, $thoughts);

		return($thoughts);
	}

	//
	// Queries the database for thoughts that are relevant to the query.
	// Returns whether there are more search results after those 
	// limited.
	//

	public function getThoughts(
			$login,
			$thinkerId,
			$query,
			$start,
			$num,
			&$thoughts)
	{
		$related = array();

		if(!$query) {
			// Get all thoughts if user specifies empty query string
			$rows = $this->getAllThoughts($start, $num);
		}

		else {
			$rows = $this->getThoughtsByKeywords($query, $start, $num);
		}

		$thoughts = array();
		$more = $this->retrieveVisibleThoughts($login, $thinkerId, $num, $rows, $thoughts);

		return($more);
	}

	//
	// Gets all thoughts
	//
	public function getAllThoughts($start,$num)
	{
		// Query for the thoughts
		$query = SPARQL_PREFIXES .
			"SELECT ?thoughtId ?date ?thinkerId ?content ?private " .
			"WHERE { " .
		    "  ?thought a thinklog:Thought ; " .
			"           thinklog:thoughtId ?thoughtId ; " .
			"           thinklog:date ?date; " .
			"           thinklog:author ?thinker; " .
			"           thinklog:content ?content; " .
			"           thinklog:private ?private. " .
			"  ?thinker thinklog:thinkerId ?thinkerId. " .
			"} " .
			"ORDER BY DESC(?date) LIMIT ".($num+1)." OFFSET $start ";
		$rows = $this->store->query($query, "rows");
		return($rows);
	}

	//
	// Gets thoughts related to the query string
	//
	public function getThoughtsByKeywords($query, $start, $num)
	{
		$words = $this->keywordService->getWords($query);

		// Query for the thoughts
		$query = SPARQL_PREFIXES .
			"SELECT ?thoughtId ?date ?private ?content ?thinkerId COUNT(?word1) AS ?cnt1 COUNT(?word2) AS ?cnt2 " .
			"WHERE { " .
		    "  ?thought a thinklog:Thought ; " .
			"           thinklog:thoughtId ?thoughtId ; " .
			"           thinklog:date ?date; " .
			"           thinklog:author ?thinker; " .
			"           thinklog:content ?content; " .
			"           thinklog:private ?private. " .
			"  ?thinker thinklog:thinkerId ?thinkerId. " .
			"  ?thought thinklog:mentions ?word. ";
		$query .=
			"  OPTIONAL { " .
			"    ?word1 rdfs:label ?label. " .
			"    FILTER(?word = ?word1) " .
			"  } ";
		$query .=
			"  OPTIONAL { " .
			"    ?word thinklog:relatedKeyword ?word2. " .
			"    ?word2 rdfs:label ?label. " .
			"    FILTER(?word != ?word2) " .
			"  } ";
		$query .= $this->keywordService->getKeywordsFilter("?label",$words);
		$query .=
			"} " .
			"GROUP BY ?thoughtId " .
			"ORDER BY DESC(?cnt1) DESC(?cnt2) DESC(?date) " .
			"LIMIT ".($num+1)." OFFSET $start ";
		$rows = $this->store->query($query, "rows");
//		echo htmlentities($query)."\n";
//		echo "<pre>\n";
//		var_dump($this->store->getErrors());
//		var_dump($rows);
//		echo "</pre>\n";
		return($rows);
	}

	//
	// Gets thoughts related to the thought with this Id
	//
	public function getRelatedByKeywords($thoughtId, $start, $num)
	{
		// Query for the thoughts
		$query = SPARQL_PREFIXES .
			"SELECT ?thoughtId ?date ?private ?content ?thinkerId COUNT(?w1) AS ?cnt1 COUNT(?w2) AS ?cnt2 " .
			"WHERE { " .
			"  ?myThought a thinklog:Thought ; " .
			"           thinklog:thoughtId \"$thoughtId\". " .
		    "  ?thought a thinklog:Thought ; " .
			"           thinklog:thoughtId ?thoughtId ; " .
			"           thinklog:date ?date; " .
			"           thinklog:author ?thinker; " .
			"           thinklog:content ?content; " .
			"           thinklog:private ?private. " .
			"  ?thinker thinklog:thinkerId ?thinkerId. " .
			"  ?thought thinklog:mentions ?word. " .
			"  ?myWord rdfs:label ?label. " .
			"  OPTIONAL { ".
			"    ?myThought thinklog:mentions ?myWord. " .
			"    ?myWord rdfs:label ?w1. " .
			"    FILTER(?word = ?myWord). " .
			"  } " .
			"  OPTIONAL { ".
			"    ?myThought thinklog:mentions ?myWord. " .
			"    ?word thinklog:relatedKeyword ?myWord. " .
			"    ?myWord rdfs:label ?w2. " .
			"    FILTER(?word != ?myWord). " .
			"  } " .
			"  FILTER((?w1 = ?label || ?w2 = ?label) && ?thoughtId != \"$thoughtId\"). ";
		$query .=
			"} " .
			"GROUP BY ?thoughtId " .
			"ORDER BY DESC(?cnt1) DESC(?cnt2) DESC(?date) " .
			"LIMIT ".($num+1)." OFFSET $start ";
		$rows = $this->store->query($query, "rows");
//		echo htmlentities($query)."\n";
//		echo "<pre>\n";
//		var_dump($this->store->getErrors());
//		var_dump($rows);
//		echo "</pre>\n";
		return($rows);
	}

	//
	// Leave only visible thoughts.
	// Stores them in $thoughts, returns whether there are more
	//

	public function retrieveVisibleThoughts($login, $thinkerId, $num, $rows, &$thoughts)
	{
		$n = 0;
		foreach($rows as $row)
		{
			// Retrieve the thought from row
			$thought = $this->thoughtService->getFromRow($row);

			// Add the thought to the collection if possible
			if(isset($thought) && $this->thoughtService->getReadPermission($login, $thought))
			{
				if((!isset($thinkerId)) || ($thought->getThinkerId() == $thinkerId))
				{
					// Don't go over limit
					$n = $n + 1;
					if($n > $num) {
						return(true);
					}

					// Add the thought to our results
					$thoughts[] = $thought;
				}
			}

		}

		return(false);
	}
}
