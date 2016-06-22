#Implementation of Pattern "Reliable queue", using php and Redis

##Important
All examples you can find in tests/UsecaseTest.php

##Configuration
This library use [Predis](https://github.com/nrk/predis) to connect to Redis server.
So we need predis object in examples below

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
// eg. this is message with array, but message could be a simple string either
$message = new \RedisMq\Message([
    'x' => 2,
    'string' => 'Message number 2',
    'rand' => rand(10000, 99999)
]);
$queue->addMessage($message);
```

##Play with tasks
###Get list of tasks to process

```
$taskQty = 100;
$taskList = $queue->getTaskList($taskQty);
```

###Get task detail

```
$task = $taskList->getTask();
$message = $task->getMessage();
$body = $messageArray->getBody();

//variable $body is an array given in section "Add task (message) to queue"
```

###Confirm task

```
$task = $taskList->getTask();

/*
 * .....
 * proccess message (task details) from task and if is all right then confirm this task
 * .....
 */

$task->confirm();
```

Task is removed from queue after confirmation.

##Repair queue
When you get TaskList from Queue, then Tasks are moved from Queue to TaskList.
Task which are in TaskList not exist any longer in Queue.

In some situations you may want to move task from TaskList back to Queue.
To do this use **repairTaskLists** method.
This method takes one param (time in seconds).
This is the minimum time that has elapsed since the creation of TaskLists.
This method check all Task Lists that was created from Queue.

**Important**: if all Tasks from TaskList are confirmed, then this TaskList is empty and Redis remove this task immediately.

```
$queue = new \RedisMq\Queue($this->client, $name);
$queue->repairTaskLists(60); // 60 is time in seconds
```