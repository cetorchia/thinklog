<?php

//
// Responsible for inserting and querying with regard to keywords being
// related. Currently, this holds if they are in the same sentence.
//

class RelatedKeywordService
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
	// Update the knowledgebase for any relationships between keywords arising
	// from thoughts. We do this after the mentions because the relationships
	// between keywords depends on them.
	//

	function update()
	{
		$num = 10;
		$start = 0;
		do {
			$rows = $this->getPairRelationships($start,$num);
			$start = $start + $num;
			if($rows)
			{
				$this->pairCounts($rows);
				$this->relatedKeywords($rows);
			}
		} while($rows);						// Keep going until no more
	}

	//
	// For each pair of keywords, get the number of thoughts they occur
	// together.
	//

	function getPairRelationships($start, $num)
	{
		$this->timerService->start('query');
		$query = SPARQL_PREFIXES .
		         "SELECT ?keyword1 ?keyword2 COUNT(?thought) AS ?cnt WHERE { " .
		         "  ?k1 a thinklog:Keyword; " .
		         "      rdfs:label ?keyword1. " .
		         "  ?k2 a thinklog:Keyword; " .
		         "      rdfs:label ?keyword2. " .
		         "  ?thought a thinklog:Thought; " .
		         "           thinklog:mentions ?k1; " .
		         "           thinklog:mentions ?k2. " .
		         "  FILTER(?keyword1 != ?keyword2). " .
		         "} GROUP BY ?keyword1,?keyword2 LIMIT $num OFFSET $start ";
		$rows = $this->store->query($query,"rows");
		echo "Got pair relationships in " . $this->timerService->read('query') . " seconds\n";
		return($rows);
	}

	// State that that these pairs of keywords are related,
	// Records keyword pair counts
	// Returns whether we need to look for more relationships
	function relatedKeywords($rows)
	{
		$query = SPARQL_PREFIXES .
		         "INSERT INTO <" . THINKLOG_GRAPH . "> " .
		         "CONSTRUCT { " .
		         "  ?keyword1 thinklog:relatedKeyword ?keyword2. " .
		         "} " .
		         "WHERE { ";
		$first = true;
		foreach($rows as $row)
		{
			if($row["cnt"] >= KEYWORD_THRESHOLD)
			{
				if($first) { $first = false; }
				else { $query .= " UNION "; }	// One came before

				echo "  ".$row["keyword1"]." and ".$row["keyword2"]." are together in ".$row["cnt"]." thoughts\n";
				$query .=
				 "  { " .
				 "    ?keyword1 rdfs:label \"" . $row["keyword1"] . "\". " .
				 "    ?keyword2 rdfs:label \"" . $row["keyword2"] . "\". " .
				 "  } ";
			}
		}
		$query .= "} ";

		$this->timerService->start('query');
		$this->store->query($query,"raw");
		echo "Updated pair relationships in " . $this->timerService->read('query') . " seconds\n";

		return(true);
	}

	// Records pair counts
	function pairCounts($rows)
	{
		foreach($rows as $row)
		{
			if($row["cnt"] < 1) {
				continue;
			}

			$keyword1 = $row["keyword1"];
			$keyword2 = $row["keyword2"];
			$cnt = $row["cnt"];

			// See if there already is a pair count for this pair
			$query = SPARQL_PREFIXES .
				"SELECT ?rel WHERE { " .
				"  ?rel a thinklog:PairCount ; " .
				"       thinklog:keyword1 ?k1 ; " .
				"       thinklog:keyword2 ?k2. " .
				"  ?k1 rdfs:label \"$keyword1\". " .
				"  ?k2 rdfs:label \"$keyword2\". " .
				"} ";
			$myRows = $this->store->query($query,"rows");

			// Add the pair count if it is not already there
			if(!$myRows) {
				$relName = "rel_$keyword1_$keyword2";
				$query = SPARQL_PREFIXES .
					"INSERT INTO <" . THINKLOG_GRAPH . "> " .
					"CONSTRUCT { " .
					"  _:$relName a thinklog:PairCount ; " .
					"             thinklog:keyword1 ?k1 ; " .
					"             thinklog:keyword2 ?k2 ; " .
					"             thinklog:count \"0\". " .
					"} " .
					"WHERE { " .
					"  ?k1 a thinklog:Keyword; rdfs:label \"$keyword1\". " .
					"  ?k2 a thinklog:Keyword; rdfs:label \"$keyword2\". " .
					"} ";
				$this->timerService->start('query');
				$this->store->query($query,"raw");
				echo "Created pair count for $keyword1,$keyword2 " . $this->timerService->read('query') . " seconds\n";
			}

			// Delete the current count
			$query = SPARQL_PREFIXES .
				"DELETE FROM <" . THINKLOG_GRAPH . "> " .
				"CONSTRUCT { " .
				"  ?rel thinklog:count ?cnt. " .
				"} " .
				"WHERE { " .
				"  ?rel a thinklog:PairCount; thinklog:keyword1 ?k1; thinklog:keyword2 ?k2. " .
				"  ?k1 a thinklog:Keyword; rdfs:label \"$keyword1\". " .
				"  ?k2 a thinklog:Keyword; rdfs:label \"$keyword2\". " .
				"  ?rel thinklog:count ?cnt. " .
				"} ";
			$this->timerService->start('query');
			$this->store->query($query,"raw");
			echo "Deleted pair count count for $keyword1,$keyword2 " . $this->timerService->read('query') . " seconds\n";

			// Update the count
			$query = SPARQL_PREFIXES .
				"INSERT INTO <" . THINKLOG_GRAPH . "> " .
				"CONSTRUCT { " .
				"  ?rel thinklog:count \"$cnt\". " .
				"} " .
				"WHERE { " .
				"  ?rel a thinklog:PairCount; thinklog:keyword1 ?k1; thinklog:keyword2 ?k2. " .
				"  ?k1 a thinklog:Keyword; rdfs:label \"$keyword1\". " .
				"  ?k2 a thinklog:Keyword; rdfs:label \"$keyword2\". " .
				"} ";
			$this->timerService->start('query');
			$this->store->query($query,"raw");
			echo "Updated pair count for $keyword1,$keyword2 " . $this->timerService->read('query') . " seconds\n";
		}
	}
}
