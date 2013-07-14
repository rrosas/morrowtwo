<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$this->prepare('Db', $this->config->get('db'));

		// $test = new Models\Test;
		// $test = Factory::load('\App\Models\Test');

		// 	$counter = $this->session->get('counter');
		// 	Debug::dump(++$counter);
		// 	$this->session->set('counter', $counter);

		Factory::load('Streams\Db:streamdb_files', 'db', 'files', $this->db);
		Factory::load('Streams\Db:streamdb_sessions', 'sessions', 'sessions', $this->db);
		
		$bla = fopen('db://images/test.jpg', 'w');
		$bla = fopen('sessions://images/test2.jpg', 'w');

		die('fu');
	}
}