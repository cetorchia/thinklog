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
				$keywords[] = array("keyword" => $keywordPair["kw2"],
				                    "cnt" => $keywordPair["cnt"]);
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
			$div = new Div();
			$div->set("class", "bubble tag_cloud");
			$div->set("style", "float: left");
			$div->addContent("${title1}Keywords${title2}: <br />");
			$first = true;
			foreach($keywords as $row)
			{
				if ($first) {
					$first = false;
				} else {
					$div->addContent(" &nbsp; ");
				}
				$keyword = $row["keyword"];
				$count = $row["cnt"] * 5;
				$link = new Anchor($formatService->getQueryURL($keyword),$keyword);
				$link->set("style", "font-size: 1.${count}em");
				$div->addContent($link);
			}
			$output .= $div;
		}

		// Render keyword pairs ("relationships")
		if ($keywordPairs) {
			$div = new Div();
			$div->set("class", "bubble tag_cloud");
			$div->set("style", "float: left");
			$div->addContent("${title1}Keyword Relationships${title2}: <br />");
			$first = true;
			foreach($keywordPairs as $row)
			{
				if ($first) {
					$first = false;
				} else {
					$div->addContent(" &nbsp; ");
				}
				$keyword1 = $row["kw1"];
				$keyword2 = $row["kw2"];
				$count = $row["cnt"] * 5;
				$link = new Anchor($formatService->getQueryURL("$keyword1 $keyword2"),
				                   "$keyword1-$keyword2");
				$link->set("style", "font-size: 1.${count}em");
				$div->addContent($link);
			}
			$output .= $div;
		}

		if ($keywords || $keywordPairs) {
			$clearBoth = new Div("&nbsp;");
			$clearBoth->set("style", "clear: both; font-size: 0");
			$output .= $clearBoth;
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
