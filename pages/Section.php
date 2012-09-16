<?php

require_once(DOC_ROOT . "/lib/html.php");

abstract class Section
{
	protected $serverRequest;
	protected $services;
	protected $login;

	public function __construct($serverRequest, $services, $login)
	{
		$this->serverRequest = $serverRequest;
		$this->services = $services;
		$this->login = $login;
	}

	abstract public function getContent();

	public function draw()
	{
		$div = new Div();
		$div->setContent($this->getContent());
		$div->set("class", "section");
		return $div;
	}
}
