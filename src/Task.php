<?php

namespace RedisMq;

use \RedisMq\Message;

class Task {
	
	/**
	 * @return Message
	 */
	public function getMessage() {
		return new Message();
	}
	
	/**
	 * return bool
	 */
	public function confirm() {
		
	}
	
}
