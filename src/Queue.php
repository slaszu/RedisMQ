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
	 * Copy data from queue to new process list with given size
	 * 
	 * @param int $size
	 * @return TaskList
	 */
//	public function getTaskList($size = 100)
//	{
//
//		/**
//		 * copy part of queue to other redis list
//		 */
//		$taskListsKey = $this->getName() . ':task_lists';
//		$queueName = $this->getName();
//		
//		$script =
//			'
//				taskListUniqueName = KEYS[1]
//				taskListsKey = KEYS[2]
//				size = KEYS[3]
//				queueName = KEYS[4]
//				-- check if key is unique
//				is_unique = redis.call("sismember",taskListsKey, taskListUniqueName)
//				if is_unique != 0 then return 0 end
//				
//				messages = redis.call("lrange",queueName,0,size)
//				redis.call("sadd",taskListsKey,taskListUniqueName)
//				redis.call("lpush",taskListUniqueName,messages)
//				redis.call("ltrim",queueName,size,-1)
//				return 1
//			';
//		
//		
////		$taskListName = $this->client->transaction(function ($tx) use ($taskListsKey, $queueName, $size) {
////
//////			$messages = $tx->lrange($queueName, 0, $size);
//////			if (count($messages) == 0) {
//////				throw new Exception("Queue '$queueName' is empty or not exists at all !");
//////			}
////			
////			/**
////			 * create uniqu name
////			 */
////			$taskListUniqueName = md5(microtime() . mt_rand());
//////			$isUnique = $tx->sismember($taskListsKey, $taskListUniqueName);
//////			if ($isUnique != 0) {
//////				throw new Exception("Task list '$taskListUniqueName' exists in list '$taskListsKey', very rare problem, try run process again !");
//////			}
////			$tx->sadd($taskListsKey, $taskListUniqueName);
////
////			
////			//$tx->lpush($taskListUniqueName, $messages);
////			$tx->ltrim($queueName, 0, $size);
////			
////		});
//
//		var_dump($taskListName);
//		exit;
//		
//		return new TaskList();
//	}



	public function getTaskList($size = 100)
	{
		/**
		 * copy part of queue to other redis list
		 */
		$taskListUniqueName = $this->getName().':'.md5(microtime() . mt_rand());
		$queueName = $this->getName();

		$script = '
				local taskListUniqueName = KEYS[1]
				local queueName = KEYS[2]
				local size = KEYS[3]
				-- check if key is unique
				local is_unique = redis.call("exists", taskListUniqueName)
				if is_unique == 1 then return 0 end
				
				local messages = redis.call("lrange",queueName,0,size)
				for key,message in pairs(messages) do
					redis.call("lpush",taskListUniqueName,message)
				end
				redis.call("ltrim",queueName,size,-1)
				return 1
			';

		/**
		 * 1. check llen of queue
		 */
		/**
		 * 2. create task list
		 */
		$res = $this->client->eval($script, 3, $taskListUniqueName, $queueName, $size);
		if ($res == 0) {
			throw new Exception("Task list '$taskListUniqueName' exists in list '$taskListsKey', very rare problem, try run process again !");
		} elseif ($res == 1) {
			
			return new TaskList($taskListUniqueName);
			
		} else {
			throw new Exception("Strange problem occured ! res = ".  var_export($res, true));
		}
		
	}

}
