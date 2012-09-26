<?php

//
// Methods for finding relevant thoughts in the database
//
// (c) 2010 Carlos E. Torchia
// Licensed under GNU GPL v2, see fsf.org, no warranty
//

class QueryService
{
	protected $thoughtService;
	protected $keywordService;

	protected $thoughtColumns0 = "thought_id, date, thinker_id, body, private";
	protected $thoughtColumns1 = "thought_id, UNIX_TIMESTAMP(date) AS date, thinker_id, content AS body, private";
	protected $thoughtColumns2 = "t2.thought_id, UNIX_TIMESTAMP(t2.date) AS date, t2.thinker_id, t2.content AS body, t2.private";

	//
	// We'll need a few services to get data from the database
	//

	public function __construct($services)
	{
		$this->thoughtService = $services->thoughtService;
		$this->keywordService = $services->keywordService;
	}

	//
	// Gets thoughts recommended for a thinker
	//

	public function getRecommended($login, $start, $num, $thinkerId)
	{
		$result = $this->getRecommendedByThinker($thinkerId, $start, $num);
		$thoughts = array();
		$this->retrieveVisibleThoughts($login, $num, $result, $thoughts);

		return $thoughts;
	}

	//
	// Gets articles related to the thought
	//

	public function getRelated($login, $start, $num, $thoughtId)
	{
		$result = $this->getRelatedByThought($thoughtId, $start, $num);
		$thoughts = array();
		$this->retrieveVisibleThoughts($login, $num, $result, $thoughts);

		return $thoughts;
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
		if(!$query) {
			// Get all thoughts if user specifies empty query string
			$result = $this->getAllThoughts($thinkerId, $start, $num);
		}

		else {
			$result = $this->getThoughtsByKeywords($thinkerId, $query, $start, $num);
		}

		$thoughts = array();
		$more = $this->retrieveVisibleThoughts($login, $num, $result, $thoughts);

		return $more;
	}

	//
	// Gets all thoughts
	// @return A mysql_query() result object
	//
	public function getAllThoughts($thinkerId, $start, $num)
	{
		// Query for all thoughts, starting at most recent
		$query = "SELECT $this->thoughtColumns1 " .
		         "FROM thoughts " .
		         (isset($thinkerId) ? "WHERE thinker_id = '$thinkerId' " : "") .
		         "ORDER BY date DESC LIMIT ".($num+1)." OFFSET $start ";
		return mysql_query($query);
	}

	//
	// Gets thoughts related to the query string
	// @return A mysql_query() result object
	//
	public function getThoughtsByKeywords($thinkerId, $query, $start, $num)
	{
		$words = $this->keywordService->getWords($query);
		$wordlist = $this->keywordService->getKeywordsSQL($words);
		$thinkerClause = (isset($thinkerId) ? "  AND thinker_id = '$thinkerId' " : "") .

		// Query for the thoughts related to the query's keywords
		// Sort by the number of the query's keywords that are in the thought and then by the number
		// of the query's keywords that are related to a keyword in the thought.
		$query = "SELECT $this->thoughtColumns0 " .
		         "FROM ( ".
		         "  (SELECT t.$this->thoughtColumns1, m.keyword_id AS mk, NULL AS rk, idf " .
		         "   FROM thoughts t, mentions m, keywords k, keyword_idf i " .
		         "   WHERE t.thought_id = m.thought_id " .
		         "     AND i.keyword_id = m.keyword_id " .
		         "     AND m.keyword_id = k.keyword_id " .
		         "     AND k.keyword IN ($wordlist) " .
		         "     $thinkerClause) " .
		         "  UNION " .
		         "  (SELECT t.$this->thoughtColumns1, NULL AS mk, r.keyword2 AS rk, idf " .
		         "   FROM thoughts t, mentions m, related_keywords r, keywords k, keyword_idf i " .
		         "   WHERE t.thought_id = m.thought_id AND m.keyword_id = r.keyword1 " .
		         "     AND i.keyword_id = r.keyword1 " .
		         "     AND k.keyword_id = r.keyword2 " .
		         "     AND k.keyword IN ($wordlist) " .
		         "     $thinkerClause)) tbl " .
		         "GROUP BY thought_id " .
		         "ORDER BY (COUNT(DISTINCT mk) + COUNT(DISTINCT rk))*AVG(idf) DESC " .
		         "LIMIT ".($num+1)." OFFSET $start ";

		return mysql_query($query);
	}

