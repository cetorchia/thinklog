<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class SearchSection extends Section
{
	public function getContent()
	{
		$output = "";

		$GET = $this->serverRequest->getGET();

		// Get the current thinker and login thinker
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		$login = $this->login;

		// Some services we might need.
		$thinkerService = $this->services->thinkerService;
		$formatService  = $this->services->formatService;

		// Get info about thinkers

		if(isset($thinkerId)) {
			$thinker = $thinkerService->getThinker($thinkerId);
		}

		// The search markup

		$output .= "<form method=\"get\" action=\"" . $formatService->getThinklogURL() . "\" " .
		           "style=\"padding: 0; margin: 0px;\">\n";
		$output .= "<input type=\"text\" value=\"\" name=\"q\" style=\"width: 50%; height: 2em;\" />\n";
		$output .= "<input type=\"submit\" value=\"search\" style=\"height:2em\"/>\n";

		if(isset($thinkerId))
		{
			$output .= "<br />\n";
			$output .= "<input type=\"checkbox\" name=\"thinker\" value=\"" . htmlspecialchars($thinkerId) . "\" />";
			$output .= htmlspecialchars($thinker->getName()) . "'s thoughts only";
		}

		return $output;
	}
}
