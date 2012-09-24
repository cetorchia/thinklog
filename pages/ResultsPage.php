<?php

require_once(DOC_ROOT."/lib/html.php");
require_once(DOC_ROOT."/classes/FormatService.php");
require_once(DOC_ROOT."/classes/PageRenderService.php");
require_once(DOC_ROOT."/classes/ThinkerService.php");
require_once(DOC_ROOT."/classes/ThoughtService.php");
require_once(DOC_ROOT."/classes/QueryService.php");
require_once(DOC_ROOT."/pages/Page.php");
require_once(DOC_ROOT."/pages/TagCloudSection.php");

class ResultsPage extends Page
{

	public function getContent()
	{
		$output = "";

		/*
		 * Get context
		 */

		$GET = $this->serverRequest->getGET();
		$thinkerService = $this->services->thinkerService;
		$queryService = $this->services->queryService;
		$pageRenderService = $this->services->pageRenderService;
		$login = $this->login;

		// Get thinker
		$thinkerId = isset($GET["thinker"])?$GET["thinker"]:null;
		if(isset($thinkerId)) {
			$thinker = $thinkerService->getThinker($thinkerId);
		}

		//
		// Get query
		//

		$query = isset($GET["q"])?$GET["q"]:null;

		// Get query parameters

		$offset=isset($GET['offset'])?$GET['offset']:0;

		/*
		 * Begin query output
		 */

		$div = new Div();
		$div->set("id","query_results");
		$div->set("class","section");

		$div->addContent("<h2>\n");
		$div->addContent(
			htmlspecialchars((!isset($query)) || (strlen($query)==0) ? "All Thoughts":"Query \"".$query."\"")." "
		);
		if(isset($thinker)) {
			$div->addContent("By ".htmlspecialchars($thinker->getName()));
		}

		$div->addContent("</h2>\n");

		//
		// Do query
		//

		$div->addContent("<p>\n");

		// Call the query service and draw the thoughts.
		$thoughts = array();
		$moreResults = $queryService->getThoughts($login,$thinkerId,$query,$offset,DEFAULT_QUERY_RESULTS_PER_PAGE,$thoughts);
		$div->addContent($pageRenderService->drawThoughts($thoughts));

		$div->addContent("</p>\n");

		//
		// Page URLs
		//

		$url = "./?q=".urlencode($query);
		$url .= isset($thinkerId)?("&thinker=".urlencode($thinkerId)):"";
		$urlPrev = $url."&offset=".($offset-DEFAULT_QUERY_RESULTS_PER_PAGE);
		$urlNext = $url."&offset=".($offset+DEFAULT_QUERY_RESULTS_PER_PAGE);

		$div->addContent("<p>\n");

		if($offset >= DEFAULT_QUERY_RESULTS_PER_PAGE) {
			$div->addContent("<a href=\"".htmlspecialchars($urlPrev)."\">&lt;&lt; Previous ".
				 DEFAULT_QUERY_RESULTS_PER_PAGE."</a> &nbsp;\n"
			);
		}
		if($moreResults) {
			$div->addContent("<a href=\"".htmlspecialchars($urlNext)."\">Next ".
				 DEFAULT_QUERY_RESULTS_PER_PAGE." &gt;&gt;</a>\n"
			);
		}

		$div->addContent("</p>\n");

		/*
		 * RSS link
		 */

		$div->addContent("<p>\n");
		$rssURL='./?output=rss'.
				 '&q='.$query.
				 (isset($thinkerId)?'&thinker='.$thinkerId:'');
		$div->addContent("<a href=\"$rssURL\">RSS feed</a>");
		$div->addContent("</p>\n");

		// Put a tag cloud up top
		if(!$query) {
			$tagCloudSection = new TagCloudSection($this->serverRequest,$this->services,$this->login,
			                                       $thinkerId);
			$output .= "".($tagCloudSection->draw()) . "\n";
		} else {
			$tagCloudSection = new TagCloudSection($this->serverRequest,$this->services,$this->login,
			                                       $thinkerId, null, $query);
			$output .= "".($tagCloudSection->draw()) . "\n";
		}

		// Then output the results div
		$output .= "".$div;

		return $output;
	}
}
