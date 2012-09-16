<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Section.php");

class MenuSection extends Section
{
	public function getContent()
	{
		$output = "<h2>Menu</h2>\n";

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

		$linksDiv->addContent(new Paragraph(new Anchor(
				$formatService->getAllURL(),
				"All thoughts"
		)));

		// Link to home page
		$linksDiv->addContent(new Paragraph(new Anchor(
				$formatService->getQueryPageURL(),
				"Query"
		)));

		// Specific to current thinker
		if(isset($thinker))
		{
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getThinkerAllURL($thinkerId),
					htmlentities($thinker->getName()) . "'s thoughts")
			));

			if((!isset($login)) || ($login->getThinkerId() != $thinkerId))
			{
				$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getThinkerURL($thinkerId),
					htmlentities($thinker->getName()) . "'s Thinklog")
				));
			}
		}

		// Specific to logged-in thinker
		if(isset($login))
		{
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getThinkerURL($login->getThinkerId()),
					"My Thinklog"
			)));
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getUploadURL(),
					"Upload thoughts"
			)));
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getLogoutURL(),
					"Logout"
			)));
					
		}

		// If the user is not logged in... maybe they should
		if(!isset($login))
		{
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getLoginURL(),
					"Login"
			)));
			$linksDiv->addContent(new Paragraph(new Anchor(
					$formatService->getSignUpURL(),
					"Sign up"
			)));
		}

		// Link to home page
		$linksDiv->addContent(new Paragraph(new Anchor(
				$formatService->getThinklogURL(),
				"Home"
		)));

		$output .= $linksDiv;

		return $output;
	}
}
