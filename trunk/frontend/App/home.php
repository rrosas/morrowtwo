<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		// $test = new Models\Test;
		// $test = Factory::load('\App\Models\Test');

		// 	$counter = $this->session->get('counter');
		// 	Debug::dump(++$counter);
		// 	$this->session->set('counter', $counter);

		Factory::load('Streams\Db:streamdb', 'db', Factory::load('Db', $this->config->get('db')));
		Factory::load('Streams\Db:streamdb2', 'db2', Factory::load('Db', $this->config->get('db')));
		
		$bla = file_get_contents('db://default/images/test.jpg', 'w');
		$bla = file_get_contents('db2://default/images/test2.jpg', 'w');

		//file_get_contents('db://images/test.jpg
		echo "fu";
		die();
	}
}