<?php

namespace Morrow\Core\Libraries;

include(PROJECT_PATH . '_user/externals/test.class.php');

class Test extends \Test {
	protected $value;
	
	public function __construct($value = 'foo') {
		$this->value = $value;
	}
	
	public function get() {
		return 'Adapter "Test" initialized: ' . $this->value;
	}
}