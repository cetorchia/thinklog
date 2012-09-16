<?php

require_once(DOC_ROOT . "/classes/Login.php");
require_once(DOC_ROOT . "/classes/Thinker.php");

class ThinkerService
{
	protected $table = "thinkers";		// Table name

	protected $thinkers = array();		// Thinker data cache

	protected $store;					// Access to RDF triple store

	public function __construct($services)
	{
		$this->store = $services->tripleStoreService->getStore();
	}

	//
	// This function returns an array with thinklog data
	// such as title, description, about me, etc.
	//

	function getThinker($thinkerId)
	{
		if(isset($thinkerId))
		{
			// See if it's in cache
			if(isset($this->thinkers[$thinkerId]))
			{
				return $this->thinkers[$thinkerId];
			}

			// Get other info from triple store
			$rows = $this->store->query(SPARQL_PREFIXES .
				"SELECT ?name ?about " .
				"WHERE { ".
				"  ?thinker thinklog:thinkerId \"$thinkerId\" ; " .
				"           thinklog:about ?about ; " .
				"           thinklog:name ?name . " .
				"}",
			"rows");

			if($rows && isset($rows[0]))
			{
				$row = $rows[0];
				$thinker = new Thinker();
				$thinker->setId($thinkerId);
				$thinker->setName($row['name']);
				$thinker->setAbout($row['about']);

				$this->thinkers[$thinkerId] = $thinker;

				return($thinker);
			}
		}

		return(null);
	}

	//
	// This function will verify whether the password of $thinkerId
	// is indeed $password. It is expected that $password is a SHA1 hash
	// of the plaintext password.
	//

	public function verifyLogin($login)
	{
		if(isset($login) && ($login->getThinkerId()))
		{
			$count = $this->getPasswordCount($login);

			if($count == 1) {
				return(true);
			}

			else {
				return(false);
			}
		}

		else
		{
			return(false);
		}
	}

	//
	// This function will return how many thinkers have
	// their thinker_id and password as those given.
	//

	protected function getPasswordCount($login)
	{
		$query="SELECT count(*) as cnt FROM " . $this->table . " " .
		       "WHERE thinker_id='" . mysql_real_escape_string($login->getThinkerId()) . "' " .
		       "AND password='" . mysql_real_escape_string($login->getPassword()) . "'";

		$result=mysql_query($query);

		if(!$result) {
			die("Error getting $thinkerId password count: ".mysql_error());
			return(false);
		}

		// There should be one row
		$row = mysql_fetch_array($result);
		return($row['cnt']);
	}

	//
	// The following function creates a thinker. Choose a strong password, give hash.
	//

	public function add($thinker,$password) 
	{
		$thinkerId = $thinker->getId();
		$name = $thinker->getName();
		$about = $thinker->getAbout();

		// Check that the user isn't already in the database
		if($this->getThinker($thinkerId) != null)
		{
			return(false);
		}

		// Put Id/password into database
		$query="INSERT INTO " . $this->table . " (thinker_id,password) " .
		       "VALUES ('" . mysql_real_escape_string($thinkerId) . "', " .
		               "'" . mysql_real_escape_string($password) . "'" .
		                ")";
		if(!mysql_query($query))
		{
			return(false);
		}

		// Insert user info into Thinklog triple store
		$query = SPARQL_PREFIXES .
			"INSERT INTO <" . THINKLOG_GRAPH . "> { " .
			"  _:thinker_$thinkerId a thinklog:Thinker ; " .
			"           thinklog:thinkerId \"$thinkerId\" ; " .
			"           thinklog:about \"$about\" ; " .
			"           thinklog:name \"$name\" . " .
			"}";
		if(!$this->store->query($query,"raw"))
		{
			echo "<pre>\n";
			var_dump($this->store->getErrors());
			echo "</pre>\n";
			return(false);
		}

		return(true);
	}
}
