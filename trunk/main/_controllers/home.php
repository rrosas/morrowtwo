<?php

namespace Morrow;

//use Exception;
//use Zend\Text\Figlet\Figlet;

class PageController extends DefaultController {
	public function run() {
		/*
		dump( Helpers\General::cleanPath( FW_PATH ) );
		dump( Time::create()->get('datetime') );
		$this->benchmark->start('fd');
		dump(Factory::load('Benchmark')->get());
		*/
		
		// Extending morrow with external classes and without name conflicts
		//dump(Factory::load('Test')->get());
		// is the same as
		//dump(Factory::load('\Morrow\Test')->get());
		
		/*
		$this->load('Test:test1', 'bar');
		$this->load('Test:test2', 'bar2');
		dump($this->test1->get());
		dump($this->test2->get());
		*/
		
		// load a model ******************************************************************************
		//$test = Factory::load('Models\Test');
		
		/*
		// http://framework.zend.com/manual/2.0/en/modules/zend.text.figlet.html
		$figlet = new \Zend\Text\Figlet\Figlet();
		//$figlet = new Figlet();
		$text = $figlet->render('Morrow');
		$this->view->setHandler('plain');
		$this->view->setContent($text);
		*/
		
		// get all constants defined via Morrow
		/*
		$constants = current(array_intersect_key(get_defined_constants(true), array('user' => '')));
		dump($constants);
		
		// classes loaded for this request
		$classes = array_filter(get_declared_classes(), function($class) { return strpos($class, 'Morrow\\') === 0; });
		dump($classes);
		
		// get all functions defined via Morrow
		$functions = current(array_intersect_key(get_defined_functions(), array('user' => '')));
		dump($functions);
		*/
		
		// Zend Mail Test
		/*
		$transport = new \Zend\Mail\Transport\Smtp();
		$options   = new \Zend\Mail\Transport\SmtpOptions(array(
			'host'              => 'smtp.googlemail.com',
			'port'              => '465',
			'connection_class'  => 'login',
			'connection_config' => array(
				'username' => 'ministry.robot@googlemail.com',
				'password' => 'fyOGMafo',
				'ssl'      => 'ssl',
			),
		));
		$transport->setOptions($options);
		
		$message = new \Zend\Mail\Message();
		$message	-> setEncoding("UTF-8")
					-> addFrom('ministry.robot@googlemail.com', 'Ministry Robot')
					-> setSender('ministry.robot@googlemail.com', 'Ministry Robot')
					-> addTo('christoph.erdmann@ministry.de', 'Christoph')
					-> setSubject('TestBetreff')
					-> setBody('Das ist der Text des Mails.')
		;
		
		$transport->send($message);
		*/
		
		/*
		try {
			echo \DateTime::createFromFormat('2012-08-01');
		} catch (\Exception $e) {
			echo 'Wurst!';
		}
		*/
	}
}