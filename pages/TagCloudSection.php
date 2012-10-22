<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");
require_once(DOC_ROOT . "/lib/GraphDrawer.php");
require_once(DOC_ROOT . "/lib/StreamDrawer.php");

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
		$sentimentService = $this->services->sentimentService;
		$tagCloudService = $this->services->tagCloudService;
		$keywordHistoryService = $this->services->keywordHistoryService;
		$thinkerService = $this->services->thinkerService;
		$GET = $this->serverRequest->getGET();

		// Get the requested thinker or thought, if any
		$thinkerId = $this->thinkerId;
		$thinker = isset($thinkerId) ? $thinkerService->getThinker($thinkerId) : null;
		$thinkerName = isset($thinker) ? $thinker->getName() : $thinkerId;
		$thoughtId = $this->thoughtId;
		$query = $this->query;

		// Retrieve keywords and keyword pairs
		$keywords = $tagCloudService->getKeywords($thinkerId, $thoughtId, $query);
		$keywordPairs = $tagCloudService->getKeywordPairs($thinkerId, $thoughtId, $query);

		// Look up sentiment
		if ($keywords) {
			$sentiment = $sentimentService->getAverageSentiment($keywords);
			foreach ($keywords as $i => $row) {
				$keywords[$i]["sentiment"] = $sentimentService->getSentiment($row["keyword"]);
			}
		}

		// Retrieve keyword history
		if (!$thoughtId) {
			$keywordHistory = $keywordHistoryService->getKeywordHistory($thinkerId, $query);
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
			// Div for sentiment value
			$div = new Div();
			$div->set("class", "bubble tag_cloud");
			$div->set("style", "float: left");
			$div->addContent("${title1}Sentiment{$title2}: <br />");
			$span = new Span();
			$span->set("style", $this->_getSentimentStyle($sentiment, true));
			if ($sentiment >= 0.5) {
				$span->addContent("Positive");
			} else if ($sentiment <= -0.50) {
				$span->addContent("Negative");
			} else {
				$span->addContent("Neutral");
			}
			$span->addContent(" ($sentiment)");
			$div->addContent($span);
			$output .= $div;

			// Div for prominent keywords
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
				$link->set("style", "font-size: 1.${count}em; " .
				                    $this->_getSentimentStyle($row["sentiment"]));
				$div->addContent($link);
			}
			$output .= $div;
		}

		// Render keyword history
		if ($keywordHistory) {
			$div = new Div();
			$div->set("class", "bubble tag_cloud");
			$div->set("style", "float: left");
			$div->addContent("${title1}Keyword History${title2} (last 30 days): <br />");
			$div->addContent("<div id=\"keyword_history\"></div>");
			$stream = new StreamDrawer("keyword_history", 250, 200, 30, $keywordHistory);
			$output .= $div;
			$output .= $stream->draw();
		}

		// Render keyword pairs ("relationships")
		if ($keywordPairs) {
			$div = new Div();
			$div->set("class", "bubble tag_cloud");
			$div->set("style", "float: left");
			$div->addContent("${title1}Keyword Relationships${title2}: <br />");
			$div->addContent("<div id=\"keyword_relationships\"></div>");
			$graph = new GraphDrawer("keyword_relationships", 250, 200);
			foreach($keywordPairs as $row)
			{
				$keyword1 = $row["kw1"];
				$keyword2 = $row["kw2"];
				$count = $row["cnt"] * 5;
				$graph->addNode($keyword1, $formatService->getQueryURL($keyword1));
				$graph->addNode($keyword2, $formatService->getQueryURL($keyword2));
				$graph->addEdge($keyword1, $keyword2, $count);
			}
			$output .= $div;
			$output .= $graph->draw();
		}

		if ($keywords || $keywordPairs || $keywordHistory) {
			$clearBoth = new Div("&nbsp;");
			$clearBoth->set("style", "clear: both; font-size: 0");
			$output .= $clearBoth;
		}

		return $output;
	}

	// Given sentiment from -1.0 to 1.0, return CSS style
	// for text expressing this sentiment.
	// @param $sentiment Sentiment value
	// @param $size Boolean whether to make text bigger when sentiment is stronger
	//              (i.e. the magnitude of its absolute value is higher)
	protected function _getSentimentStyle($sentiment, $size=false) {
		if ($sentiment >= 0.5) {
			$colour = "green";
		} else if ($sentiment <= -0.5) {
			$colour = "red";
		} else {
			$colour = "black";
		}
		$style = "color: $colour;";
		if ($size) {
			$fontSize = round(abs($sentiment) * 5);
			$style .= " font-size: 1.${fontSize}em;";
		}
		return $style;
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
