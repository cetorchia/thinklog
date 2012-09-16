<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Page.php");

class LoginPage extends Page
{
	public function getContent()
	{
		$GET = $this->serverRequest->getGET();

		// Get the login thinker and if there's an error.
		$login = $this->login;
		$error = isset($GET["error"]);

		// Some services we might need.
		$formatService  = $this->services->formatService;
		$thinkerService = $this->services->thinkerService;

		//
		// Output the login page
		//

		$div = new Div();
		$div->set("class","section");

		$div->addContent("<h3>Login to Thinklog</h3>\n");
		$par = new Paragraph();

		// Logged in already?

		if(isset($login))
		{
			$loginThinker = $thinkerService->getThinker($login->getThinkerId());
			$par->addContent("You are already logged in as " . $loginThinker->getName());
		}

		// Error?

		if($error)
		{
			$par->addContent("<b>Invalid login name and/or password.</b> Try again?\n");
		}

		$par->addContent(
			"<form method=\"post\" action=\"" . $formatService->getThinklogURL() . "\">\n" .
			"<input type=\"hidden\" name=\"login\" value=\"1\" />" .
			"<table class=\"login_table\">\n" .
			"<tr>\n" .
			"<td>Login:</td>\n" .
			"<td><input type=\"text\" name=\"thinker\" size=\"14\" /></td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td>Password:</td>\n" .
			"<td><input type=\"password\" name=\"password\" size=\"11\" /></td>\n" .
			"</tr>\n" .
			"<tr>\n" .
			"<td><input type=\"submit\" value=\"login\" /></td>\n" .
			"</tr>\n" .
			"</table>\n" .
			"</form>\n"
		);

		$div->addContent($par);

		return "" . $div;
	}
}

