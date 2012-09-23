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
		$welcome = "";

		// Generate a greeting directed at the logged-in user
		if (isset($this->login)) {
			if (isset($thinkerId) && ($this->login->getThinkerId() == $thinkerId)) {
				$genitive = $this->services->formatService->getGenitive($this->login->getThinkerId());
				$heading = "$genitive $heading";
			}

			$loginThinker = $this->services->thinkerService->getThinker($this->login->getThinkerId());
			$welcome = new Paragraph("Welcome, " . $loginThinker->getName() . "");
		}

		// Generate the HTML and return it.
		$h = new Heading("1", $heading);
		$h->set("style", "font-style: italic;");
		return $h . $welcome;
	}
}