	//
	// Gets thoughts related to the thought with this Id
	// @return A mysql_query() result object
	//
	public function getRelatedByThought($thoughtId, $start, $num)
	{
		// Query for the thoughts related to this thought's keywords
		// Sort by the number of the keywords of this thought that are in the related thought
                // and then by the number keywords of this thought that are related to a keyword in the
		// related thought.
		$query = "SELECT $this->thoughtColumns0 " .
		         "FROM ( ".
		         "  (SELECT t.$this->thoughtColumns1, m1.keyword_id AS mk, NULL AS rk, idf " .
		         "   FROM thoughts t, mentions m1, mentions m2, keyword_idf i " .
		         "   WHERE m1.thought_id = $thoughtId " .
		         "     AND m1.keyword_id = m2.keyword_id " .
		         "     AND t.thought_id = m2.thought_id " .
		         "     AND t.thought_id <> m1.thought_id " .
		         "     AND i.keyword_id = m2.keyword_id) " .
		         "  UNION " .
		         "  (SELECT t.$this->thoughtColumns1, NULL AS mk, r.keyword1 AS rk, idf " .
		         "   FROM thoughts t, mentions m1, mentions m2, related_keywords r, keyword_idf i " .
		         "   WHERE m1.thought_id = $thoughtId " .
		         "     AND m1.keyword_id = r.keyword1 " .
		         "     AND m2.keyword_id = r.keyword2 " .
		         "     AND m2.thought_id = t.thought_id " .
		         "     AND m1.thought_id <> t.thought_id " .
		         "     AND i.keyword_id = m2.keyword_id)) tbl " .
		         "GROUP BY thought_id " .
		         "ORDER BY (COUNT(DISTINCT mk) + COUNT(DISTINCT rk))*AVG(idf) DESC " .
		         "LIMIT ".($num+1)." OFFSET $start ";
		return mysql_query($query);
	}

	//
	// Gets thoughts recommended for this thinker
	// @return A mysql_query() result object
	//
	public function getRecommendedByThinker($thinkerId, $start, $num)
	{
		// Get thoughts that are by other thinkers that have the same keywords as or
		// related keywords to thoughts of this thinker.
		$query = "SELECT $this->thoughtColumns0 " .
		         "FROM ( " .
		         "  (SELECT $this->thoughtColumns2, m1.keyword_id AS mk, NULL AS rk, idf " .
		         "   FROM thoughts t1, mentions m1, thoughts t2, mentions m2, keyword_idf i " .
		         "   WHERE t1.thinker_id = '$thinkerId' AND t1.thought_id = m1.thought_id " .
		         "     AND t1.thinker_id <> t2.thinker_id AND t2.thought_id = m2.thought_id " .
		         "     AND m1.keyword_id = m2.keyword_id " .
		         "     AND i.keyword_id = m2.keyword_id) " .
		         "  UNION " .
		         "  (SELECT $this->thoughtColumns2, NULL AS mk, r.keyword1 AS rk, idf " .
		         "   FROM thoughts t1, mentions m1, thoughts t2, mentions m2, related_keywords r, keyword_idf i " .
		         "   WHERE t1.thinker_id = '$thinkerId' AND t1.thought_id = m1.thought_id " .
		         "     AND t1.thinker_id <> t2.thinker_id AND t2.thought_id = m2.thought_id " .
		         "     AND m1.keyword_id = r.keyword1 AND m2.keyword_id = r.keyword2 " .
		         "     AND i.keyword_id = m2.keyword_id)) tbl " .
		         "GROUP BY thought_id " .
		         "ORDER BY (COUNT(DISTINCT mk) + COUNT(DISTINCT rk))*AVG(idf) DESC " .
		         "LIMIT ".($num+1)." OFFSET $start ";
		return mysql_query($query);
	}

	//
	// Leave only visible thoughts.
	// Stores them in $thoughts, returns whether there are more after these
	//

	public function retrieveVisibleThoughts($login, $num, $result, &$thoughts)
	{
		$n = 0;
		while ($row = mysql_fetch_array($result)) { 
			$thought = $this->thoughtService->getFromRow($row);

			// Don't go over limit
			$n = $n + 1;
			if($n > $num) {
				return true;
			}

			// Add the thought to the collection if permissible
			if(isset($thought) && $this->thoughtService->getReadPermission($login, $thought))
			{
				// Add the thought to our results
				$thoughts[] = $thought;
			}

		}

		return false;
	}
}
