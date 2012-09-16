<?php

require_once(DOC_ROOT . "/classes/Login.php");

/**
 * The LoginRequest handles requests to log into Thinklog.
 */

class LoginRequest
{
	protected $thinkerService;
	protected $formatService;

	protected $doLogin;
	protected $thinkerId;
	protected $password;

	public function __construct($serverRequest, $services)
	{
		// Read POST request
		$POST = $serverRequest->getPOST();
		$this->doLogin = isset($POST["login"]) && ($POST["login"] != "0");
		$this->thinkerId = isset($POST["thinker"]) ? $POST["thinker"] : null;
		$this->password = isset($POST["password"]) ? sha1($POST["password"]) : null;

		$this->thinkerService = $services->thinkerService;
		$this->formatService  = $services->formatService;
	}

	//
	// Detect the need to log into Thinklog in the POST parameters, and
	// then do so.
	//

	public function execute()
	{
		if($this->doLogin)
		{
			$login = new Login();
			$login->setThinkerId($this->thinkerId);
			$login->setPassword($this->password);

			if($this->thinkerService->verifyLogin($login))
			{
				$expire=time()+1000000;
				setcookie("thinker", $this->thinkerId, $expire);
				setcookie("password", $this->password, $expire);
				header("Location: ./");
			}

			else
			{
				header("Location: " . $this->formatService->getLoginErrorURL());
			}
		}
	}
}
