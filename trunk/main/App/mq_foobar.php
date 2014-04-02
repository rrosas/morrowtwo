<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;


class PageController extends DefaultController {
	public function run() {
		$this->view->setHandler('plain');

		if ($this->messagequeue->process()) return;

		$job = $this->messagequeue->get($this->input->get('id'));
		sleep(3);
		$this->log->set(date('H:i:s'), $job['data']);
	}
}
