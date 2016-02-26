<?php

namespace RedisMq;

use RedisMq\TaskList;
use RedisMq\Message;
use RedisMq\Exception;
use Predis\Client;

class Queue
{

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var Client
	 */
	protected $client = null;

	/**
	 * @param Client $client
	 * @param string $name
	 */
	public function __construct(Client $client, $name)
	{
		$this->client = $client;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getQueueTaskListsName() {
		return $this->getName().'_task_lists';
	}
	
	/**
	 * @return Client
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * @param Message $message
	 */
	public function addMessage(Message $message)
	{

		/**
		 * @todo 
		 * add message to channel 
		 */
		$this->client->lpush($this->getName(), $message->getAsString());
	}

	/**
	 * @return int
	 */
	public function getLength() {
		return $this->client->llen($this->getName());
	}
	
	/**
	 * Copy data from queue to new process list with given size
	 * 
	 * @param int $size
	 * @return TaskList
	 */
	public function getTaskList($size = 100)
	{
		/**
		 * copy part of queue to other redis list
		 */
		$taskListUniqueName = $this->getName().':'.md5(microtime() . mt_rand());
		
		$queueName = $this->getName();
		$queueNameTaskLists = $this->getQueueTaskListsName();
		
		$timestamp = time();
		
		$script = '
				local taskListUniqueName = KEYS[1]
				local queueName = KEYS[2]
				local size = KEYS[3]
				local timestamp = KEYS[4]
				local queueNameTaskList = KEYS[5]

				-- check if key is unique
				local is_unique = redis.call("exists", taskListUniqueName)
				if is_unique == 1 then return 0 end
				
				local messages = redis.call("lrange",queueName, - size, -1)
				for key,message in pairs(messages) do
					redis.call("rpush",taskListUniqueName,message)
				end
				
				redis.call("hset",queueNameTaskList,taskListUniqueName,timestamp)
				redis.call("ltrim",queueName,0,size - 1) -- zero based index
				return 1
			';

		/**
		 * 1. check llen of queue
		 */
		if ( $this->getLength() == 0) {
			throw new Exception("Queue '$queueName' is empty or not exists !");
		}
		
		
		/**
		 * 2. create task list
		 */
		$res = $this->client->eval($script, 5, $taskListUniqueName, $queueName, $size, $timestamp, $queueNameTaskLists);
		if ($res == 0) {
			throw new Exception("Task list '$taskListUniqueName' exists, it is very rare problem, try run process again !");
		} elseif ($res == 1) {
			
			return new TaskList($taskListUniqueName, $this);
			
		} else {
			throw new Exception("Strange problem occured ! res = ".  var_export($res, true));
		}
		
	}

}
