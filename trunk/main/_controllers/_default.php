<?php

namespace Morrow;

class DefaultController extends Loader {
	public function setup() {
		$this->View->setHandler('serpent');
	}
}
