<?php

require_once(DOC_ROOT . "/lib/geturl.php");
require_once(DOC_ROOT . "/classes/Login.php");
require_once(DOC_ROOT . "/classes/Thought.php");
require_once(DOC_ROOT . "/classes/ThinkerService.php");
require_once(DOC_ROOT . "/classes/ThoughtService.php");

//
// Responsible for handling user's requests to add, upload, or stream 
// thoughts from various sources.
//

class AddRequest
{
	protected $isRequested;		// if we have anything to do

	// For uploading file
	protected $fromFile;
	protected $filename;

	// For add thoughts in a URL
	protected $fromURL;
	protected $url;

	// For adding one thought
	protected $add;
	protected $body;
	protected $private;

	// Services
	protected $thinkerService;
	protected $thoughtService;
	protected $mentionsService;
	protected $login;

	// Get the request information

	public function __construct($serverRequest, $services, $login)
	{
		$POST = $serverRequest->getPOST();

		// For uploading from file
		$this->fromFile = isset($POST["fromFile"]) && ($POST["fromFile"] == "1");
		$this->filename = $serverRequest->getFile("file");

		// For retrieving from URL
		$this->fromURL = isset($POST["fromURL"]) && ($POST["fromURL"] == "1")
		                       && preg_match('/^http\:\/\//', $POST["url"]);
		$this->url = isset($POST["url"]) ? $POST["url"] : null;

		// For adding one thought
		$this->add = isset($POST["add"]) && ($POST["add"] == "1");
		$this->body = isset($POST["body"]) ? $POST["body"] : "";
		$this->private = isset($POST["private"]) && ($POST["private"] == '1') ? '1' : '0';

		// For adding thought in any way
		$this->isRequested = $this->add || $this->fromFile || $this->fromURL;

		// Access to services
		$this->thinkerService = $services->thinkerService;
		$this->thoughtService = $services->thoughtService;
		$this->mentionsService = $services->mentionsService;

		$this->login = $login;
	}

	//
	// Sees if the POST data is requesting to upload thoughts,
	// or add one thought. If it is, then try to add them.
	//
	// This script must be run before any output occurs, because
	// it changes the HTTP header in order to redirect to the home
	// page.
	//

	function execute()
	{
		if($this->isRequested)
		{
			// Only post if we can verify the login thinker id and password!!
			if($this->thinkerService->verifyLogin($this->login)) {

				// Add one thought
				if($this->add)
				{
					$this->doAdd();
				}

				// Upload thoughts from file
				else if($this->fromFile)
				{
					$this->doFromFile();
				}

				// Retrieve thoughts from URL
				else if($this->fromURL)
				{
					$this->doFromURL();
				}
			}

			else
			{
				header('Location: ./?think&notLogin');
				exit;
			}

			// It must have been successful if we are here.
			header('Location: ./?think&success');
			exit;
		}
	}

	// Add one thought from the POST data

	function doAdd()
	{
		return $this->addThought($this->body,$this->private);
	}

	// Reads the file and loads the thoughts into the DB

	function doFromFile()
	{
		$data = file_get_contents($this->filename);
		return $this->addThoughts($data);
	}

	// Retrieves thoughts from the URL and loads the thoughts into the DB

	function doFromURL()
	{
		$data = geturl($this->url, "Thinklog");
		return $this->addThoughts($data);
	}

	// Parses a thoughts file and adds the thoughts
	function addThoughts($data) {
		// Extract thoughts from response
		if (substr(trim($data),0,1) == '{') {
			// It's JSON
			return $this->addThoughtsFromJSON($data);
		} else {
			// Probably XML/RSS
			$doc = new DOMDocument();
			$doc->loadXML($data);
			return $this->addThoughtsFromXML($doc);  // Parse the XML and add the thoughts
		}
	}

	//
	// Parses JSON to add a series of thoughts
	// Input: plain text
	//
	function addThoughtsFromJSON($data) {
		$response = json_decode($data, true);
		$results = $response["results"];
		$itWorked = true;

		foreach ($results as $item) {
			$thinkerId = isset($item["from_user"]) ? $item["from_user"] : null;
			$body = isset($item["text"]) ? $item["text"] : null;
			$private = false;
			$itWorked = $itWorked && $this->addThought($body, $private, $thinkerId);
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
			}

			$itWorked = $itWorked && $this->addThought($body, $private, $thinkerId);
		}

		return $itWorked;
	}

	// Asks ThoughtService to add the thought with this body/private-ness
	//
	// ** Aborts if any error is found.

	function addThought($body,$private,$thinkerId=null)
	{
		// Try to add the thought
		if($body)
		{
			// Add a thot with the body (and current date) to the thinklog.

			if(strlen($body) > MAX_BODY_LENGTH)
			{
				header('Location: ./?think&tooLong');
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
				$thought->setPrivate(isset($private)?$private:false);

				if($this->thoughtService->add($thought))
				{
					$this->mentionsService->mentions($thought);	// Tag keywords
					return true;
				}
			}
		}

		header('Location: ./?think&error');
		return false;
	}
}