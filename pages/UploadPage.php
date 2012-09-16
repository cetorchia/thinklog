<?php
require_once(DOC_ROOT . "/pages/Page.php");

class UploadPage extends Page
{
	public function getContent()
	{
		// For adding one thought

		$addDiv = new Div();
		$addDiv->set("class","section");
		$s = "<h1>What's on your mind?</h1>\n";
		$s .= "<form method=\"POST\" action\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"add\" value=\"1\" />\n";
		$s .= "<textarea rows=\"5\" cols=\"60\" name=\"body\"></textarea>\n";
		$s .= "<br />\n";
		$s .= "<input type=\"submit\" value=\"submit\" />\n";
		$s .= "<input name=\"private\" type=\"checkbox\" value=\"1\"/>Private\n";
		$s .= "</form>\n";
		$addDiv->addContent($s);

		// For XML thoughts from file

		$uploadDiv = new Div();
		$uploadDiv->set("class","section");
		$s = "<h2>Upload thoughts from file</h2>\n";
		$s .= "<p>Upload your thoughts in XML or RSS format from a file on your computer.</p>\n";
		$s .= "<form method=\"POST\"  enctype=\"multipart/form-data\" action\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"upload\" value=\"1\" />\n";
		$s .= "<input name=\"file\" type=\"file\" value=\"1\" />\n";
		$s .= "<input type=\"submit\" value=\"upload\" />\n";
		$s .= "</form>\n";
		$uploadDiv->addContent($s);

		// For XML thoughts from URL

		$fromURLDiv = new Div();
		$fromURLDiv->set("class","section");
		$s = "<h2>Upload thoughts from URL</h2>\n";
		$s .= "<p>Upload your thoughts in XML or RSS format from a URL.</p>\n";
		$s .= "<form method=\"POST\" action\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"fromURL\" value=\"1\" />\n";
		$s .= "<input name=\"url\" type=\"text\" />\n";
		$s .= "<input type=\"submit\" value=\"upload\" />\n";
		$s .= "</form>\n";
		$fromURLDiv->addContent($s);

		return("".
		       $addDiv . "\n" .
		       $uploadDiv . "\n" .
		       $fromURLDiv . "\n"
		);
	}
}
