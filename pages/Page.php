<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/HeaderSection.php");
require_once(DOC_ROOT . "/pages/MessagesSection.php");
require_once(DOC_ROOT . "/pages/MenuSection.php");
require_once(DOC_ROOT . "/pages/ThinkerSection.php");

abstract class Page
{
	protected $thinkerId;

	protected $serverRequest;
	protected $services;
	protected $login;

	// Take in the request and services
	public function __construct($serverRequest, $services, $login)
	{
		$this->serverRequest = $serverRequest;
		$this->services = $services;
		$this->login = $login;
	}

	// Override this to generate the page's content, given the logged in user, if any.
	abstract function getContent();

	//
	// This function prints the page requested for the given login,
	// in HTML output.
	//

	public function draw()
	{
		// Initialize section objects we might need
		$headerSection = new HeaderSection($this->serverRequest, $this->services, $this->login);
		$messagesSection = new MessagesSection($this->serverRequest, $this->services, $this->login);
		$menuSection = new MenuSection($this->serverRequest, $this->services, $this->login);
		$thinkerSection = new ThinkerSection($this->serverRequest, $this->services, $this->login);

		// Get the context for this page
		$GET = $this->serverRequest->getGET();

		//
		// Write an HTML document.
		//

		header("Content-Type: text/html; charset=utf-8");
		header("Expires: 0");

		//
		// Lay out the initial HTML.
		//

		$html = new Html();

		$head = new Head();
		$head->addContent(new Meta("http-equiv", "Expires", "0"));
		$head->addContent(new Title("Thinklog"));
		//$head->addContent(new Link("icon", "image/png", "images/icon.png"));
		$head->addContent(new Link("StyleSheet", "text/css", "style.css"));
		$html->addContent($head);

		//
		// Start the body off
		//

		$body = new Body();

		// Add the header, menu, and messages
		$body->addContent($headerSection->draw());
		$body->addContent($menuSection->draw());
		$body->addContent($messagesSection->draw());

		// Add the overidden content
		$content = new Div();
		$content->set("id", "content");
		$content->addContent($this->getContent());
		$body->addContent($content);

		// Add an about section if this is specific to a thinker.
		if(isset($GET["id"]) || isset($GET["thinker"]))
		{
			$body->addContent($thinkerSection->draw());
		}

		$html->addContent($body);

		echo $html;
	}
}
