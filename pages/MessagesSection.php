<?php

require_once(DOC_ROOT . "/lib/html.php");

class MessagesSection
{
	protected $think;
	protected $notLogin;
	protected $tooLong;
	protected $noThoughts;
	protected $invalidURL;
	protected $error;
	protected $success;

	public function __construct($serverRequest, $services, $login)
	{
		$GET = $serverRequest->getGET();
		$this->think = isset($GET["think"]);
		$this->notLogin = isset($GET["notLogin"]);
		$this->tooLong = isset($GET["tooLong"]);
		$this->noThoughts = isset($GET["noThoughts"]);
		$this->invalidURL = isset($GET["invalidURL"]);
		$this->error = isset($GET["error"]);
		$this->success = isset($GET["success"]);
	}

	public function getContent()
	{
		$output = null;

		if($this->think)
		{
			if($this->notLogin)
			{
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_error");
				$span->setContent("I could not add or modify some of your thoughts: you do not have permission. Try logging in.");
				$par->setContent($span);

				$output = "" . $par;
			}

			if($this->tooLong)
			{
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_error");
				$span->setContent("I could not add some of your thoughts: a thought is too long!");
				$par->setContent($span);

				$output = "" . $par;
			}
			if($this->noThoughts)
			{
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_error");
				$span->setContent("I could not add your thoughts: there are no thought(s) to add!");
				$par->setContent($span);

				$output = "" . $par;
			}
			if($this->invalidURL)
			{
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_error");
				$span->setContent("I could not add your thoughts: you did not specify a proper URL!");
				$par->setContent($span);

				$output = "" . $par;
			}
			if($this->error)
			{
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_error");
				$span->setContent("I could not add or modify some of your thoughts: error occurred.");
				$par->setContent($span);

				$output = "" . $par;
			}

			if($this->success) {
				$par = new Paragraph();
				$span = new Span();
				$span->set("class", "thot_success");
				$span->setContent("You successfully added/modified all your thought(s)!");
				$par->setContent($span);

				$output = "" . $par;
			}
		}

		if(isset($output))
		{
			return "<div class=\"section\">" . $output . "</div>";
		}

		return "";
	}

	public function draw()
	{
		return $this->getContent();
	}
}
