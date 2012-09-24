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

	protected $thoughtColumns = "thought_id, UNIX_TIMESTAMP(date) AS date, thinker_id, content AS body, private";
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
	public function getAllThoughts($thinkerId, $start,$num)
	{
		// Query for all thoughts, starting at most recent
		$query = "SELECT $this->thoughtColumns " .
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

		// Query for the thoughts related to the query's keywords
		// Sort by the number of the query's keywords that are in the thought and then the number
		// of the query's keywords that are related to a keyword in the thought.
		$query = "SELECT t.$this->thoughtColumns " .
		         "FROM (thoughts t, mentions m, keywords mk) LEFT JOIN (related_keywords r, keywords rk) ON (1 = 1) " .
		         "WHERE t.thought_id = m.thought_id " .
		         "  AND m.keyword_id = mk.keyword_id " .
		         "  AND (r.keyword2 IS NULL OR r.keyword2 = rk.keyword_id) " .
		         "  AND (mk.keyword IN (" . $this->keywordService->getKeywordsSQL($words) .") " .
		         "       OR (m.keyword_id = r.keyword1 AND " .
		         "           rk.keyword IN (" .
		         $this->keywordService->getKeywordsSQL($words) ."))) " .
		         (isset($thinkerId) ? "  AND thinker_id = '$thinkerId' " : "") .
		         "GROUP BY t.thought_id " .
		         "ORDER BY COUNT(DISTINCT mk.keyword_id) DESC, COUNT(DISTINCT rk.keyword_id) DESC " .
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
		$query = "SELECT t.$this->thoughtColumns " .
		         "FROM (thoughts t, mentions tm, mentions m) LEFT JOIN (related_keywords r) ON (1 = 1) " .
		         "WHERE m.thought_id = $thoughtId " .
		         "  AND t.thought_id = tm.thought_id " .
		         "  AND (tm.keyword_id = m.keyword_id " .
		         "       OR (tm.keyword_id = r.keyword1 " .
		         "           AND r.keyword2 = m.keyword_id)) " .
		         "  AND m.thought_id <> t.thought_id " .
		         "GROUP BY t.thought_id " .
		         "ORDER BY COUNT(DISTINCT m.keyword_id) DESC, COUNT(DISTINCT r.keyword2) DESC " .
		         "LIMIT ".($num+1)." OFFSET $start ";
		return mysql_query($query);
	}

	//
	// Gets thoughts recommended for this thinker
	// @return A mysql_query() result object
	//
	public function getRecommendedByThinker($thinkerId, $start, $num)
	{
		// TODO: none of these queries will work if there are any related keyword pairs
		$query = "SELECT $this->thoughtColumns2 " .
		         "FROM thoughts t1, mentions m1, thoughts t2, mentions m2 " .
		         "WHERE t1.thinker_id = '$thinkerId' AND t1.thought_id = m1.thought_id " .
		         "  AND t1.thinker_id <> t2.thinker_id AND t2.thought_id = m2.thought_id " .
		         "  AND m1.keyword_id = m2.keyword_id " .
		         "GROUP BY t2.thought_id " .
		         "ORDER BY COUNT(DISTINCT m1.keyword_id) DESC " .
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

			// Add the thought to the collection if permissible
			if(isset($thought) && $this->thoughtService->getReadPermission($login, $thought))
			{
				// Don't go over limit
				$n = $n + 1;
				if($n > $num) {
					return true;
				}

				// Add the thought to our results
				$thoughts[] = $thought;
			}

		}

		return false;
	}
}
