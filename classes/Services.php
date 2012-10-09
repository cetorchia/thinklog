<?php

/**
 * Abstract definition for an object that defines services.
 */

abstract class Services
{
	public $timerService;
	public $thinkerService;
	public $thoughtService;
	public $queryService;
	public $loginService;
	public $logoutService;
	public $pageRenderService;
	public $formatService;
	public $mentionsService;
	public $relatedKeywordService;
	public $keywordService;
	public $commonKeywordService;
	public $wikipediaKeywordService;
	public $tagCloudService;
	public $sentimentService;

	// Initialize the services.
	abstract public function __construct();
}
