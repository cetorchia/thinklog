<?php

// Infer the triples of the knowledgebase for user-typed thoughts
// * Extract triples from text
// * Add triples to the database

class TriplifierService
{
	protected $mentionsService;
	protected $relatedKeywordService;

	function __construct($services)
	{
		// Get services
		$this->mentionsService = $services->mentionsService;
		$this->relatedKeywordService = $services->relatedKeywordService;
	}

	// Infer as many triples as possible for the given thought

	function triplify($thought)
	{
		$this->mentionsService->mentions($thought);
		$this->relatedKeywordService->thoughtRelatedKeywords($thought);

		return(true);
	}
}
