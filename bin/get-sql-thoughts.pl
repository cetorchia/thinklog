#!/usr/bin/perl -w
#
# Just to extract the body out of some SQL output of 
# thoughts from the database.
#

print "<thoughts>\n";

while($x = <>)
{
	# Get the 4th, 5th, and 6th columns
	if($x =~ /^\|([^|]*)\|([^|]*)\|([^|]*)\|([^|]*)\|([^|]*)\|([^|]*)\|/)
	{
		# Extract the strings from the columns
		$body = $4;
		$private = $5;

		# Trim the strings
		$body =~ s/^\s*//;
		$body =~ s/\s*$//;
		$private =~ s/^\s*//;
		$private =~ s/\s*$//;

		# Print the XML
		print "\n\t<thought>\n";
		print "\t\t<body>$body</body>\n";
		print "\t\t<private/>\n" if $private;
		print "\t</thought>\n\n";
	}
}

print "</thoughts>\n";
