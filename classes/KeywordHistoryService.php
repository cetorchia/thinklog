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
		if ($query) {
			$words = $this->keywordService->getWords($query);
			$wordlist = $this->keywordService->getKeywordsSQL($words);
		} else {
			$wordlist = null;
		}
		$thinkerSQL = $thinkerId ? "t.thinker_id = '$thinkerId'" : "TRUE";

		$keywordHistory = array();
		for ($day = 0; $day < 30; $day++) {
			// Get the keyword history rows
			$result = $this->_queryDay($thinkerSQL, $wordlist, $day);
			while ($row = mysql_fetch_array($result)) {
				$keyword = $row["keyword"];
				$count = (int)$row["cnt"];
				if (!isset($keywordHistory[$keyword])) {
					$keywordHistory[$keyword] = array_fill(0, 30, 0);
				}
				$keywordHistory[$keyword][$day] = $count;
			}
		}

		return $keywordHistory;
	}

	// Returns the MySQL query result for given thinker, query, and day
	protected function _queryDay($thinkerSQL, $wordlist, $day) {
		$daySQL = "-FLOOR(UNIX_TIMESTAMP(current_timestamp)/86400)+FLOOR(UNIX_TIMESTAMP(date)/86400)+30";

		// Query for keywords related to this thinker and query and count how many for each day
		if($wordlist) {
			$query = "SELECT k2.keyword as keyword, $daySQL as day, COUNT(t.thought_id) as cnt " .
			         " FROM keywords k1, keywords k2, mentions m1, mentions m2, thoughts t " .
			         " WHERE k1.keyword IN ($wordlist) " .
			         "   AND k1.keyword_id = m1.keyword_id " .
			         "   AND k2.keyword_id = m2.keyword_id " .
		        	 "   AND t.thought_id = m1.thought_id " .
			         "   AND t.thought_id = m2.thought_id " .
			         "   AND $thinkerSQL " .
			         " GROUP BY k2.keyword_id HAVING day = $day ".
			         " ORDER BY cnt DESC LIMIT 10 ";
		} else {
			$query = "SELECT k.keyword as keyword, $daySQL as day, COUNT(t.thought_id) as cnt " .
			         " FROM keywords k, mentions m, thoughts t " .
			         " WHERE k.keyword_id = m.keyword_id " .
			       	 "   AND t.thought_id = m.thought_id " .
			         "   AND $thinkerSQL " .
			         " GROUP BY k.keyword_id HAVING day = $day ".
			         " ORDER BY cnt DESC LIMIT 10 ";
		}
		return mysql_query($query);
	}
}
