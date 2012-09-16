<?php

// Error handling that ... actually prints a stack trace!!
// (c) 2010 Carlos Torchia under GNU GPL v2 - no warranty

function myErrorHandler($errno, $errstr)
{
	if(($errno & E_ERROR) || defined("THINKLOG_DEBUG"))
	{
		echo "<p><span style=\"color:#f00; font-weight: bold\">$errstr (errno $errno)</span></p>\n";

		// Print the backtrace
		$backtrace = debug_backtrace();
		echo "<p>\n";
		echo "<b>Call backtrace</b><br />\n";
		echo "<table border=\"1\">\n";
		echo "<tr><td><b>File</b></td><td><b>Line</b></td><td><b>Function</b></td></tr>\n";
		foreach($backtrace as $call)
		{
			echo "<tr>\n";
			echo "<td>" . (isset($call["file"]) ? $call["file"] : "") . "</td>\n";
			echo "<td>" . (isset($call["line"]) ? $call["line"] : "") . "</td>\n";
			echo "<td>" . (isset($call["function"]) ? $call["function"] : "") . "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "</p>\n";

		if(($errno & E_ERROR))
		{
			echo "<p>Fatal error, aborting</p>\n";
			exit;
		}
	}
}

set_error_handler("myErrorHandler");
