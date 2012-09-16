<?php

class Thought
{
	protected $id;
	protected $thinkerId;
	protected $date;
	protected $body;
	protected $private;

	public function getId() { return $this->id; }
	public function setId($id) { $this->id = $id; }

	public function getThinkerId() { return $this->thinkerId; }
	public function setThinkerId($thinkerId) { $this->thinkerId = $thinkerId; }

	public function getDate() { return $this->date; }
	public function setDate($date) { $this->date = $date; }

	public function getBody() { return $this->body; }
	public function setBody($body) { $this->body = $body; }

	public function getPrivate() { return $this->private; }
	public function setPrivate($private) { $this->private = $private; }

}
