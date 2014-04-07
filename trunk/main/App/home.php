<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		//$this->_testStreams();
		//$this->_testMessageQueue();
		$this->_testForms();
		$this->_testValidator();
	}

	protected function _testValidator() {
		$input = array(
			'image' => '',
			'width' => '',
			'height' => '',
			'email' => 'test@cerdmann.com',
			'url' => 'http://www.disney.de',
		);
		$this->validator2->validate(
			$input,
			array(
				'image' => array('tmp_name' => 'images/ape.png'),
				'width' => array('tmp_name' => 'images/ape.png'),
				'height' => array('tmp_name' => 'images/ape.png'),
				'email' => 'test@cerdmann.com',
				'url' => 'http://www.disney.de',
			)
		);
	}

	protected function _testForms() {
		$fields = array(
			'email' => array('required' => true, 'checktype' => 'email'),
		);

		// at the moment loadDef HAS to be before setInput (setInput should not do anything)
		$this->form->loadDef(array('form_name' => $fields));
		$this->form->setInput($this->input->get());

		if ($this->form->isSubmitted('form_name')) {
			echo "Submitted";
			if ($this->form->validate('form_name')) {
				echo "Valid";
			}
		}
	}

	protected function _testStreams() {
		// You have to do this before working with the session
		$this->prepare('Db', $this->config->get('db'));
		
		Factory::load('Streams\Db:streamdb_sessions', 'dbs', $this->db, 'sessions');
		//unlink('db://images/test.jpg');
		//$this->session->set('debug', 'test');
		echo $this->session->get('debug');
		//print_r(scandir('sessions://'));

		Factory::load('Streams\File:streamfile_public', 'public', PUBLIC_PATH);

		#file_put_contents('public://test.jpg', 'ulfggdfgfdgdfgdf2');
		#unlink('public://test.jpg');



		
		//Factory::load('Streams\Db:streamdb_files', 'db', $this->db, 'files');
		//file_put_contents('db://images/test.jpg', 'ulfggdfgfdgdfgdf2');
		//file_get_contents('db://images/test.jpg');
		//unlink('db://images/test.jpg');
		//file_put_contents('db://images/test2.jpg', '123');

		//$bla = fopen('db://images/test.jpg', 'w');
		//$bla = fopen('sessions://images/test2.jpg', 'w');

		// var_dump(filetype('db://images/test.jpg'));
		// var_dump(file_exists('db://images/test.jpg'));
		// var_dump(is_readable('db://images/test.jpg'));
		// var_dump(is_writable('db://images/test.jpg'));
		// var_dump(is_executable('db://images/test.jpg'));
		// var_dump(filemtime('db://images/test.jpg'));
		// var_dump(touch('db://images/test.jpg'));

		//print_r( file_get_contents('db://images/test.jpg') );
	}

	protected function _testMessageQueue() {
		$this->messagequeue->set('mq/foobar', array('data' => '1'));
		$this->messagequeue->set('mq/foobar', array('data' => '2'));
		$this->messagequeue->set('mq/foobar', array('data' => array('foo' => 'bar')));
	}
}