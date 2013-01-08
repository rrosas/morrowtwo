<?php

namespace Morrow;

class DefaultController extends Core\Loader {
	public function setup() {
		$this->view->setHandler('serpent');
	}
}
