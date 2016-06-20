#Implementation of Pattern "Reliable queue", using php and Redis

##Important
All examples you can find in tests/UsecaseTest.php

##Configuration
This library use [Predis](https://github.com/nrk/predis) to connect to Redis server.
So we need predis object in examples belowe

```
$this->client = new \Predis\Client([
	'scheme' => 'tcp',
	'host' => REDIS_SERVER_HOST,
	'port' => REDIS_SERVER_PORT,
]);
```

##Create a queue

```
$queue = new \RedisMq\Queue($this->client, $name);
```

##Add task (message) to queue

```
$messageArray = new \RedisMq\Message([
    'x' => 2,
    'string' => 'Message number 2',
    'rand' => rand(10000, 99999)
]);
$queue->addMessage($messageArray);

$messageString = new \RedisMq\Message('simple message');
$queue->addMessage($messageString);
```

##Play with tasks

```
$taskQty = 100;
$taskList = $queue->getTaskList($taskQty);
```

in progress...