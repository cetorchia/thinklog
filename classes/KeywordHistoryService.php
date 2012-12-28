<?php

class KeywordHistoryService
{
	protected $keywordService;

	function __construct(&$services)
	{
		$this->keywordService = $services->keywordService;
	}

	//
	// Return keyword activity in the last 30 days
	//
	function getKeywordHistory($thinkerId, $query)
	{
		// Generate the query statement
		if ($query) {
			$words = $this->keywordService->getWords($query);
			$wordlist = $this->keywordService->getKeywordsSQL($words);
		} else {
			$wordlist = null;
		}
		$thinkerSQL = $thinkerId ? "t.thinker_id = '$thinkerId'" : "TRUE";

		for ($day = 0; $day < 30; $day++) {
			$query .= "(" . $this->getDayQuery($thinkerSQL, $wordlist, $day) . ")";
			if ($day < 29) {
				$query .= " UNION ";
			}
		}

		// Retrieve the keyword day counts
		$keywordHistory = array();
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
			$keyword = $row["keyword"];
			$count = (int)$row["cnt"];
			$day = (int)$row["day"];
			if (!isset($keywordHistory[$keyword])) {
				$keywordHistory[$keyword] = array_fill(0, 30, 0);
			}
			$keywordHistory[$keyword][$day] = $count;
		}

		return $keywordHistory;
	}

	// Returns the MySQL SELECT clause for given thinker, query, and day
	protected function getDayQuery($thinkerSQL, $wordlist, $day) {
		$daySQL = "-FLOOR(UNIX_TIMESTAMP(current_timestamp)/86400)+FLOOR(UNIX_TIMESTAMP(date)/86400)+29";

		// Query for keywords related to this thinker and query and count how many for each day
		if($wordlist) {
			$query = "SELECT k2.keyword as keyword, COUNT(t.thought_id) as cnt, $day as day " .
			         " FROM keywords k1, keywords k2, mentions m1, mentions m2, thoughts t " .
			         " WHERE k1.keyword IN ($wordlist) " .
			         "   AND k1.keyword_id = m1.keyword_id " .
			         "   AND k2.keyword_id = m2.keyword_id " .
		        	 "   AND t.thought_id = m1.thought_id " .
			         "   AND t.thought_id = m2.thought_id " .
			         "   AND $daySQL = $day " .
			         "   AND $thinkerSQL " .
			         " GROUP BY k2.keyword_id ".
			         " ORDER BY cnt DESC LIMIT 10 ";
		} else {
			$query = "SELECT k.keyword as keyword, COUNT(t.thought_id) as cnt, $day as day " .
			         " FROM keywords k, mentions m, thoughts t " .
			         " WHERE k.keyword_id = m.keyword_id " .
			       	 "   AND t.thought_id = m.thought_id " .
			         "   AND $daySQL = $day " .
			         "   AND $thinkerSQL " .
			         " GROUP BY k.keyword_id ".
			         " ORDER BY cnt DESC LIMIT 10 ";
		}
		return $query;
	}
}
