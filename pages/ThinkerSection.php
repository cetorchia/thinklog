<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class ThinkerSection extends Section
{

	// Note: if you have a thinker section, there should be some 
	// indication as to what the thinker is, i.e. this page is for an 
	// article, or a query specific to a certain thinker.

	public function getContent()
	{
		$output = "";

		// Some services and context we might need.
		$thoughtService = $this->services->thoughtService;
		$thinkerService = $this->services->thinkerService;
		$formatService  = $this->services->formatService;
		$GET = $this->serverRequest->getGET();

		// Get the current thinker and login thinker
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		$thoughtId = isset($GET["id"]) ? $GET["id"] : null;
		if(!isset($thinkerId))
		{
			if(isset($thoughtId))
			{
				$thought = $thoughtService->getThought($thoughtId);
				$thinkerId = $thought->getThinkerId();
			}
		}

		// Get info about the thinker

		if(isset($thinkerId)) {
			$thinker = $thinkerService->getThinker($thinkerId);
		}

		// Info specific to the current thinker
		if(isset($thinker))
		{
			$output .= new Heading(2, "About " . htmlspecialchars($thinker->getName()));
			// $infoImg = new Image("images/thinker.png", "Thinker");
			// $infoImg->set("height", "56");
			// $output .= $infoImg;
			$output .= new Paragraph($formatService->formatText($thinker->getAbout()));
		}
		else
		{
			$output .= "<p>No thinker</p>\n";
		}

		return $output;
	}
}
