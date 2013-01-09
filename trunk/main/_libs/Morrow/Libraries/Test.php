<?php

namespace Morrow\Libraries;

include(__DIR__ . '/../Externals/test.class.php');

class Test extends \Test {
	protected $value;
	
	public function __construct($value = 'foo') {
		$this->value = $value;
	}
	
	public function get() {
		return 'Adapter "Test" initialized: ' . $this->value;
	}
}