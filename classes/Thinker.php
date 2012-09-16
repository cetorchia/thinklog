<?php

class Thinker
{
	protected $id;
	protected $name;
	protected $about;

	public function getId() { return $this->id; }
	public function setId($id) { $this->id = $id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

	public function getAbout() { return $this->about; }
	public function setAbout($about) { $this->about = $about; }
}
