<?php

require_once(DOC_ROOT . "/classes/Login.php");
require_once(DOC_ROOT . "/classes/Thought.php");

class ThoughtService
{
	// Thought cache
	protected $thoughts = array();

	protected $store;

	public function __construct($services)
	{
		$this->store = $services->tripleStoreService->getStore();
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

			// Get the thought from the triple store
			$query = SPARQL_PREFIXES .
				"SELECT ?thoughtId ?thinkerId ?content ?private ?date " .
				"WHERE { " .
				"  ?thought thinklog:thoughtId \"$id\" ; " .
				"           thinklog:thoughtId ?thoughtId ; " .
				"           thinklog:author ?thinker ; " .
				"           thinklog:content ?content ; " .
				"           thinklog:private ?private ; " .
				"           thinklog:date ?date . " .
				"  ?thinker thinklog:thinkerId ?thinkerId . " .
				"}";
			$rows = $this->store->query($query, "rows");

			if($rows && isset($rows[0]))
			{
				$thought = $this->getFromRow($rows[0]);

				$this->thoughts[$id] = $thought;
				return($thought);
			}
		}

		return(null);
	}

	//
	// Returns the newest thought of $thinkerid that the $login's allowed to see.
	//

	public function getNewest($login,$thinkerId)
	{
		$secure = (isset($login) && isset($thinkerId) && ($login->getThinkerId() == $thinkerId));
		$owner = isset($thinkerId);

		// Get newest thought from triple store that the logged in thinker is
		// allowed to see.

		// Get newest date
		$query = SPARQL_PREFIXES .
			"SELECT MAX(?date) AS ?max " .
			"WHERE { " .
			"  ?thought thinklog:date ?date ; " .
			"           thinklog:author ?thinker " .
			( $secure ?
				"" :
				";      thinklog:private \"0\" "
			) .
			" . " .
			"?thinker a thinklog:Thinker " .
			( $owner ?
				";    thinklog:thinkerId \"$thinkerId\" " :
				""
			) .
			" . " .
			"}";
		$rows = $this->store->query($query, "rows");
		if(!($rows && isset($rows[0])))
		{
			return(null);
		}
		$date = $rows[0]['max'];

		// Get the thought with that date

		$query = SPARQL_PREFIXES .
			"SELECT ?thoughtId ?thinkerId ?content ?private ?date " .
			"WHERE { " .
			"  ?thought a thinklog:Thought ; " .
			"           thinklog:date ?date ; " .
			"           thinklog:author ?thinker ; " .
			"           thinklog:content ?content ; " .
			"           thinklog:thoughtId ?thoughtId ; " .
			( $secure ?
				"" :
				"       thinklog:private \"0\" ; "
			) .
			"           thinklog:private ?private . " .
			"  ?thinker a thinklog:Thinker ; " .
			( $owner ?
				"       thinklog:thinkerId \"$thinkerId\" ; " :
				""
			) .
			"           thinklog:thinkerId ?thinkerId . " .
			"  FILTER (?date >= \"$date\"). " .
			"}";
		$rows = $this->store->query($query, "rows");
		if(!($rows && isset($rows[0])))
		{
			return(null);
		}
		$thought = $this->getFromRow($rows[0]);

		$this->thoughts[$thought->getId()] = $thought;

		return($thought);
	}

	//
	// ------------------------ thought manipulation -----------------
	//

	//
	// This function adds a thought to the thinklog with title $title,
	// author $thinkerId, and body as being $body.
	// $private is true if private, false if not. 
	//

	public function add(&$thought)
	{
		// Find the next thought Id
		$query = SPARQL_PREFIXES .
			"SELECT MAX(?thoughtId) AS ?max " .
			"WHERE { " .
			"  ?thought thinklog:thoughtId ?thoughtId ; " .
			"}";
		$rows = $this->store->query($query, "rows");
		if(!($rows && isset($rows[0])))
		{
			$max = 0;
		}
		else
		{
			$max = $rows[0]['max'];
		}

		// Construct the thought
		$thought->setId($thoughtId = $max + 1);
		$thinkerId = $thought->getThinkerId();
		$thought->setDate($date = date('U'));
		$body = $thought->getBody();
		$private = $thought->getPrivate() ? "1" : "0";

		// Add the thought to the triple store
		$query = SPARQL_PREFIXES .
			"INSERT INTO <" . THINKLOG_GRAPH . "> CONSTRUCT { " .
			"  _:thought_$thoughtId a thinklog:Thought ; " .
			"                       thinklog:thoughtId \"$thoughtId\" ; " .
			"                       thinklog:author ?thinker ; " .
			"                       thinklog:date \"$date\" ; " .
			"                       thinklog:content \"$body\" ; " .
			"                       thinklog:private \"$private\" . " .
			"} " .
			"WHERE { " .
			"  ?thinker thinklog:thinkerId \"$thinkerId\" . " .
			"} ";

		$result = $this->store->query($query, "raw");
		if(($errs = $this->store->getErrors()))
		{
			return(false);
		}

		return($result ? true : false);
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
		$thought->setId($row['thoughtId']);
		$thought->setThinkerId($row['thinkerId']);
		$thought->setBody($row['content']);
		$thought->setPrivate($row['private'] ? true : false);
		$thought->setDate($row['date']);

		return($thought);
	}
}
