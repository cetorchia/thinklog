<?php

require_once(DOC_ROOT . "/classes/Login.php");
require_once(DOC_ROOT . "/classes/Thought.php");
require_once(DOC_ROOT . "/classes/ThinkerService.php");
require_once(DOC_ROOT . "/classes/ThoughtService.php");

//
// Responsible for handling user's requests to add, upload, or stream 
// thoughts from various sources.
//

class UploadRequest
{
	protected $isRequested;		// if we have anything to do

	// For uploading file
	protected $upload;
	protected $filename;

	// For uploading from URL
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
		$this->upload = isset($POST["upload"]) && ($POST["upload"] == "1");
		$this->filename = $serverRequest->getFile("file");

		// For uploading from URL
		$this->fromURL = isset($POST["fromURL"]) && ($POST["fromURL"] == "1")
		                 && preg_match('/^http\:\/\//',$POST["url"]);
		$this->url = isset($POST["url"]) ? $POST["url"] : null;

		// For adding one thought
		$this->add = isset($POST["add"]) && ($POST["add"] == "1");
		$this->body = isset($POST["body"]) ? $POST["body"] : "";
		$this->private = isset($POST["private"]) && ($POST["private"] == '1') ? '1' : '0';

		// For adding any thought
		$this->isRequested = $this->add || $this->upload || $this->fromURL;

		// Access to services
		$this->thinkerService = $services->thinkerService;
		$this->thoughtService = $services->thoughtService;
		$this->mentionsService = $services->mentionsService;

		$this->login = $login;
	}

	//
	// Sees if the POST-DATA is requesting to upload thoughts,
	// or add one thought. If it is, then try to upload them.
	//
	// This script must be run before any output occurs, because
	// it changes the HTTP header in order to refresh to the home
	// page.
	//

	function execute()
	{
		if($this->isRequested)
		{
			// Only post if we can verify the login thinker id and password!!
			if($this->thinkerService->verifyLogin($this->login)) {

				// Upload one thought
				if($this->add)
				{
					$this->doAdd();
				}

				// Upload thoughts from file
				else if($this->upload)
				{
					$this->doUpload();
				}

				// Upload thoughts from URL
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

	// Upload one thought from the POST-DATA

	function doAdd()
	{
		return($this->addThought($this->body,$this->private));
	}

	// Reads the file and loads the thoughts into the DB

	function doUpload()
	{
		$doc = new DOMDocument("1.0");
		$doc->load($this->filename);		// Load XML from the temp file
		return($this->addThoughts($doc));	// Parse the XML and add the thoughts
	}

	// Reads from the URL and loads the thoughts into the DB

	function doFromURL()
	{
		// Get XML from URL
		$dataLength = strlen($data);
		$opts = array("http" => array(
			"method"  => "GET",
			"header"  => "User-Agent: Thinklog\r\n",
		));
		$context = stream_context_create($opts);
		$xml = file_get_contents($this->url,false,$context);

		// Extract thoughts from response
		$doc = new DOMDocument();
		$doc->loadXML($xml);
		return($this->addThoughts($doc));				// Parse the XML and add the thoughts
	}

	//
	// Parses XML to add a series of thoughts
	// Input: a loaded DOMDocument object
	//
	function addThoughts($doc)
	{
		$itWorked = true;

		// Get the thoughts from the XML
			$thoughtElements = $doc->getElementsByTagName("item");
		foreach($thoughtElements as $thoughtElement)
		{
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
			}

			$itWorked = $itWorked && $this->addThought($body, $private);
		}

		return($itWorked);
	}

	// Asks ThoughtService to add the thought with this body/private-ness
	//
	// ** Aborts if any error is found.

	function addThought($body,$private)
	{
		// Try to add the thought
		if($body)
		{
			// Add a thot with the body (and current date) to the thinklog.

			if(strlen($body) > MAX_BODY_LENGTH)
			{
				header('Location: ./?think&tooLong');
				return(false);
			}

			else
			{
				/*
				 * **************** Add to database ***********
				 */

				$thought = new Thought();
				$thought->setThinkerId($this->login->getThinkerId());
				$thought->setBody($body);
				$thought->setPrivate(isset($private)?$private:false);

				if($this->thoughtService->add($thought))
				{
					$this->mentionsService->mentions($thought);	// Tag keywords
					return(true);
				}
			}
		}

		header('Location: ./?think&error');
		return(false);
	}
}
