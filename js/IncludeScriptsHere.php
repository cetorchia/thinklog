<?php
/**
 * Class containing the the list of JavaScript libraries we use.
 * PLEASE put include any scripts you use here!
 */

class IncludeScriptsHere
{
	public function __toString()
	{
		$output = "<script type=\"text/javascript\" src=\"js/d3.v3.min.js\"></script>\n";
		$output .= "<script type=\"text/javascript\" src=\"js/force.js\"></script>\n";
		$output .= "<script type=\"text/javascript\" src=\"js/stream.js\"></script>\n";
		return $output;
	}
}
