<?php

class GlobalController extends Loader {
	public function setup() {
		$this->view->setHandler('serpent');
	}
}
