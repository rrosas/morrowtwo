<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$item = $this->mq->getItem();

		try {
			sleep(3);
			if ($item['data'] == '2') throw new \Exception('Shit');
			$this->log->set(date('H:i:s'), $item['data']);

			$success = true;
		} catch (\Exception $e) {
			$this->log->set($e->__toString(), $item);
			
			$success = false;
		}

		$this->mq->next($success);
	}
}