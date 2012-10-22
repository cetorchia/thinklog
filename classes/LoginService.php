<?php

require_once(DOC_ROOT . "/classes/Login.php");

class LoginService
{
	protected $thinkerService;

	public function __construct(&$services)
	{
		$this->thinkerService = $services->thinkerService;
	}

	//
	// Get login values of the server request.
	// If the user is logged in these will make sense. Otherwise, they are null.
	//

	public function getLogin(&$serverRequest)
	{
		$COOKIE = $serverRequest->getCOOKIE();

		$thinkerId = isset($COOKIE['thinker']) ? $COOKIE['thinker'] : null;
		$password = isset($COOKIE['password']) ? $COOKIE['password'] : null;

		if(isset($thinkerId) && isset($password))
		{
			$login = new Login();
			$login->setThinkerId($thinkerId);
			$login->setPassword($password);

			if($this->thinkerService->verifyLogin($login))
			{
				return($login);
			}
		}

		return(null);
	}
}
