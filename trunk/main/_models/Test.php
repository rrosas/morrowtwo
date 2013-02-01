<?php

namespace Morrow\Models;

//use Morrow\Debug;

class Test extends \Morrow\Factory {
	public function __construct() {
		Debug::dump('Model "Test" found.');
	}
}
