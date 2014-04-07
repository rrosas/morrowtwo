<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		//$this->_testStreams();
		//$this->_testMessageQueue();
		$this->_testForms();
		//$this->_testValidator();
	}

	protected function _testValidator() {
		$this->validator2->add('eq2', function($input, $value) {
			if ($value == 2) return true;
			return false;
		});

		$input = array(
			'redundant'		=> 'foobar',
			'eq2'			=> '2',
			'optional'		=> '',
			'required'		=> 'foobar',
			'required2'		=> 'foobar',
			'same'			=> 'foobar',
			'integer'		=> '1',
			'numeric'		=> '2.0',
			'min'			=> '1',
			'max'			=> '1',
			'length'		=> '123456',
			'regex'			=> '045,34',
			'in'			=> '2',
			'image'			=> 'images/ape.png',
			'array'			=> array(),
			'uris'			=> array(
				'email'	=> 'test@cerdmann.com',
				'url'	=> 'http://www.example.com/434',
				'ip'	=> '193.168.2.5',
			),
			'date'			=> '2013-02-17',
			'date2'			=> '2013-02-17',
			'after'			=> '2013-02-17',
			'before'		=> '2013-02-17',
		);
		$rules =  array(
			'eq2'			=> 'eq2',
			'optional'		=> 'optional',
			'required'		=> 'required',
			'required2'		=> 'required:required,foobar',
			'same'			=> 'same:required',
			'integer'		=> 'integer',
			'numeric'		=> 'numeric',
			'min'			=> 'min:.5',
			'max'			=> 'max:1.1',
			'length'		=> 'length:3,6',
			'regex'			=> 'regex:/^[\d,]+$/',
			'in'			=> 'in:1,2,3',
			'image'			=> 'image:gif,png|width:1233|height:1233',
			'array'			=> 'array',
			'uris.email'	=> 'email',
			'uris.url'		=> 'url:http,https',
			'uris.ip'		=> 'ip:ipv4',
			'date'			=> 'date',
			'date2'			=> 'date:%Y-%m-%d',
			'after'			=> 'after:2013-02-16',
			'before'		=> 'before:2013-02-18',
		);

		$input = $this->validator2->filter($input, $rules);
		Debug::dump($input);
		$input = $this->validator2->filter($input, $rules, true);
		Debug::dump($input);
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