<?php

namespace App;
use Morrow\Factory;

class PageController extends DefaultController {
	public function run() {
		
		$test = new Models\Test;
		$test = Factory::load('\App\Models\Test');
	}
}