<?php

require_once(DOC_ROOT . "/classes/Login.php");
require_once(DOC_ROOT . "/classes/Thought.php");

class ThoughtService
{
	// Thought cache
	protected $thoughts = array();
	protected $table = "thoughts";
	protected $columns = "thought_id, thinker_id, twitter_id, content AS body, private, UNIX_TIMESTAMP(date) AS date";

	public function __construct(&$services)
	{
	}

	//
	// ---------------------- Getting of thoughts ---------
	//

	//
	// A function that returns an associative array of the thought's info
	//

	public function getThought($id)
	{
		if($id)
		{
			if(isset($this->thoughts[$id]))
			{
				return $this->thoughts[$id];
			}

			// Get the thought from the database
			$query = "SELECT $this->columns " .
			         "FROM $this->table " .
			         "WHERE thought_id = $id ";
			$result = mysql_query($query);
			if (!$result) {
				return null;
			}

			$row = mysql_fetch_array($result);
			if (!$row) {
				return null;
			}

			$thought = $this->getFromRow($row);

			$this->thoughts[$id] = $thought;
			return $thought;
		}

		return null;
	}

	//
	// Returns the newest thought of $thinkerid that the $login's allowed to see.
	//

	public function getNewest($login,$thinkerId)
	{
		$secure = (isset($login) && isset($thinkerId) && ($login->getThinkerId() == $thinkerId));
		$owner = isset($thinkerId);

		// Get newest thought from database that the logged in thinker is
		// allowed to see.
		$query = "SELECT $this->columns FROM $this->table " .
		         "WHERE " .
		         ($owner ? "thinker_id = '$thinkerId' " : " ") .
		         (!$secure ? "AND NOT private " :
		                     "AND ((NOT private) OR thinker_id='". mysql_real_escape_string($thinkerId) . "') ") .
		         "ORDER BY date DESC " .
		         "LIMIT 1";
		$result = mysql_query($query);
		if(!$result) {
			return null;
		}

		$row = mysql_fetch_array($result);
		if (!$row) {
			return null;
		}

		$thought = $this->getFromRow($row);

		$this->thoughts[$thought->getId()] = $thought;
		return($thought);
	}

	//
	// ------------------------ thought manipulation -----------------
	//

	public function add(&$thought)
	{
		$columns = "thinker_id, content, private";
		if ($thought->getTwitterId()) {
			$columns .= ", twitter_id";
		}

		// Add the thought to the database
		$query = "INSERT INTO $this->table ($columns) " .
		         "VALUES ( " .
		         "'" . mysql_real_escape_string($thought->getThinkerId()) . "', " .
		         "'" . mysql_real_escape_string($thought->getBody()) . "', " .
		         mysql_real_escape_string($thought->getPrivate() ? "1" : "0");
		if ($thought->getTwitterId()) {
			$query .= ", '".$thought->getTwitterId()."'";
		}
		$query .= ")";
		$result = mysql_query($query); 
		if($result) {
			$thought->setId(mysql_insert_id());	// Get the ID!
		} else {
			return(false);
		}

		return(true);
	}

	//
	// This function returns whether the logged in
	// user has permission to see the given thought.
	// To do: make this verify the logged in user.
	//

	function getReadPermission($login, $thought)
	{
		if((!$thought->getPrivate()) ||
		   (isset($login) && ($login->getThinkerId() == $thought->getThinkerId())))
		{
			return(true);
		}

		else
		{
			return(false);
		}
	}

	function getFromRow($row)
	{
		$thought = new Thought();
		$thought->setId($row['thought_id']);
		$thought->setThinkerId($row['thinker_id']);
		$thought->setTwitterId($row['twitter_id']);
		$thought->setBody($row['body']);
		$thought->setPrivate($row['private'] ? true : false);
		$thought->setDate($row['date']);

		return($thought);
	}
}
