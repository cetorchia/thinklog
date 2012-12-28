<?php

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

	// For add thoughts in Twitter search results
	protected $fromTwitter;
	protected $twitterQuery;

	// For adding one thought
	protected $add;
	protected $body;
	protected $private;

	// Services
	protected $thinkerService;
	protected $addService;
	protected $login;

	// Get the request information

	public function __construct(&$serverRequest, &$services, &$login)
	{
		$POST = $serverRequest->getPOST();

		// For uploading from file
		$this->fromFile = isset($POST["fromFile"]) && ($POST["fromFile"] == "1");
		$this->filename = $serverRequest->getFile("file");

		// For retrieving from URL
		$this->fromURL = isset($POST["fromURL"]) && ($POST["fromURL"] == "1");
		$this->url = isset($POST["url"]) ? $POST["url"] : null;

		// For retrieving from Twitter
		$this->fromTwitter = isset($POST["fromTwitter"]) && ($POST["fromTwitter"] == "1");
		$this->twitterQuery = isset($POST["twitterQuery"]) ? $POST["twitterQuery"] : null;

		// For adding one thought
		$this->add = isset($POST["add"]) && ($POST["add"] == "1");
		$this->body = isset($POST["body"]) ? $POST["body"] : "";
		$this->private = isset($POST["private"]) && ($POST["private"] == '1') ? true : false;

		// For adding thought in any way
		$this->isRequested = $this->add || $this->fromFile || $this->fromURL
		                                || $this->fromTwitter;

		// Access to services
		$this->thinkerService = $services->thinkerService;
		$this->addService = $services->addService;

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
		$params = "think";	// Parameters of page to redirect to
		$success = true;	// So far so good
		if($this->isRequested)
		{
			// Only post if we can verify the login thinker id and password!!
			if($this->thinkerService->verifyLogin($this->login))
			{
				$this->addService->setLogin($this->login);

				// Add one thought
				if($this->add)
				{
					$this->addService->addThought($this->body, $this->private);
					$params .= "&body=". urlencode(substr($this->body, 0, 512));
					if ($this->private) {
						$params .= "&private=1";
					}
				}

				// Upload thoughts from file
				else if($this->fromFile)
				{
					$this->addService->addThoughtsFromFile($this->filename);
				}

				// Retrieve thoughts from URL
				else if($this->fromURL)
				{
					$this->addService->addThoughtsFromURL($this->url);
					$params .= "&url=" . urlencode($this->url);
				}

				// Retrieve thoughts from URL
				else if($this->fromTwitter)
				{
					$this->addService->addThoughtsFromTwitter($this->twitterQuery);
					$params .= "&twitterQuery=" . urlencode($this->twitterQuery);
				}

				//
				// Check for errors
				//
				if ($this->addService->tooLong) {
					$params .= "&tooLong";
					$success = false;
				}
				if ($this->addService->error) {
					$params .= "&error";
					$success = false;
				}
				if ($this->addService->duplicate) {
					$params .= "&duplicate";
					$success = false;
				}
				if ($this->addService->noThoughts) {
					$params .= "&noThoughts";
					$success = false;
				}
				if ($this->addService->invalidURL) {
					$params .= "&invalidURL";
					$success = false;
				}
			}

			else
			{
				$params .= "&notLogin";
				$success = false;
			}

			if ($success) {
				$params = "think&success";
			} else {
				// Give them another chance
				$params = "add&" . $params;
			}

			header("Location: ./?$params");
			exit;
		}
	}
}
