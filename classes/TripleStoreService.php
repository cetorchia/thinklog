<?php
require_once(DOC_ROOT."arc/ARC2.php");

class TripleStoreService
{
	protected $store = null;

	function __construct($services)
	{
		$config = array(
			'db_name' => MYSQL_DB,
			'db_host' => MYSQL_HOST,
			'db_user' => MYSQL_USER,
			'db_pwd'  => MYSQL_PASSWORD,
			'store_name' => STORE_NAME,
			'max_errors' => 100,
		);

		$this->store = ARC2::getStore($config);
		if(!$this->store->isSetup())
		{
			$this->store->setUp();
			$this->loadSchema();
		}
	}

	function getStore()
	{
		return $this->store;
	}

	function loadSchema()
	{
		$this->store->query("LOAD <".THINKLOG_URL."/schema.nt>","raw");
		if(($errs = $this->store->getErrors()))
		{
			echo "<p>Couldn't load schema.nt:</p>\n";
			echo "<pre>\n";
			var_dump($errs);
			echo "</pre>\n";
			exit;
		}
	}
}
