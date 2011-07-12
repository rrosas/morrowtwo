<?php

class GlobalController extends Controller {
	public function setup() {
		$this->view->setHandler('serpent');
	}
}
