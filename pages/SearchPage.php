<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Page.php");
require_once(DOC_ROOT . "/pages/Notice.php");

class SearchPage extends Page
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

		// The search markup

		$output .= new Notice(NOTICE_SEARCH);
		$output .= "<form method=\"get\" action=\"" . $formatService->getThinklogURL() . "\">\n";
		$output .= "<input type=\"text\" value=\"\" name=\"q\" style=\"width: 50%\" />\n";
		$output .= "<input type=\"submit\" value=\"search\" />\n";
		$output .= "<br />\n";

		if(isset($thinkerId))
		{
			$output .= "<input type=\"checkbox\" name=\"thinker\" value=\"" . htmlspecialchars($thinkerId) . "\" />";
			$output .= htmlspecialchars($thinker->getName()) . "'s thots only";
		}

		$output .= "</div>\n";

		return $output;
	}
}
