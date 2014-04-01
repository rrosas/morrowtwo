<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;


class PageController extends DefaultController {
	public function run() {
		$this->view->setHandler('plain');

		if ($this->mq->process()) return;

		$job = $this->mq->get($this->input->get('id'));
		sleep(3);
		$this->log->set(date('H:i:s'), $job['data']);
	}
}
