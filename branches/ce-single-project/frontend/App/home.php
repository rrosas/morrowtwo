<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		
		Debug::dump($this->input->get());
		$test = new Models\Test;
		$test = Factory::load('\App\Models\Test');
	}
}