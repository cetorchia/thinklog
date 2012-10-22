<?php

class DBConnectionService
{
	public function __construct(&$services)
	{
	}

	//
	// Connects to the database
	// For now these methods are static because the connection handle
	// is global.
	//

	function connect()
	{
		if(!mysql_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PASSWORD)) {
			die("Could not connect to Thinklog database: ".mysql_error());
		}

		mysql_select_db(MYSQL_DB);
	}

	//
	// Closes connection to database
	//

	function close()
	{
		mysql_close();
	}
}
