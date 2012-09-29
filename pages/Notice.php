<?php

require_once(DOC_ROOT . "/lib/html.php");

/**
 * Div element for notifying user tips
 */

class Notice extends Div {
	public function __construct($content) {
		parent::__construct($content);
		$this->set("class", "notice");
	}
}
