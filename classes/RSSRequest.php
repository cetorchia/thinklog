<?php

require_once(DOC_ROOT . "/classes/ThinkerService.php");
require_once(DOC_ROOT . "/classes/ThoughtService.php");

class RSSRequest
{
	protected $defaultQueryResultsPerPage = 10;

	protected $thinkerId;
	protected $query;

	protected $thinkerService;
	protected $thoughtService;
	protected $queryService;
	protected $formatService;

	protected $login;

	public function __construct($serverRequest, $services, $login)
	{
		$GET = $serverRequest->getGET();

		$this->thinkerId = isset($GET['thinker']) ? $GET['thinker'] : null;
		$this->query = isset($GET['q']) ? $GET['q'] : null;

		$this->thinkerService = $services->thinkerService;
		$this->thoughtService = $services->thoughtService;
		$this->queryService = $services->queryService;
		$this->formatService = $services->formatService;

		$this->login = $login;
	}

	// Prints out the full RSS feed
	public function execute()
	{
		$thinker = isset($this->thinkerId) ? $this->thinkerService->getThinker($this->thinkerId) : null;

		// Do it in XML
		header("Content-Type: text/xml");
		$doc = new DOMDocument("1.0","iso-8859-1");
		$rss = $doc->appendChild($doc->createElement('rss'));
		$rss->setAttribute("version", "2.0");
		$channel = $rss->appendChild($doc->createElement('channel'));

		// Title, etc.
		if(isset($this->thinkerId))
		{
			if(isset($thinker))
			{
				$title = $thinker->getName() . "'s Thinklog";
				$link = $this->formatService->getThinkerURL($thinker);
				$description = isset($this->query) ? ('Query "'.$this->query.'"') : $thinker->getAbout();
			}

			else
			{
				$title = 'Error';
				$link = '';
				$description = 'Thinker not found';
			}
		}

		else
		{
			$title = 'Thinklog';
			$link = $this->formatService->getQueryURL($this->query);
			$description="Query " . (($this->query) ? "\"".$this->query."\"" : "all");
		}

		$channel->appendChild($doc->createElement('title', $title));
		$channel->appendChild($doc->createElement('link', $link));
		$channel->appendChild($doc->createElement('description', $description));

		//
		// Do query
		//

		if(isset($this->query)) {

			// Get query results and print them.
			$thoughts = array();
			$this->queryService->getThoughts(
					$this->login,
					$this->thinkerId,
					$this->query,
					0,
					$this->defaultQueryResultsPerPage,
					$thoughts
			);
			$this->addThoughts($thoughts,$doc,$channel);
		}

		echo $doc->saveXML();
	}

	// Adds thoughts to an <channel /> node.
	public function addThoughts($thoughts, &$doc, &$channel)
	{
		foreach($thoughts as $thought)
		{
			$item = $channel->appendChild($doc->createElement('item'));

			$date = date("r", $thought->getDate());
			$description = $this->formatService->getExcerpt($thought);
			$link = $this->formatService->getThoughtURL($thought);

			$item->appendChild($doc->createElement('link', $link));
			$item->appendChild($doc->createElement('description', $description));
			$item->appendChild($doc->createElement('pubDate', $date));
		}
	}
}
