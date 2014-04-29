<?php

namespace App;
use Morrow\Factory;
use Morrow\Debug;

class PageController extends DefaultController {
	public function run() {
		$this->_testValidator();
		$this->_testForms();
		//$this->_testStreams();
		//$this->_testMessageQueue();
	}

	protected function _testValidator() {
		// for debugging
		$this->input->set('redundant', 'foobar');

		$this->validator2->add('captcha', function($input, $value, $session_captcha) {
			if (!is_string($value) || $value !== $session_captcha) return false;
			return true;
		}, 'Wrong Captcha.');

		$this->validator2->setMessages(array(
			'equal'			=> 'Bullshit',
		));

		$input = array(
			'redundant'		=> 'foobar',
			'captcha'		=> '43242',
			'optional'		=> '',
			'required'		=> 'foobar',
			'required2'		=> 'foobar',
			'equal'			=> '2',
			'same'			=> 'foobar',
			'number'		=> '1',
			'numeric'		=> '2.0',
			'min'			=> '1',
			'max'			=> '1',
			'minlength'		=> '123456',
			'maxlength'		=> '123456',
			'regex'			=> '045,34',
			'in'			=> '2',
			'image'			=> 'images/ape.png',
			'array'			=> array(),
			'uris'			=> array(
				'email'	=> 'test@example.com',
				'url'	=> 'http://www.example.com/434',
				'ip'	=> '193.168.2.5',
			),
			'date'			=> '2013-02-30',
			'before'		=> '2013-02-17',
			'after'			=> '2013-02-17',
			'age'			=> '2000-02-18',
		);
		$rules =  array(
			'captcha'		=> array('captcha' => $this->session->get('captcha')),
			'optional'		=> array('optional', 'equal' => 2),
			'required'		=> array('required'),
			'required2'		=> array('required' => array('required' => 'foobar')),
			'equal'			=> array('equal' => 2),
			'same'			=> array('same' => 'required'),
			'number'		=> array('number'),
			'numeric'		=> array('numeric'),
			'min'			=> array('min' => .5),
			'max'			=> array('max' => 1.1),
			'minlength'		=> array('minlength' => 3),
			'maxlength'		=> array('maxlength' => 6),
			'regex'			=> array('regex' => '/^[\d,]+$/'),
			'in'			=> array('in' => array(1,2,3)),
			'image'			=> array('image' => array('gif','png'), 'width' => 1233, 'height' => 1233),
			'array'			=> array('array'),
			'uris.email'	=> array('email'),
			'uris.url'		=> array('url' => array('http','https')),
			'uris.ip'		=> array('ip' => array('ipv4')),
			'date'			=> array('date' => '%Y-%m-%d'),
			'before'		=> array('before' => '2013-02-18'),
			'after'			=> array('after' => '2013-02-16'),
			'age'			=> array('age' => array(18, 99)),
		);

		if ($this->input->get('redundant')) {
			if ($data = $this->validator2->filter($input, $rules, $errors, true)) {
				Debug::dump($data);
			} else {
				Debug::dump($errors);
			}
		}

		//$this->view->setContent('form', $this->load('Formelements', $input, $errors));


		$input2 = $this->validator2->filter($input, $rules);
		Debug::dump($input2);	

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