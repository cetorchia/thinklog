<?php

require_once(DOC_ROOT . "/lib/html.php");
require_once(DOC_ROOT . "/pages/Page.php");

class AboutPage extends Page
{
	public function getContent()
	{
		$output = "<div id=\"about\" class=\"section\">\n";
		$output .= file_get_contents(DOC_ROOT . "/pages/about.html");
		$output .= "</div>\n";

		return $output;
	}
}
