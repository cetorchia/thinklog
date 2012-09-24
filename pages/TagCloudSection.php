<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class TagCloudSection extends Section
{

	// Note: if you have a thinker section, there should be some 
	// indication as to what the thinker is, i.e. this page is for an 
	// article, or a query specific to a certain thinker.

	public function getContent()
	{
		$output = "";

		// Some services and context we might need.
		$formatService = $this->services->formatService;
		$tagCloudService = $this->services->tagCloudService;
		$thinkerService = $this->services->thinkerService;
		$GET = $this->serverRequest->getGET();

		// Get the requested thinker or thought, if any
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		$thinker = isset($thinkerId) ? $thinkerService->getThinker($thinkerId) : null;
		$thinkerName = isset($thinker) ? $thinker->getName() : $thinkerId;
		$thoughtId = isset($GET["id"]) ? $GET["id"] : null;

		// Get tag cloud
		$keywords = $tagCloudService->getKeywords($thinkerId, $thoughtId);
		$keywordPairs = $tagCloudService->getKeywordPairs($thinkerId, $thoughtId);

		// Render HTML for tag cloud
		$title1 = isset($thinkerId) ? "$thinkerName's " : 
			(isset($thoughtId) ? "" : "Trending ");
		$title2 = isset($thoughtId) ? " for this thought" : "";

		// Render keywords
		if ($keywords) {
			$par = new Paragraph();
			$par->addContent("${title1}Keywords${title2}: ");
			foreach($keywords as $keyword)
			{
				$link = new Anchor($formatService->getQueryURL($keyword),$keyword);
				$par->addContent($link." ");
			}
			$output .= $par;
		}

		// Render keyword pairs
		if ($keywordPairs) {
			$par = new Paragraph();
			$par->addContent("${title1}Keyword Pairs${title2}: ");
			foreach($keywordPairs as $keywordPair)
			{
				$keyword1 = $keywordPair[0];
				$keyword2 = $keywordPair[1];
				$link = new Anchor($formatService->getQueryURL("$keyword1 $keyword2"),
				                   "$keyword1 - $keyword2");
				$par->addContent("$link &nbsp; ");
			}
			$output .= $par;
		}

		return $output;
	}

	public function draw()
	{
		$div = new Div();
		$content = $this->getContent();
		if (!$content) {
			return "";
		}
		$div->setContent($content);
		$div->set("class", "section");
		return $div;
	}
}
