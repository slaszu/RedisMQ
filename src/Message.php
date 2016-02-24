<?php

namespace RedisMq;

class Message {
	
	/**
	 * @var mixed
	 */
	protected $body;
	
	public function __construct($body)
	{
		$this->body;
	}
	
	public function getBody() {
		return $this->body;
	}
}
