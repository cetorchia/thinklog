<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class HeaderSection extends Section
{
	public function getContent()
	{
		$GET = $this->serverRequest->getGET();
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;

		$heading = THINKLOG_TITLE;

		// Generate a greeting directed at the logged-in user
		if (isset($this->login)) {
			$loginThinker = $this->services->thinkerService->getThinker($this->login->getThinkerId());
			$welcome = new Paragraph("Welcome, " . $loginThinker->getName() . "");
		} else {
			$welcome = "";
		}

		// Generate the HTML and return it.
		$h = new Heading("1", $heading);
		return $h . $welcome;
	}
}
