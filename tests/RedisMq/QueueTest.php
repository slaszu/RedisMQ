<?php

class QueueTest extends \PHPUnit_Framework_TestCase
{

	protected $client = null;

	public function setUp()
	{

		$this->client = new \Predis\Client([
			'scheme' => 'tcp',
			'host' => REDIS_SERVER_HOST,
			'port' => REDIS_SERVER_PORT,
		]);
	}

	public function testQueueInit()
	{
		$name = 'phpunit_tests';
		$queue = new \RedisMq\Queue($this->client, $name);

		$this->assertEquals($name, $queue->getName());
	}

}
