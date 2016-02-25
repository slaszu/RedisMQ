<?php

namespace RedisMq;

use RedisMq\TaskList;
use RedisMq\Message;
use Predis\Client;

class Queue {
	
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
	public function addMessage(Message $message) {
		
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
	public function getTaskList($size = 100) {
		
		
		return new TaskList();
	}
}
