<?php

class QueueTest extends \PHPUnit_Framework_TestCase
{

	protected $client = null;
	protected $message = null;
	
	public function setUp()
	{
		$this->client = new \Predis\Client([
			'scheme' => 'tcp',
			'host' => REDIS_SERVER_HOST,
			'port' => REDIS_SERVER_PORT,
		]);
		
		$this->message = new \RedisMq\Message([
			'x' =>1,
			'y' => [
				'y1' => 11,
				'y2' => 22
			],
			'rand' => rand(10000,99999)
		]);
	}

	public function testQueueInit()
	{
		$name = 'phpunit_tests';
		$queue = new \RedisMq\Queue($this->client, $name);

		/**
		 * clear queue
		 */
		$queue->getClient()->del($name);
		
		$length = $queue->getClient()->llen($name);
		
		$this->assertEquals($name, $queue->getName());
		$this->assertEquals($length, 0);
		
		return $queue;
	}

	/**
	 * @depends testQueueInit
	 * @param \RedisMq\Queue $queue
	 */
	public function testAddMessage(\RedisMq\Queue $queue) {
		$queue->addMessage($this->message);
		
		$checkMessage = $queue->getClient()->lrange($queue->getName(),0,0);
		
		$messageToCheck = new \RedisMq\Message();
		$messageToCheck->setFromString($checkMessage[0]);
		
		$this->assertEquals($this->message, $messageToCheck);
		
		return $queue;
	}
			
	/**
	 * @depends testAddMessage
	 * @param \RedisMq\Queue $queue
	 */
	public function testGetTaskList(\RedisMq\Queue $queue)
	{
		$taskList = $queue->getTaskList(5);
	}
	
}
