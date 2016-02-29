<?php

namespace RedisMq;

class Message {
	
	/**
	 * @var mixed
	 */
	protected $body;
	
	public function __construct($body = null)
	{
		$this->body = $body;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function getAsString()
	{
		return serialize($this->getBody());
	}
	
	public function setFromString($string) {
		$this->body = unserialize($string);
	}

}
