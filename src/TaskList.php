<?php

namespace RedisMq;

use RedisMq\Queue;
use RedisMq\Task;

class TaskList
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var Queue
	 */
	protected $queue;

	/**
	 * @param string $name
	 * @param Queue $queue
	 */
	public function __construct($name, Queue $queue)
	{
		$this->name = $name;
		$this->queue = $queue;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @return Queue
	 */
	public function getQueue() {
		return $this->queue;
	}

	/**
	 * @return int
	 */
	public function getLength() {
		return $this->queue->getClient()->llen($this->getName());
	}
	
	/**
	 * @return Task
	 */
	public function getTask() {
		
		/**
		 * get first
		 */
		$checkMessage = $this->queue->getClient()->lrange($this->getName(),0,0);
		
		$messageToCheck = new \RedisMq\Message();
		$messageToCheck->setFromString($checkMessage[0]);
		
		$task = new Task($messageToCheck, $this);
		
		return $task;
		
	}
}
