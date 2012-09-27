<?php

require_once(DOC_ROOT."/lib/html.php");
require_once(DOC_ROOT."/classes/FormatService.php");
require_once(DOC_ROOT."/classes/PageRenderService.php");
require_once(DOC_ROOT."/classes/ThinkerService.php");
require_once(DOC_ROOT."/classes/ThoughtService.php");
require_once(DOC_ROOT."/classes/QueryService.php");
require_once(DOC_ROOT."/pages/Page.php");
require_once(DOC_ROOT."/pages/TagCloudSection.php");

class ThoughtPage extends Page
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
		$thoughtId = isset($GET["id"]) ? $GET["id"] : null;

		/*
		 * Get thought id from server request and get the thought data.
		 */

		if(isset($thoughtId))
		{
			$thought = $thoughtService->getThought($thoughtId);
			$thinkerId = $thought->getThinkerId();
		}

		//
		// Or use the most recent thought
		//

		else
		{
			if(!isset($thinkerId) && isset($login)) {
				$thinkerId = $login->getThinkerId();
			}
			$thought = $thoughtService->getNewest($login,$thinkerId);
			if(isset($thought)) {
				$thoughtId = $thought->getId();
				$thinkerId = $thought->getThinkerId();
			}
		}

		// Add a tag cloud
		$tagCloudSection = new TagCloudSection($this->serverRequest,$this->services,$this->login,
		                                       null, $thoughtId);
		$output .= "".($tagCloudSection->draw()) . "\n";

		//
		// Display the thought, if there is one.
		//

		if(isset($thought))
		{
			if($thoughtService->getReadPermission($login,$thought))
			{
				$div = new Div();
				$div->set("id","thought");
				$div->set("class","section");

				$div->addContent(new Heading("2", "Thought"));
				$div->addContent($pageRenderService->drawThought($thought));
				$output .= $div;

				/*
				 * Display related thoughts
				 */

				$relatedDiv = new Div();
				$relatedDiv->set("id","related");
				$relatedDiv->set("class","section");

				$relatedDiv->addContent("<h2>Related thoughts</h2>");

				$relatedDiv->addContent("<p>");
				$thoughts = $queryService->getRelated($login, 0, DEFAULT_QUERY_RESULTS_PER_PAGE, $thoughtId);
				$relatedDiv->addContent($pageRenderService->drawThoughts($thoughts));
				$relatedDiv->addContent("</p>");

				$output .= $relatedDiv;
			}

			else
			{
				$output .= "<div class=\"section\">\n";
				$output .= "<p>You do not have permission to see this thought.</p>";
				$output .= "</div>\n";
			}

		} // end if(isset($thought))

		else
		{
			$output .= "<div class=\"section\"><p>No thought.</p></div>\n";
		}

		return $output;
	}
}
