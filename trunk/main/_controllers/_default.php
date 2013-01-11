<?php

namespace Morrow;

class DefaultController extends Factory {
	public function setup() {
		$this->view->setHandler('serpent');
	}
}
