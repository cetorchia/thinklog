<?php

class TimerService
{
	protected $timers;

	public function __construct($services)
	{
		$this->timers = array();
		$this->start('total');
	}

	//
	// Start a timer
	//
	public function start($timer)
	{
		$this->timers[$timer] = array(
			'start' => $this->getTime(),
			'stop'  => null,
		);
	}

	//
	// Read a timer
	//
	public function read($timer)
	{
		if(!isset($this->timers[$timer]['stop']))
		{
			return($this->getTime() - $this->timers[$timer]['start']);
		}

		return($this->timers[$timer]['stop'] - $this->timers[$timer]['start']);
	}

	//
	// Stop a timer
	//
	public function stop($timer)
	{
		$this->timers[$timer]['stop'] = $this->getTime();
		return($this->timers[$timer]['stop'] - $this->timers[$timer]['start']);
	}

	//
	// Returns the time
	//
	protected function getTime()
	{
		return(gettimeofday(true));
	}
}
