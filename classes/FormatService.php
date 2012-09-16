<?php

/**
 * Responsible for generating some output such as dates, URLs,
 * and thought bodies.
 */

class FormatService
{
	protected $nExcerptWords = 10;

	public function __constructor($services)
	{
	}

	// Get the link to the Thinklog home page
	public function getThinklogURL()
	{
		return(THINKLOG_URL);
	}

	// Get the thinker's Thinklog link
	public function getThinkerURL($thinkerId)
	{
		return(THINKLOG_URL . "?thinker=" . urlencode($thinkerId));
	}

	// Get all of the thinker's thoughts link
	public function getThinkerAllURL($thinkerId)
	{
		return($this->getThinkerURL($thinkerId) . "&q=");
	}

	// Get all of the thoughts link
	public function getAllURL()
	{
		return($this->getQueryURL(""));
	}

	// Gets a URL to the thought.
	public function getThoughtURL($thought)
	{
		return(THINKLOG_URL . "?id=" . urlencode($thought->getId()));
	}

	// Get the Thinklog URL that will take you to that query.
	public function getQueryURL($query)
	{
		return THINKLOG_URL . "?q=" . urlencode($query);
	}

	// Get the Thinklog URL that will let you add a thought
	public function getAddURL()
	{
		return THINKLOG_URL . "?add";
	}

	// Get the Thinklog URL that will take you to the upload page
	public function getUploadURL()
	{
		return THINKLOG_URL . "?upload";
	}

	// Get the Thinklog URL that will take you to the query page
	public function getQueryPageURL()
	{
		return THINKLOG_URL . "?query";
	}

	// Get the Thinklog URL that will take you to the logout page
	public function getLogoutURL()
	{
		return THINKLOG_URL . "?logout";
	}

	// Get the Thinklog URL that will take you to the login page
	public function getLoginURL()
	{
		return THINKLOG_URL . "?login";
	}

	// Get the Thinklog URL that will indicate a login error has occurred.
	public function getLoginErrorURL()
	{
		return $this->getLoginURL() . "&error";
	}

	// Get the Thinklog URL that will take you to the signup page
	public function getSignUpURL()
	{
		return THINKLOG_URL . "?signup";
	}

	//
	// Gets an excerpt of the thought
	//

	public function getExcerpt($thought)
	{
		// Get the body words and take the first few

		$body = $thought->getBody();
		$excerptWords = array();
		$bodyWords = explode(" ", $body);
		$nBodyWords = count($bodyWords);
		$n = min($this->nExcerptWords, $nBodyWords);

		for($i = 0; $i < $n; $i = $i + 1) {
			$excerptWords[] = $bodyWords[$i];
		}

		$excerpt = implode(" ", $excerptWords);

		// Put "..." if we couldn't get them all

		if($n < $nBodyWords) {
			$excerpt .= " ...";
		}

		return($excerpt);
	}

	//
	// Let the user specify some formatting
	//
	// This is called the "whitelist" approach (contrary to "blacklist") 
	// where you do not risk possible threat by trusting potentially risky
	// allowed html.
	//

	public function formatText($string)
	{
		// Get it ready for HTML
		$string=htmlspecialchars($string);

		//
		// Generate hyperlinks
		//

		$string = preg_replace('/(http:\/\/[^\s\"]*[^\s.,;])/',
		                       "<a href=\"$1\">$1</a>$2",$string);

		$string = preg_replace('/(https:\/\/[^\s\"]*[^\s.,;])/',
		                       "<a href=\"$1\">$1</a>$2",$string);

		// Allow limited formatting with these tags

		$string = preg_replace('/\*([^\*\s][^\*]*[^\*\s]|[^\s\*])\*/',
		                       "<b>$1</b>",$string);

		// Link hash tags to queries

		$string = preg_replace(HASH_TAG_REGEX,
		                       " <a href=\"" . $this->getQueryURL("") . "$1\">#$1</a>",
		                       $string);

		return($string);
	}

	// Returns the genitive of the name
	public function getGenitive($name)
	{
		$i=strlen($name)-1;

		if($name[$i]=="s")
		{
			return $name . "'";
		}

		else
		{
			return $name . "'s";
		}
	}
}
