<?php

/**
 * Consists of GET, POST, and COOKIE parameters, stripped of slashes and
 * ready for use.
 */

class ServerRequest
{
	protected $POST;
	protected $GET;
	protected $COOKIE;
	protected $FILES;

	public function __construct($GET, $POST, $COOKIE, $FILES)
	{
		$this->POST = $this->stripSlashesParamArray($POST);
		$this->GET = $this->stripSlashesParamArray($GET);
		$this->COOKIE = $this->stripSlashesParamArray($COOKIE);
		$this->FILES = $FILES;
	}

	// Returns the data in an uploaded file
	public function getFile($name)
	{
		if(isset($this->FILES[$name]) && isset($this->FILES[$name]["tmp_name"]))
		{
			$filename = $this->FILES[$name]["tmp_name"];
			return $filename;
		}

		return null;
	}

	//
	// Getters for the GET, POST, and COOKIE requests.
	// Also for FILES.
	//

	public function getGET()
	{
		return $this->GET;
	}

	public function getPOST()
	{
		return $this->POST;
	}

	public function getCOOKIE()
	{
		return $this->COOKIE;
	}

	public function getFILES()
	{
		return $this->FILES;
	}

	// Somehow, ' gets translated
	// into \' in certain versions of PHP, because the creators
	// of PHP could not think of a better way to prevent SQL injection.

	public function stripSlashesParamValue($str)
	{
		return(get_magic_quotes_gpc() ? stripslashes($str) : $str);
	}

	public function stripSlashesParamArray($paramArray)
	{
		$newParamArray = array();

		foreach($paramArray as $key => $value) {
			$newParamArray[$key] = isset($value) ? $this->stripSlashesParamValue($value) : null;
		}

		return($newParamArray);
	}
}
