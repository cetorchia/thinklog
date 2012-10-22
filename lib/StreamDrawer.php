<?php

/**
 * Draws a stream graph for historical data of a set of keywords
 */

class StreamDrawer
{
	protected $id;
	protected $width;
	protected $height;
	protected $m;
	protected $keywordHistory;

	function __construct($id, $width, $height, $m, $keywordHistory) {
		$this->id = $id;
		$this->width = $width;
		$this->height = $height;
		$this->m = $m;
		$this->keywordHistory = $keywordHistory;
	}

	function draw() {
		$keywordHistory = json_encode($this->keywordHistory);
		$output = "<script type=\"text/javascript\">\n";
		$output .= "drawStream(\"$this->id\", $this->width, $this->height, $this->m, " .
		           "$keywordHistory);\n";
		$output .= "</script>\n";
		return $output;
	}
}
