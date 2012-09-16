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
		$login = $this->login;
		$GET = $this->serverRequest->getGET();

		// Get the current thinker and login thinker
		$thinkerId = isset($GET["thinker"]) && isset($login) && ($login->getThinkerId() == $GET["thinker"]) ? $GET["thinker"] : null;
		$thinker = isset($thinkerId) ? $thinkerService->getThinker($thinkerId) : null;
		$thinkerName = isset($thinker) ? $thinker->getName() : null;
		$thoughtId = isset($GET["id"]) ? $GET["id"] : null;

		// Get tag cloud
		$keywords = $tagCloudService->getKeywords($thinkerId, $thoughtId);
		$keywordPairs = $tagCloudService->getKeywordPairs($thinkerId, $thoughtId);

		// Render HTML for tag cloud
		$title1 = isset($thinkerId) ? "$thinkerName's " : 
			(isset($thoughtId) ? "" : "Trending ");
		$title2 = isset($thoughtId) ? " for this thought" : "";

		// Render keywords
		$par = new Paragraph();
		$par->addContent("${title1}keywords${title2}: ");
		foreach($keywords as $keyword)
		{
			$link = new Anchor($formatService->getQueryURL($keyword),$keyword);
			$par->addContent($link." ");
		}
		$output .= $par;

		// Render keyword pairs
		$par = new Paragraph();
		$par->addContent("${title1}keyword pairs${title2}: ");
		foreach($keywordPairs as $keyword1 => $keyword2)
		{
			$link1 = new Anchor($formatService->getQueryURL($keyword1),$keyword1);
			$link2 = new Anchor($formatService->getQueryURL($keyword2),$keyword2);
			$par->addContent("(".$link1.", ".$link2.") ");
		}
		$output .= $par;

		return $output;
	}
}
