<?php

require_once(DOC_ROOT . "/lib/html.php");

class PageRenderService
{

	protected $formatService;

	public function __construct(&$services)
	{
		$this->formatService = $services->formatService;
	}

	//
	// Prints all of the thoughts in $thoughts in list format.
	//

	function drawThoughts($thoughts)
	{
		if(empty($thoughts)) {
			$par = new Paragraph("None");
			return $par;
		}

		$output = "";

		foreach($thoughts as $thought) {
			$output .= $this->drawThoughtListing($thought);
		}

		return $output;
	}

	//
	// This function prints out an entire thought with its date,
	// body, thinker_id. Also displays if it's private.
	//
	// NOTE: make sure that the user has permission to see this thought
	//       before calling this function on it!
	//

	function drawThought($thought)
	{
		$output = "";

		// Private?
		if($thought->getPrivate())
		{
			$par = new Paragraph("(private)");
			$output .= $par;
		}

		// thought text
		$div = new Div($this->formatService->formatText($thought->getBody()));
		$div->set("class", "bubble thought_body");
		$output .= $div;

		// By whom and when
		$a = new Anchor($this->formatService->getThinkerURL($thought->getThinkerId()),
			htmlspecialchars($thought->getThinkerId()));
		$par = new Paragraph("By $a ");
		$thoughtDate = date("D, M j, Y", $thought->getDate());
		$thoughtTime = date("g:i:s T", $thought->getDate());
		$par->addContent("on $thoughtDate at $thoughtTime");
		$output .= $par;

		return $output;
	}

	//
	// This function will print out one thought as a results listing
	// NOTE: make sure that the user has permission to see this thought
	//       before calling this function on it!
	//

	function drawThoughtListing($thought)
	{
		$div=new Div();
		$div->set("class", "bubble result");

		$div->addContent($this->getThoughtLink($thought));
		$div->addContent(new LineBreak());
		$div->addContent("By ".htmlspecialchars($thought->getThinkerId()));
		$div->addContent("&nbsp;(".date("M d, Y",$thought->getDate()).")");

		if($thought->getPrivate()) {
			$div->addContent("&nbsp;(private)");
		}

		return($div);

	}

	//
	// Gets a link to the thought
	//

	public function getThoughtLink($thought)
	{
		$a = new Anchor(
			$this->formatService->getThoughtURL($thought),
			htmlspecialchars($this->formatService->getExcerpt($thought))
		);

		return("" . $a);
	}
}
