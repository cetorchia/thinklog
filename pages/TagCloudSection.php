<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class TagCloudSection extends Section
{
	protected $thinkerId;
	protected $thoughtId;
	protected $query;

	public function __construct($serverRequest, $services, $login, $thinkerId=null, $thoughtId=null, $query=null)
	{
		$this->serverRequest = $serverRequest;
		$this->services = $services;
		$this->login = $login;

		$this->thinkerId = $thinkerId;
		$this->thoughtId = $thoughtId;
		$this->query= $query;
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
		$query = $this->query;

		// Retrieve keywords and keyword pairs
		if (!$query) {
			$keywords = $tagCloudService->getKeywords($thinkerId, $thoughtId, $query);
			$keywordPairs = $tagCloudService->getKeywordPairs($thinkerId, $thoughtId, $query);
		} else {
			$keywordPairs = $tagCloudService->getKeywordPairs($thinkerId, $thoughtId, $query);
			$keywords = array();
			foreach ($keywordPairs as $keywordPair) {
				$keywords[] = $keywordPair[1];
			}
		}

		/*
		 * Render HTML for tag cloud
		 */
		if ($thinkerId) {
			$title1 = "$thinkerName's ";
		} else {
			$title1 = "";
		}

		if (!$thoughtId && !$query) {
			$title1 .= "Trending ";
		}

		if ($query) {
			$title2 = " related to this query";
		} else if ($thoughtId) {
			$title2 = " of this thought";
		} else {
			$title2 = "";
		}

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
				                   "$keyword1&nbsp;-&nbsp;$keyword2");
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
