<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Page.php");

class QueryPage extends Page
{
	public function getContent()
	{
		$output = "<div class=\"section\">\n";
		$output .= "<h2>Search</h2>\n";

		$GET = $this->serverRequest->getGET();

		// Get the current thinker and login thinker
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		$login = $this->login;

		// Some services we might need.
		$thoughtService = $this->services->thoughtService;
		$thinkerService = $this->services->thinkerService;
		$formatService  = $this->services->formatService;

		// Get info about thinkers

		if(isset($thinkerId)) {
			$thinker = $thinkerService->getThinker($thinkerId);
		}

		if(isset($login))
		{
			$loginThinker = $thinkerService->getThinker($login->getThinkerId());
		}

		// The search markup

		$output .= "<form method=\"get\" action=\"" . $formatService->getThinklogURL() . "\">\n";
		$output .= "<input type=\"text\" value=\"\" name=\"q\" />\n";
		$output .= "<input type=\"submit\" value=\"search\" style=\"width: 25%\" />\n";
		$output .= "<br />\n";

		if(isset($thinkerId))
		{
			$output .= "<input type=\"checkbox\" name=\"thinker\" value=\"" . htmlentities($thinkerId) . "\" />";
			$output .= htmlentities($thinker->getName()) . "'s thots only";
		}

		$output .= "</div>\n";

		return $output;
	}
}
