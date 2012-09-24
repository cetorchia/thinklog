<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class TagCloudSection extends Section
{
	protected $thinkerId;
	protected $thoughtId;

	public function __construct($serverRequest, $services, $login, $thinkerId=null, $thoughtId=null)
	{
		$this->serverRequest = $serverRequest;
		$this->services = $services;
		$this->login = $login;

		$this->thinkerId = $thinkerId;
		$this->thoughtId = $thoughtId;
	}

	public function getContent()
	{
		$output = "";

		// Some services and context we might need.
		$formatService = $this->services->formatService;
		$tagCloudService = $this->services->tagCloudService;
		$thinkerService = $this->services->thinkerService;
		$GET = $this->serverRequest->getGET();

		// Get the requested thinker or thought, if any
		$thinkerId = $this->thinkerId;
		$thinker = isset($thinkerId) ? $thinkerService->getThinker($thinkerId) : null;
		$thinkerName = isset($thinker) ? $thinker->getName() : $thinkerId;
		$thoughtId = $this->thoughtId;

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
				$par->addContent("$link &nbsp; ");
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
