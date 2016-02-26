<?php

class QueueTest extends \PHPUnit_Framework_TestCase
{
	protected $queueName = 'phpunit_tests';
	protected $client = null;
	protected static $message = null;

	public function setUp()
	{
		$this->client = new \Predis\Client([
			'scheme' => 'tcp',
			'host' => REDIS_SERVER_HOST,
			'port' => REDIS_SERVER_PORT,
		]);

		if (self::$message === null) {

			self::$message[] = new \RedisMq\Message([
				'x' => 1,
				'y' => [
					'y1' => 11,
					'y2' => 22
				],
				'rand' => rand(10000, 99999)
			]);

			self::$message[] = new \RedisMq\Message([
				'x' => 2,
				'string' => 'Message number 2',
				'rand' => rand(10000, 99999)
			]);

			self::$message[] = new \RedisMq\Message([
				'x' => 3,
				'string' => 'Message number 3',
				'rand' => rand(10000, 99999)
			]);

			self::$message[] = new \RedisMq\Message([
				'x' => 4,
				'string' => 'Message number 4',
				'rand' => rand(10000, 99999)
			]);
		}
	}

	public function testQueueInit()
	{
		$name = $this->queueName;
		$queue = new \RedisMq\Queue($this->client, $name);

		/**
		 * clear queue
		 */
		$keys = $queue->getClient()->keys($name . '*');
		foreach ($keys as $key) {
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

		foreach (self::$message as $m) {
			$queue->addMessage($m);
			$checkMessage = $queue->getClient()->lrange($queue->getName(), 0, 0);
			$messageToCheck = new \RedisMq\Message();
			$messageToCheck->setFromString($checkMessage[0]);
			$this->assertEquals($m, $messageToCheck);
		}

		$qty = $queue->getLength();
		$this->assertEquals($qty, count(self::$message));


		return $queue;
	}

	/**
	 * @depends testAddMessage
	 * @param \RedisMq\Queue $queue
	 */
	public function testGetTaskList(\RedisMq\Queue $queue)
	{
		$taskQty = floor(count(self::$message) / 2);
		$queueQty = count(self::$message) - $taskQty;

		$taskList = $queue->getTaskList($taskQty);

		$this->assertNotEmpty($taskList->getName());
		$this->assertEquals($queue, $taskList->getQueue());
		$this->assertEquals($taskList->getLength(), $taskQty, 'check task list size');
		$this->assertEquals($queue->getLength(), $queueQty, 'check queue size');

		return $taskList;
	}

	/**
	 * @depends testGetTaskList
	 * @param \RedisMq\TaskList $taskList
	 */
	public function testGetFirstTask(\RedisMq\TaskList $taskList)
	{
		$task = $taskList->getTask();
		$message = $task->getMessage();
		$this->assertEquals($message, self::$message[0], 'check first task');
		
		// secound time should be the same message
		$task = $taskList->getTask();
		$message = $task->getMessage();
		$this->assertEquals($message, self::$message[0], 'check first task again');
		
		/**
		 * test message body
		 */
		$body = $message->getBody();
		
		$this->assertInternalType('array',$body);
		$this->assertArrayHasKey('x', $body);
		$this->assertEquals($body['x'],1);
		
		
		return $taskList;
	}
	
	/**
	 * @depends testGetFirstTask
	 * @param \RedisMq\TaskList $taskList
	 */
	public function testTasksConfirm(\RedisMq\TaskList $taskList)
	{
		$client = $taskList->getQueue()->getClient();
		$queueTaskListsName = $taskList->getQueue()->getQueueTaskListsName();
		$taskListName = $taskList->getName();
		
		$qty = $taskList->getLength();
		
		for($i = 1; $i <= $qty; $i++) {
			// queue task list shoud contain this task list
			$res = $client->hget($queueTaskListsName, $taskListName);
			$this->assertNotNull($res);
			
			$task = $taskList->getTask();
			$task->confirm();
			
			// check if task list is empty and was removed
			$length = $taskList->getLength();
			$this->assertEquals($length, $qty - $i, "check task list size after $i confirmed tasks ");
		}
		
		// check if task list is empty and was removed
		$qtyAll = $taskList->getLength();
		$this->assertEquals($qtyAll, 0, 'check task list size after confirmed all tasks');
		
		// queue task list shoud not contain this task list
		$res = $client->hget($queueTaskListsName, $taskListName);
		$this->assertNull($res);
		
		return $taskList;
	}

	
	/**
	 * @depends testTasksConfirm
	 * @param \RedisMq\TaskList $taskList
	 */
	public function testRepairQueue(\RedisMq\TaskList $taskList)
	{
		$queue = $taskList->getQueue();
		$newTaskList = $queue->getTaskList();
		
		$this->assertEquals($queue->getLength(), 0, 'check queue after last task list get');
		
		/**
		 * now is queue and task list
		 * but exception occured and worker for task list stop working
		 * for this kind of task list we need put all data back again to top of queue
		 */
		
		// we dont now taks list name
		$newTaskList = null;
		
		// we know only queue name
		$name = $this->queueName;
		
		$queue = new \RedisMq\Queue($this->client, $name);
		//$res = $queue->repairTaskLists(-1);
		
		//var_dump($res);
	}
	
}
