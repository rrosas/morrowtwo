<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;


class PageController extends DefaultController {
	public function run() {
		$this->view->setHandler('plain');

		if ($this->messagequeue->process()) return;

		sleep(3);
		$this->log->set(date('H:i:s'), $this->input->get('data'));
	}
}
