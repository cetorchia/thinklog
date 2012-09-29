<?php

require_once(DOC_ROOT."/lib/html.php");
require_once(DOC_ROOT."/classes/FormatService.php");
require_once(DOC_ROOT."/classes/PageRenderService.php");
require_once(DOC_ROOT."/classes/ThinkerService.php");
require_once(DOC_ROOT."/classes/ThoughtService.php");
require_once(DOC_ROOT."/classes/QueryService.php");
require_once(DOC_ROOT."/pages/Page.php");
require_once(DOC_ROOT."/pages/Notice.php");
require_once(DOC_ROOT."/pages/TagCloudSection.php");

class RecommendedPage extends Page
{

	public function getContent()
	{
		$output = "";

		/*
		 * Get context
		 */

		$thinkerService = $this->services->thinkerService;
		$thoughtService = $this->services->thoughtService;
		$queryService = $this->services->queryService;
		$formatService = $this->services->formatService;
		$pageRenderService = $this->services->pageRenderService;

		$login = $this->login;

		$GET = $this->serverRequest->getGET();

		/*
		 * Get thinker id
		 */

		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		if((!isset($thinkerId)) && (isset($this->login)))
		{
			$thinkerId = $this->login->getThinkerId();
		}

		//
		// Display the recommended thoughts for the given thinker if any.
		//

		if(isset($thinkerId))
		{
			// Get thinker data
			$thinker = $thinkerService->getThinker($thinkerId);
			$thinkerName = isset($thinker) ? $thinker->getName() : $thinkerId;

			/*
			 * Display related thoughts
			 */

			$recommendedDiv = new Div();
			$recommendedDiv->set("id","recommended");
			$recommendedDiv->set("class","section");

			$recommendedDiv->addContent("<h2>Recommended thoughts for $thinkerName</h2>");
			$recommendedDiv->addContent(new Notice(NOTICE_RESULTS));

			$recommendedDiv->addContent("<p>");
			$thoughts = $queryService->getRecommended($login, 0, DEFAULT_QUERY_RESULTS_PER_PAGE, $thinkerId);
			$recommendedDiv->addContent($pageRenderService->drawThoughts($thoughts));
			$recommendedDiv->addContent("</p>");

			$output .= $recommendedDiv;
		}
		else
		{
			$output .= "<div class=\"section\"><p>No recommended thoughts.\n" .
			           "Please specify a thinker.</p></div>\n";
		}

		return $output;
	}

	public function getSideBar()
	{
		$GET = $this->serverRequest->getGET();
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		if((!isset($thinkerId)) && (isset($this->login)))
		{
			$thinkerId = $this->login->getThinkerId();
		}

		// Add a tag cloud
		$tagCloudSection = new TagCloudSection($this->serverRequest,$this->services,$this->login, $thinkerId);

		return $tagCloudSection->draw();
	}
}
