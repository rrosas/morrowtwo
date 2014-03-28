<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		sleep(5);
		$this->log->set($this->input->get());
		die();
	}
}