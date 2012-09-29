<?php
require_once(DOC_ROOT . "/pages/Page.php");
require_once(DOC_ROOT . "/pages/Notice.php");

class AddPage extends Page
{
	public function getContent()
	{
		// For adding one thought

		$addDiv = new Div();
		$addDiv->set("class","section");
		$s = "<h1>What's on your mind?</h1>\n";
		$s .= new Notice(NOTICE_ADD_THOUGHTS);
		$s .= "<form method=\"POST\" action=\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"add\" value=\"1\" />\n";
		$s .= "<textarea rows=\"5\" cols=\"60\" name=\"body\"></textarea>\n";
		$s .= "<br />\n";
		$s .= "<input type=\"submit\" value=\"add\" />\n";
		$s .= "<input name=\"private\" type=\"checkbox\" value=\"1\"/>Private\n";
		$s .= "</form>\n";
		$addDiv->addContent($s);

		// For thoughts from Twitter

		$fromTwitterDiv = new Div();
		$fromTwitterDiv->set("class","section");
		$s = "<h2>Add thoughts from Twitter</h2>\n";
		$s .= "<p>Add your thoughts from a Twitter search</p>\n";
		$s .= "<form method=\"POST\" action=\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"fromTwitter\" value=\"1\" />\n";
		$s .= "<input name=\"twitterQuery\" type=\"text\" />\n";
		$s .= "<input type=\"submit\" value=\"search & add\" />\n";
		$s .= "</form>\n";
		$fromTwitterDiv->addContent($s);

		// For thoughts from URL

		$fromURLDiv = new Div();
		$fromURLDiv->set("class","section");
		$s = "<h2>Add thoughts from URL</h2>\n";
		$s .= "<p>Add your thoughts in XML or RSS format from a URL.</p>\n";
		$s .= "<form method=\"POST\" action=\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"fromURL\" value=\"1\" />\n";
		$s .= "<input name=\"url\" type=\"text\" />\n";
		$s .= "<input type=\"submit\" value=\"add\" />\n";
		$s .= "</form>\n";
		$fromURLDiv->addContent($s);

		// For thoughts from file

		$fromFileDiv = new Div();
		$fromFileDiv->set("class","section");
		$s = "<h2>Add thoughts from a file</h2>\n";
		$s .= "<p>Add your thoughts in JSON, XML, RSS format from a file on your computer.</p>\n";
		$s .= "<form method=\"POST\"  enctype=\"multipart/form-data\" action\"./\">\n";
		$s .= "<input type=\"hidden\" name=\"fromFile\" value=\"1\" />\n";
		$s .= "<input name=\"file\" type=\"file\" value=\"1\" />\n";
		$s .= "<input type=\"submit\" value=\"add\" />\n";
		$s .= "</form>\n";
		$fromFileDiv->addContent($s);

		return("".
		       $addDiv . "\n" .
		       $fromTwitterDiv . "\n" .
		       $fromURLDiv . "\n" .
		       $fromFileDiv . "\n"
		);
	}
}
