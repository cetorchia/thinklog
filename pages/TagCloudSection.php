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

	protected $formatService;
	protected $thinkerService;
	protected $tagCloudService;
	protected $sentimentService;
	protected $keywordHistoryService;

	public function __construct(&$serverRequest, &$services, &$login, $thinkerId=null, $thoughtId=null, $query=null)
	{
		parent::__construct($serverRequest, $services, $login);
		$this->thinkerId = $thinkerId;
		$this->thoughtId = $thoughtId;
		$this->query= $query;

		$this->formatService = $this->services->formatService;
		$this->thinkerService = $this->services->thinkerService;
		$this->tagCloudService = $this->services->tagCloudService;
		$this->sentimentService = $this->services->sentimentService;
		$this->keywordHistoryService = $this->services->keywordHistoryService;
	}

	public function getContent()
	{
		$output = "";

		// Some services and context we might need.
		$GET = $this->serverRequest->getGET();

		// Get the requested thinker or thought, if any
		$thinkerId = $this->thinkerId;
		$thinker = isset($thinkerId) ? $this->thinkerService->getThinker($thinkerId) : null;
		$thinkerName = isset($thinker) ? $thinker->getName() : $thinkerId;
		$thoughtId = $this->thoughtId;
		$query = $this->query;

		// Retrieve keywords and keyword pairs
		$keywords = $this->tagCloudService->getKeywords($thinkerId, $thoughtId, $query);
		$keywordPairs = $this->tagCloudService->getKeywordPairs($thinkerId, $thoughtId, $query);

		// Look up sentiment
		if ($keywords) {
			$sentiment = $this->sentimentService->getAverageSentiment($keywords);
			foreach ($keywords as $i => $row) {
				$keywords[$i]["sentiment"] = $this->sentimentService->getSentiment($row["keyword"]);
			}
		}

		// Retrieve keyword history
		if (!$thoughtId) {
			$keywordHistory = $this->keywordHistoryService->getKeywordHistory($thinkerId, $query);
		}

		// Render bubble titles
		list($title1, $title2) = $this->renderBubbleTitles($thinkerName, $thoughtId, $query);

		// Render keyword bubbles
		if ($keywords) {
			$output .= $this->renderSentimentBubble($title1, $title2, $sentiment);
			$output .= $this->renderKeywordBubble($title1, $title2, $keywords);
		}

		// Render keyword history
		if ($keywordHistory) {
			$output .= $this->renderKeywordHistoryBubble($title1, $title2, $keywordHistory);
		}

		// Render keyword pairs ("relationships")
		if ($keywordPairs) {
			$output .= $this->renderKeywordPairBubble($title1, $title2, $keywordPairs);
		}

		if ($keywords || $keywordPairs || $keywordHistory) {
			$clearBoth = new Div("&nbsp;");
			$clearBoth->set("style", "clear: both; font-size: 0");
			$output .= $clearBoth;
		}

		return $output;
	}

	// Generate bubble titles
	protected function renderBubbleTitles($thinkerName, $thoughtId, $query) {
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

		return array($title1, $title2);
	}

	// Generate sentiment bubble
	// @param $title1, $title2  For bubble header indicating whose thoughts and for what
	// @param $sentiment Trending sentiment value
	protected function renderSentimentBubble($title1, $title2, $sentiment) {
		$output = "";

		$div = new Div();
		$div->set("class", "bubble tag_cloud");
		$div->set("style", "float: left");
		$div->addContent("${title1}Sentiment{$title2}: <br />");
		$span = new Span();
		$span->set("style", $this->getSentimentStyle($sentiment, true));
		if ($sentiment >= 0.50) {
			$span->addContent("Positive");
		} else if ($sentiment <= -0.50) {
			$span->addContent("Negative");
		} else {
			$span->addContent("Neutral");
		}
		$span->addContent(" (" . round($sentiment, 3) . ")");
		$div->addContent($span);

		$output .= $div;

		return $output;
	}

	// Generate keyword bubble
	// @param $title1, $title2  For bubble header indicating whose thoughts and for what
	// @param keywords Keyword rows to display
	protected function renderKeywordBubble($title1, $title2, $keywords) {
		$output = "";

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
			$link = new Anchor($this->formatService->getQueryURL($keyword),$keyword);
			$link->set("style", "font-size: 1.${count}em; " .
			           $this->getSentimentStyle($row["sentiment"]));
			$div->addContent($link);
		}

		$output .= $div;

		return $output;
	}

	// Generate keyword pair bubble
	// @param $title1, $title2  For bubble header indicating whose thoughts and for what
	// @param keywords Keyword pair rows to display
	protected function renderKeywordPairBubble($title1, $title2, $keywordPairs) {
		$output = "";

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
			$graph->addNode($keyword1, $this->formatService->getQueryURL($keyword1));
			$graph->addNode($keyword2, $this->formatService->getQueryURL($keyword2));
			$graph->addEdge($keyword1, $keyword2, $count);
		}

		$output .= $div;
		$output .= $graph->draw();

		return $output;
	}

	// Generate keyword history bubble
	// @param $title1, $title2  For bubble header indicating whose thoughts and for what
	// @param keywords Keyword day count rows to display
	protected function renderKeywordHistoryBubble($title1, $title2, $keywordHistory) {
		$output = "";

		$div = new Div();
		$div->set("class", "bubble tag_cloud");
		$div->set("style", "float: left");
		$div->addContent("${title1}Keyword History${title2} (last 30 days): <br />");
		$div->addContent("<div id=\"keyword_history\" class=\"chart\"></div>");
		$stream = new StreamDrawer("keyword_history", 250, 200, 30, $keywordHistory);

		$output .= $div;
		$output .= $stream->draw();

		return $output;
	}

	// Given sentiment from -1.0 to 1.0, return CSS style
	// for text expressing this sentiment.
	// @param $sentiment Sentiment value
	// @param $size Boolean whether to make text bigger when sentiment is stronger
	//              (i.e. the magnitude of its absolute value is higher)
	protected function getSentimentStyle($sentiment, $size=false) {
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
