<?php

namespace RedisMq;

use RedisMq\Message;
use RedisMq\TaskList;

class Task {
	
	/**
	 * @var Message
	 */
	protected $message;
	
	/**
	 * @var TaskList 
	 */
	protected $taskList;
	
	/**
	 * @param Message $message
	 * @param TaskList $taskList
	 */
	public function __construct(Message $message, TaskList $taskList )
	{
		$this->message = $message;
		$this->taskList = $taskList;
	}
	
	/**
	 * @return Message
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @return TaskList
	 */
	public function getTaskList() {
		return $this->getTaskList();
	}
	
	/**
	 * return bool
	 */
	public function confirm() {
		
	}
	
}
