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
		))." &nbsp; ");

		// Link to home page
		$linksDiv->addContent((new Anchor(
				$formatService->getQueryPageURL(),
				"Query"
		))." &nbsp; ");

		// Specific to current thinker
		if(isset($thinker))
		{
			$linksDiv->addContent((new Anchor(
					$formatService->getThinkerAllURL($thinkerId),
					htmlentities($thinker->getName()) . "'s thoughts")
			)." &nbsp; ");

			if((!isset($login)) || ($login->getThinkerId() != $thinkerId))
			{
				$linksDiv->addContent((new Anchor(
					$formatService->getThinkerURL($thinkerId),
					htmlentities($thinker->getName()) . "'s Thinklog")
				)." &nbsp; ");
			}
		}

		// Specific to logged-in thinker
		if(isset($login))
		{
			$linksDiv->addContent((new Anchor(
					$formatService->getThinkerURL($login->getThinkerId()),
					"My Thinklog"
			))." &nbsp; ");
			$linksDiv->addContent((new Anchor(
					$formatService->getAddURL(),
					"Add thoughts"
			))." &nbsp; ");
			$linksDiv->addContent((new Anchor(
					$formatService->getLogoutURL(),
					"Logout"
			))." &nbsp; ");
					
		}

		// If the user is not logged in... maybe they should
		if(!isset($login))
		{
			$linksDiv->addContent((new Anchor(
					$formatService->getLoginURL(),
					"Login"
			))." &nbsp; ");
			$linksDiv->addContent((new Anchor(
					$formatService->getSignUpURL(),
					"Sign up"
			))." &nbsp; ");
		}

		// Link to home page
		$linksDiv->addContent((new Anchor(
				$formatService->getThinklogURL(),
				"Home"
		))." &nbsp; ");

		$output .= $linksDiv;

		return $output;
	}
}
