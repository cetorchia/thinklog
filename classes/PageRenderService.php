<?php

require_once(DOC_ROOT . "/lib/html.php");

class PageRenderService
{

	protected $formatService;

	public function __construct($services)
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

		$ul = new UnorderedList();

		foreach($thoughts as $thought) {
			$ul->addContent($this->drawThoughtLi($thought));
		}

		return "" . $ul;
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

		// Print the heading

		$thoughtDate = date("r",$thought->getDate());

		$a = new Anchor($this->formatService->getThoughtURL($thought), htmlspecialchars($thoughtDate));
		$heading = new Heading("2",$a);
		$output .= $heading;

		// Private?
		if($thought->getPrivate())
		{
			$par = new Paragraph("(private)");
			$output .= $par;
		}

		// thought text
		$par = new Paragraph($this->formatService->formatText($thought->getBody()));
		$output .= $par;

		// By whom
		$a = new Anchor($this->formatService->getThinkerURL($thought->getThinkerId()),
			htmlspecialchars($thought->getThinkerId()));
		$par = new Paragraph("By " . $a);
		$output .= $par;

		return $output;
	}

	//
	// This function will print out one thought as a list element.
	// NOTE: make sure that the user has permission to see this thought
	//       before calling this function on it!
	//

	function drawThoughtLi($thought)
	{
		$li=new ListElement();
		$br=new LineBreak();

		$li->addContent($this->getThoughtLink($thought));
		$li->addContent($br);
		$li->addContent("&nbsp;by ".htmlspecialchars($thought->getThinkerId()));
		$li->addContent("&nbsp;(".date("M d, Y",$thought->getDate()).")");

		if($thought->getPrivate()) {
			$li->addContent("&nbsp;(private)");
		}

		return("" . $li);

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
