<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		Debug::dump($this->input->get('path'));

		$class = Factory::load('Docblock', '\Morrow\View');
		$this->view->setContent('class', $class->get());
	}
}