<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$class = '\\' . implode('\\', $this->input->get('path'));

		$class = Factory::load('Docblock', $class);
		$this->view->setContent('class', $class->get());
	}
}