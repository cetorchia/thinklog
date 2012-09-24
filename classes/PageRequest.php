<?php

require_once(DOC_ROOT . "/pages/RecommendedPage.php");
require_once(DOC_ROOT . "/pages/ThoughtPage.php");
require_once(DOC_ROOT . "/pages/ResultsPage.php");
require_once(DOC_ROOT . "/pages/QueryPage.php");
require_once(DOC_ROOT . "/pages/SignupPage.php");
require_once(DOC_ROOT . "/pages/AddPage.php");
require_once(DOC_ROOT . "/pages/LoginPage.php");

/**
 * Takes a server request and decides what page to load.
 */

class PageRequest
{
	protected $thinkerId;
	protected $thought;
	protected $query;
	protected $signup;
	protected $isLogin;

	protected $serverRequest;
	protected $services;

	protected $login;

	public function __construct($serverRequest, $services, $login)
	{
		$GET = $serverRequest->getGET();
		$this->thinkerId = isset($GET['thinker']) ? $GET['thinker'] : null;
		$this->thought = isset($GET['id']);
		$this->query = isset($GET['q']);
		$this->queryPage = isset($GET['query']);
		$this->signup = isset($GET['signup']);
		$this->addPage = isset($GET['add']);
		$this->isLogin = isset($GET['login']);

		$this->serverRequest = $serverRequest;
		$this->services = $services;

		$this->login = $login;
	}

	//
	// This function prints the page requested for the given login,
	// in HTML output.
	//

	public function execute()
	{
		//
		// Determine what kind of page this is requested.
		//

		if($this->query)
		{
			$page = new ResultsPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->thought)
		{
			$page = new ThoughtPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->signup)
		{
			$page = new SignupPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->isLogin)
		{
			$page = new LoginPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->addPage)
		{
			$page = new AddPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->queryPage)
		{
			$page = new QueryPage($this->serverRequest, $this->services, $this->login);
		}

		else if($this->thinkerId)
		{
			$page = new RecommendedPage($this->serverRequest, $this->services, $this->login);
		}

		// If no particular page is specified, but the user is logged in,
		// then give the user's personalized page.
		else if(isset($this->login))
		{
			$page = new RecommendedPage($this->serverRequest, $this->services, $this->login);
		}

		// If the user is not logged in and page is specified, then show all thoughts.
		else
		{
			$page = new ResultsPage($this->serverRequest, $this->services, $this->login);
		}

		// Draw the page
		$page->draw();
	}
}
