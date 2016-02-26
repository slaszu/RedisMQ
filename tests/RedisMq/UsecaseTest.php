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

		$this->message[] = new \RedisMq\Message([
			'x' => 1,
			'y' => [
				'y1' => 11,
				'y2' => 22
			],
			'rand' => rand(10000, 99999)
		]);

		$this->message[] = new \RedisMq\Message([
			'x' => 2,
			'string' => 'Message number 2',
			'rand' => rand(10000, 99999)
		]);
		
		$this->message[] = new \RedisMq\Message([
			'x' => 3,
			'string' => 'Message number 3',
			'rand' => rand(10000, 99999)
		]);
		
		$this->message[] = new \RedisMq\Message([
			'x' => 4,
			'string' => 'Message number 4',
			'rand' => rand(10000, 99999)
		]);
	}

	public function testQueueInit()
	{
		$name = 'phpunit_tests';
		$queue = new \RedisMq\Queue($this->client, $name);

		/**
		 * clear queue
		 */
		$keys = $queue->getClient()->keys($name.'*');
		foreach($keys as $key) {
			$queue->getClient()->del($key);
		}
		
		$length = $queue->getClient()->llen($name);

		$this->assertEquals($name, $queue->getName());
		$this->assertEquals($length, 0);

		return $queue;
	}

	/**
	 * @depends testQueueInit
	 * @param \RedisMq\Queue $queue
	 */
	public function testAddMessage(\RedisMq\Queue $queue)
	{

		foreach ($this->message as $m) {
			$queue->addMessage($m);
			$checkMessage = $queue->getClient()->lrange($queue->getName(), 0, 0);
			$messageToCheck = new \RedisMq\Message();
			$messageToCheck->setFromString($checkMessage[0]);
			$this->assertEquals($m, $messageToCheck);
		}
		
		$qty = $queue->getLength();
		$this->assertEquals($qty, count($this->message));


		return $queue;
	}

	/**
	 * @depends testAddMessage
	 * @param \RedisMq\Queue $queue
	 */
	public function testGetTaskList(\RedisMq\Queue $queue)
	{
		$taskQty = floor(count($this->message) / 2);
		$queueQty = count($this->message) - $taskQty;
		
		$taskList = $queue->getTaskList($taskQty);

		$this->assertNotEmpty($taskList->getName());
		$this->assertEquals($queue, $taskList->getQueue());
		$this->assertEquals($taskList->getLength(), $taskQty, 'check task list size');
		$this->assertEquals($queue->getLength(), $queueQty, 'check queue size');
		
	}

}
