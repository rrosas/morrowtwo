<?php

include_once(__DIR__ . '/test2.class.php');

class Test extends Test2 {
	protected $value;
	
	public function __construct($value = 'foo') {
		$this->value = $value;
	}
	
	public function get() {
		return 'Direct "Test" initialized: ' . $this->value;
	}
}