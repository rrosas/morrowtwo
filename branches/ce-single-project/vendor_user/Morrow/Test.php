<?php

namespace Morrow;

include(__DIR__ . '/../test.class.php');

class Test extends \Test {
	protected $value;
	
	public function __construct($value = 'foo') {
		$this->value = $value;
	}
	
	public function get() {
		return 'Adapter "Test" initialized: ' . $this->value;
	}
}