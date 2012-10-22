<?php

class LogoutRequest
{
	protected $logout;

	public function __construct(&$serverRequest)
	{
		$GET = $serverRequest->getGET();
		$this->logout = isset($GET["logout"]);
	}

	//
	// Tries to logout; if the GET parameters contain logout, we will logout.
	//

	public function execute()
	{
		if($this->logout) {
			// Throw the cookie out.
			setcookie('login','',time()-3600);
			setcookie('password','',time()-3600);
			header('Location: ./');
		}
	}
}
