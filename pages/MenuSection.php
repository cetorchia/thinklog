<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class MenuSection extends Section
{
	public function getContent()
	{
		$output = "";

		$GET = $this->serverRequest->getGET();

		// Get the current thinker and login thinker
		$thinkerId = isset($GET["thinker"]) ? $GET["thinker"] : null;
		$login = $this->login;

		// Some services we might need.
		$thoughtService = $this->services->thoughtService;
		$thinkerService = $this->services->thinkerService;
		$formatService  = $this->services->formatService;

		// Get info about thinkers

		if(isset($thinkerId)) {
			$thinker = $thinkerService->getThinker($thinkerId);
		}

		if(isset($login))
		{
			$loginThinker = $thinkerService->getThinker($login->getThinkerId());
		}

		//
		// Links section
		//

		$linksDiv = new Div();
		$linksDiv->set("id","links");

		$linksDiv->addContent((new Anchor(
				$formatService->getAllURL(),
				"All thoughts"
		))." <br />\n ");

		// Specific to current thinker
		if(isset($thinker))
		{
			$thinkerName = htmlspecialchars($thinker->getName());
		}
		else if(isset($thinkerId))
		{
			// They might have thoughts, but not an account (i.e. if their thoughts
			// have been imported from outside
			$thinkerName = htmlspecialchars($thinkerId);
		}

		if(isset($thinkerName)) {
			$linksDiv->addContent((new Anchor(
					$formatService->getThinkerAllURL($thinkerId),
					htmlspecialchars($thinkerName) . "'s thoughts")
			)." <br />\n ");

			$linksDiv->addContent((new Anchor(
				$formatService->getThinkerURL($thinkerId),
				htmlspecialchars($thinkerName) . "'s Thinklog")
			)." <br />\n ");
		}

		// Specific to logged-in thinker
		if(isset($login))
		{
			$linksDiv->addContent((new Anchor(
					$formatService->getThinkerURL($login->getThinkerId()),
					"My Thinklog"
			))." <br />\n ");
			$linksDiv->addContent((new Anchor(
					$formatService->getAddURL(),
					"Add thoughts"
			))." <br />\n ");
			$linksDiv->addContent((new Anchor(
					$formatService->getLogoutURL(),
					"Logout"
			))." <br />\n ");
					
		}

		// If the user is not logged in... maybe they should
		if(!isset($login))
		{
			$linksDiv->addContent((new Anchor(
					$formatService->getLoginURL(),
					"Login"
			))." <br />\n ");
			$linksDiv->addContent((new Anchor(
					$formatService->getSignUpURL(),
					"Sign up"
			))." <br />\n ");
		}

		// Link to about page
		$linksDiv->addContent((new Anchor(
				$formatService->getAboutURL(),
				"About"
		))." <br />\n ");

		// Link to home page
		$linksDiv->addContent((new Anchor(
				$formatService->getThinklogURL(),
				"Home"
		))."  ");

		$output .= $linksDiv;

		return $output;
	}
}
