<?php

require_once(DOC_ROOT . "/pages/Section.php");

class CopyrightSection extends Section
{
	public function getContent()
	{
		$output = "";

		$output .= "<div id=\"disclaimer\">\n";
		$output .= "<h3>Disclaimer</h3>\n";
		$output .= "<p>\n";
		$output .= "Thinklog is in still <b>under development</b>; use at your at <b>own risk</b>.\n";
		$output .= "</p>\n";
		$output .= "</div>\n";

		$output .= "<div id=\"copyright\">\n";
		$output .= "Thinklog &copy; 2010 Carlos E. Torchia <br>\n";
		$output .= "</div>\n";

		return $output;
	}
}
