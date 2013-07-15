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
		#Factory::load('Streams\Db:streamdb_sessions', 'sessions', 'sessions', $this->db);
		
		clearstatcache();

		//file_put_contents('db://images/test.jpg', 'ulf');
		//unlink('db://images/test2.jpg');
		//file_put_contents('db://images/test2.jpg', '123');

		//$bla = fopen('db://images/test.jpg', 'w');
		//$bla = fopen('sessions://images/test2.jpg', 'w');

		// var_dump(filetype('db://images/test.jpg'));
		// var_dump(file_exists('db://images/test.jpg'));
		// var_dump(is_readable('db://images/test.jpg'));
		// var_dump(is_writable('db://images/test.jpg'));
		// var_dump(is_executable('db://images/test.jpg'));
		// var_dump(filemtime('db://images/test.jpg'));
		//var_dump(touch('db://images/test.jpg'));

		//print_r( file_get_contents('db://images/test.jpg') );
	}
}