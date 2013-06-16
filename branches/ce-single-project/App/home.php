<?php

namespace App;
use Morrow\Debug;
use Morrow\Factory;

class PageController extends DefaultController {
	public function run() {
		/*
		$test = new \Morrow\Test('2');
		Debug::dump($test->get());

		$test = new \Test('3');
		Debug::dump($test->get());

		$test = new \Test\Test('3');
		Debug::dump($test->get());
		*/

		include(APP_PATH . 'models/Test.php');
		$test = new models\Test();
	}
}
