<?php

namespace Morrow;

include_once(__DIR__ . '/../test2.class.php');

class Test extends \Test {
	protected $value;
	
	public function __construct($value = 'foo') {
		$this->value = $value;
	}
	
	public function get() {
		return 'Adapter "Test" initialized: ' . $this->value;
	}
}