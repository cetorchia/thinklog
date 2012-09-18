<?php

//
// Responsible for querying for frequent keywords, and frequent
// keyword pairs.
//

class TagCloudService
{
	function __construct($services)
	{
	}

	//
	// Returns the most common keywords for the thinker and thought, or the
	// most common keywords in general if no thinker is given.
	//
	function getKeywords($thinkerId, $thoughtId)
	{
		// Query for keywords related to this thought and thinker
		if($thinkerId) {
			$query = "SELECT DISTINCT keyword " .
			         "FROM keywords, keyword_count, mentions, thoughts " .
			         "WHERE keywords.keyword_id = keyword_count.keyword_id " .
			         "  AND keywords.keyword_id = mentions.keyword_id " .
			         "  AND thoughts.thought_id = mentions.thought_id " .
			         "  AND thoughts.thinker_id = '$thinkerId' " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		else if($thoughtId) {
			$query = "SELECT DISTINCT keyword " .
			         "FROM keywords, keyword_count, mentions, thoughts " .
			         "WHERE keywords.keyword_id = keyword_count.keyword_id " .
			         "  AND keywords.keyword_id = mentions.keyword_id " .
			         "  AND thoughts.thought_id = mentions.thought_id " .
			         "  AND thoughts.thought_id = $thoughtId " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		else {
			$query = "SELECT DISTINCT keyword " .
			         "FROM keywords, keyword_count " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		$result = mysql_query($query);

		// Get the keywords
		$keywords = array();
		while ($row = mysql_fetch_array($result)) {
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
			$query = "SELECT DISTINCT k1.keyword, k2.keyword " .
			         "FROM keywords k1, keywords k2, keyword_pair_count, " .
			         "     mentions m1, mentions m2, thoughts t1, thoughts t2 " .
			         "WHERE k1.keyword_id = keyword_pair_count.keyword1 " .
			         "  AND k2.keyword_id = keyword_pair_count.keyword2 " .
			         "  AND k1.keyword_id = m1.keyword_id " .
			         "  AND k2.keyword_id = m2.keyword_id " .
			         "  AND t1.thought_id = m1.thought_id " .
			         "  AND t2.thought_id = m2.thought_id " .
			         "  AND t1.thinker_id = '$thinkerId' " .
			         "  AND t2.thinker_id = '$thinkerId' " .
			         "  AND k1.keyword < k2.keyword " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		else if($thoughtId) {
			$query = "SELECT DISTINCT k1.keyword, k2.keyword " .
			         "FROM keywords k1, keywords k2, keyword_pair_count, " .
			         "     mentions m1, mentions m2, thoughts t1, thoughts t2 " .
			         "WHERE k1.keyword_id = keyword_pair_count.keyword1 " .
			         "  AND k2.keyword_id = keyword_pair_count.keyword2 " .
			         "  AND k1.keyword_id = m1.keyword_id " .
			         "  AND k2.keyword_id = m2.keyword_id " .
			         "  AND t1.thought_id = m1.thought_id " .
			         "  AND t2.thought_id = m2.thought_id " .
			         "  AND t1.thought_id = $thoughtId " .
			         "  AND t2.thought_id = $thoughtId " .
			         "  AND k1.keyword < k2.keyword " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		else {
			$query = "SELECT DISTINCT k1.keyword, k2.keyword " .
			         "FROM keywords k1, keywords k2, keyword_pair_count " .
			         "WHERE k1.keyword_id = keyword_pair_count.keyword1 " .
			         "  AND k2.keyword_id = keyword_pair_count.keyword2 " .
			         "  AND k1.keyword < k2.keyword " .
			         "ORDER BY cnt DESC " .
			         "LIMIT 5 ";
		}
		$result = mysql_query($query);

		// Get the keyword pairs
		$keywordPairs = array();
		while ($row = mysql_fetch_array($result)) {
			$keywordPairs[$row["keyword1"]] = $row["keyword2"];
		}

		return($keywordPairs);
	}
}
