<?php

namespace App\Models;
use Morrow\Debug;
use Morrow\Factory;

class Test extends Factory {
	public function __construct() {
		Debug::dump('Model "Test" found.');
	}
}
