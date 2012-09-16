<?php

require_once(DOC_ROOT . "/classes/RSSRequest.php");
require_once(DOC_ROOT . "/classes/PageRequest.php");

class DocumentRequest
{
	protected $output;

	protected $serverRequest;
	protected $services;

	protected $login;

	public function __construct($serverRequest, $services, $login)
	{
		$GET = $serverRequest->getGET();
		$this->output = isset($GET["output"]) ? $GET["output"] : "html";

		$this->serverRequest = $serverRequest;
		$this->services = $services;

		$this->login = $login;
	}

	//
	// This function prints the page requested for the given login,
	// in HTML output or RSS output based on what is specified in $this->output.
	//

	public function execute()
	{
		//
		// Decide on what content to retrieve and how.
		//

		switch($this->output)
		{
			case "rss":
				$req = new RSSRequest($this->serverRequest, $this->services, $this->login);
				break;

			case "html":
			default:
				$req = new PageRequest($this->serverRequest, $this->services, $this->login);
				break;
		}

		// Execute the request for the content.
		$req->execute();
	}
}
