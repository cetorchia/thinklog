<?php
/**
 * Class containing the the list of JavaScript libraries we use.
 * PLEASE put include any scripts you use here!
 */

class IncludeScriptsHere
{
	public function __toString()
	{
		$output = "<script type=\"text/javascript\" src=\"http://d3js.org/d3.v2.min.js\"></script>\n";
		$output .= "<script type=\"text/javascript\" src=\"js/force.js\"></script>\n";
		$output .= "<script type=\"text/javascript\" src=\"js/stream.js\"></script>\n";
		return $output;
	}
}
