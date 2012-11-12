<?php

namespace Morrow;

class DefaultController extends Loader {
	public function setup() {
		$this->view->setHandler('serpent');
	}
}
