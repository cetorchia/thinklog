<?php

require_once(DOC_ROOT . "/lib/geturl.php");
require_once(DOC_ROOT . "/classes/Thought.php");

//
// Responsible for adding, uploading, or streaming thoughts from various sources.
//

class AddService
{
	// Error statuses
	public $error;
	public $tooLong;
	public $noThoughts;
	public $invalidURL;

	// Services
	protected $thoughtService;
	protected $mentionsService;
	protected $login;

	public function __construct(&$services)
	{
		// Access to services
		$this->thoughtService = $services->thoughtService;
		$this->mentionsService = $services->mentionsService;

		$this->error = $this->tooLong = $this->noThoughts = $this->invalidURL = false;
	}

	// Set the login data so we know the thinker's ID

	function setLogin($login)
	{
		$this->login = $login;
	}

	// Reads the file and loads the thoughts into the DB

	function addThoughtsFromFile($filename)
	{
		$data = file_get_contents($filename);
		return $this->addThoughts($data);
	}

	// Retrieves thoughts from the URL and loads the thoughts into the DB

	function addThoughtsFromURL($url)
	{
		if (!preg_match('/^http\:\/\//', $url)) {
			$this->invalidURL = true;
			return false;
		}
		$data = geturl($url, "Thinklog");
		return $this->addThoughts($data);
	}

	// Retrieves thoughts from the URL and loads the thoughts into the DB

	function addThoughtsFromTwitter($twitterQuery)
	{
		$url = TWITTER_SEARCH_API . "?q=" . urlencode($twitterQuery);
		$data = geturl($url);
		return $this->addThoughts($data);
	}

	// Add thoughts specified in the given data string (JSON or XML/RSS)
	function addThoughts($data) {
		// Extract thoughts from response
		if (!$data) {
			$this->noThoughts = true;
			return false;
		} else if (substr(trim($data),0,1) == '{') {
			// It's JSON
			return $this->addThoughtsFromJSON($data);
		} else {
			// Probably XML/RSS
			$doc = new DOMDocument();
			$doc->loadXML($data);
			return $this->addThoughtsFromXML($doc);
		}
	}

	//
	// Parses JSON to add a series of thoughts
	// Input: plain text
	//
	function addThoughtsFromJSON($data) {
		$response = json_decode($data, true);

		if (isset($response["error"]) && $response["error"] == "You must enter a query.") {
			$this->noThoughts = true;
			return false;
		} else if (isset($response["error"]) || isset($response["errors"])) {
			$this->error = true;
			return false;
		}

		$results = $response["results"];
		$itWorked = true;

		foreach ($results as $item) {
			$thinkerId = isset($item["from_user"]) ? $item["from_user"] : null;
			$body = isset($item["text"]) ? $item["text"] : null;
			$private = false;
			$twitterId = isset($item["id_str"]) ? $item["id_str"] : null;
			$date = isset($item["created_at"]) ? strtotime($item["created_at"]) : null;
			$itWorked = $itWorked && $this->addThought($body, $private, $thinkerId, $twitterId, $date);
		}

		return $itWorked;
	}

	//
	// Parses XML to add a series of thoughts
	// Input: a loaded DOMDocument object
	//
	function addThoughtsFromXML($doc)
	{
		$itWorked = true;

		// Get the thoughts from the XML
		$thoughtElements = $doc->getElementsByTagName("item");
		foreach($thoughtElements as $thoughtElement)
		{
			$body = null;
			$private = false;
			$thinkerId = null;
			$date = null;
			foreach($thoughtElement->childNodes as $child)
			{
				if(($child->nodeName == "body") ||
				   ($child->nodeName == "description") ||
				   ($child->nodeName == "content"))
				{
					$body = htmlspecialchars_decode(strip_tags($child->textContent));
				}
				else if($child->nodeName == "private")
				{
					$private = ($child->nodeValue == "0") ? false : true;
				}
				else if($child->nodeName == "author")
				{
					$thinkerId = htmlspecialchars_decode(strip_tags($child->textContent));
				}
				else if(($child->nodeName == "date") || ($child->nodeName == "pubDate"))
				{
					$date = strtotime($child->textContent);
				}
			}

			$itWorked = $itWorked && $this->addThought($body, $private, $thinkerId, null, $date);
		}

		return $itWorked;
	}

	// Asks ThoughtService to add the thought with this body/private-ness
	//
	// ** Aborts if any error arises

	function addThought($body, $private, $thinkerId=null, $twitterId=null, $date=null)
	{
		// Try to add the thought
		if($body)
		{
			// Add a thot with the body (and current date) to the thinklog.

			if(strlen($body) > MAX_BODY_LENGTH && !$twitterId)
			{
				$this->tooLong = true;
				return false;
			}

			else
			{
				/*
				 * **************** Add to database ***********
				 */

				$thought = new Thought();
				$thought->setThinkerId($thinkerId ? $thinkerId :
				                                    $this->login->getThinkerId());
				$thought->setBody($body);
				if ($twitterId) {
					$thought->setTwitterId($twitterId);
				}
				$thought->setPrivate(isset($private)?$private:false);
				if (isset($date)) {
					$thought->setDate($date);
				}

				if($this->thoughtService->add($thought))
				{
					$this->mentionsService->mentions($thought);	// Tag keywords
					return true;
				}
			}
		} else {
			$this->noThoughts = true;
			return false;
		}

		$this->error = true;
		return false;
	}
}
