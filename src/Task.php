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
		return $this->taskList;
	}
	
	/**
	 * return bool
	 */
	public function confirm() {
		
		/**
		 * clear task and task list
		 * 1. del task
		 * 2. if it was last task then del task list from "queue task list"
		 */
		
		$taskListUniqueName = $this->getTaskList()->getName();
		$queueName = $this->getTaskList()->getQueue()->getName();
		$queueNameTaskList = $this->getTaskList()->getQueue()->getQueueTaskListsName();
		$message = $this->getMessage()->getAsString();
		
		$script = '
				local taskListUniqueName = KEYS[1]
				local queueName = KEYS[2]
				local message = KEYS[3]
				local queueNameTaskList = KEYS[4]
				
				-- del task
				redis.call("lrem",taskListUniqueName,0,message)
				
				-- del task list from queue task list
				if (redis.call("exists",taskListUniqueName) == 0) then
					redis.call("hdel",queueNameTaskList,taskListUniqueName)
				end
				
				return 1
			';

		$client = $this->getTaskList()->getQueue()->getClient();
		$client->eval($script, 4, $taskListUniqueName, $queueName, $message, $queueNameTaskList);
		
		return true;
	}
	
}
