<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class HeaderSection extends Section
{
	public function getContent()
	{
		$GET = $this->serverRequest->getGET();
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;

		$heading = "Thinklog";
		$welcome = "";

		if(isset($this->login))
		{
			if(isset($thinkerId) && ($this->login->getThinkerId() == $thinkerId))
			{
				$genitive = $this->services->formatService->getGenitive($this->login->getThinkerId());
				$heading = $genitive . " Thinklog";
			}
			$loginThinker = $this->services->thinkerService->getThinker($this->login->getThinkerId());
			$welcome = "<p>Welcome, " . $loginThinker->getName() . "</p>";
		}

		// Generate the HTML and return it.
		$h1 = new Heading("1", $heading);
		return $h1 . $welcome;
	}
}
