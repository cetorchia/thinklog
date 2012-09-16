<?php

class Login
{
	protected $thinkerId;
	protected $password;

	public function getThinkerId() { return $this->thinkerId; }
	public function setThinkerId($thinkerId) { $this->thinkerId = $thinkerId; }

	public function getPassword() { return $this->password; }
	public function setPassword($password) { $this->password = $password; }
}
