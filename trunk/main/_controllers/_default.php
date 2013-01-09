<?php

namespace Morrow;

class DefaultController extends Core\Loader {
	public function setup() {
		$this->View->setHandler('serpent');
	}
}
