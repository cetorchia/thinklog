<?php

require_once(DOC_ROOT . "/classes/TimerService.php");
require_once(DOC_ROOT . "/classes/FormatService.php");
require_once(DOC_ROOT . "/classes/ThinkerService.php");
require_once(DOC_ROOT . "/classes/ThoughtService.php");
require_once(DOC_ROOT . "/classes/AddService.php");
require_once(DOC_ROOT . "/classes/KeywordService.php");
require_once(DOC_ROOT . "/classes/MentionsService.php");
require_once(DOC_ROOT . "/classes/QueryService.php");
require_once(DOC_ROOT . "/classes/SentimentService.php");
require_once(DOC_ROOT . "/classes/TagCloudService.php");
require_once(DOC_ROOT . "/classes/KeywordHistoryService.php");
require_once(DOC_ROOT . "/classes/LoginService.php");
require_once(DOC_ROOT . "/classes/PageRenderService.php");

/**
 * Application's definition of services.
 */

class AppServices
{
	// Initialize the services.
	public function __construct()
	{
		$this->timerService = new TimerService($this);
		$this->formatService = new FormatService($this);
		$this->thinkerService = new ThinkerService($this);
		$this->thoughtService = new ThoughtService($this);
		$this->keywordService = new KeywordService($this);
		$this->queryService = new QueryService($this);
		$this->mentionsService = new MentionsService($this);
		$this->addService = new AddService($this);
		$this->sentimentService = new SentimentService($this);
		$this->tagCloudService = new TagCloudService($this);
		$this->keywordHistoryService = new KeywordHistoryService($this);
		$this->loginService = new LoginService($this);
		$this->pageRenderService = new PageRenderService($this);
	}
}
