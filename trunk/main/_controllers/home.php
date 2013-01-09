<?php

namespace Morrow;

//use Morrow\Core\Libraries as Libs;
//use Exception;
//use Morrow\Core\Factory;

class PageController extends DefaultController {
	public function run() {
		
		//dump( Core\Helpers\General::cleanPath( FW_PATH ) );
		//dump( Core\Libraries\Time::create()->get('datetime') );
		//dump( Libs\Time::create()->get('datetime') );
		//$this->benchmark->start('fd');
		//dump(Core\Factory::load('Libraries\benchmark')->get());
		
		// Extending morrow with external classes and without name conflicts
		//dump(Factory::load('Libraries\test')->get());
		// is the same as
		//dump(Core\Factory::load('\Morrow\Core\Libraries\test')->get());
		
		/*
		$this->load('Libraries\test:test1', 'bar');
		$this->load('Libraries\test:test2', 'bar2');
		dump($this->test1->get());
		dump($this->test2->get());
		*/
		
		// load a model
		// $test = Core\Factory::load('\Morrow\Models\Test');
		
		/*
		// get all constants defined via Morrow
		$constants = current(array_intersect_key(get_defined_constants(true), array('user' => '')));
		dump($constants);
		
		// classes loaded for this request
		$classes = array_filter(get_declared_classes(), function($class) { return strpos($class, 'Morrow\\') === 0; });
		dump($classes);
		
		// get all functions defined via Morrow
		$functions = current(array_intersect_key(get_defined_functions(), array('user' => '')));
		dump($functions);
		*/
		
		/*
		try {
			echo \DateTime::createFromFormat('2012-08-01');
		} catch (\Exception $e) {
			echo 'Wurst!';
		}
		*/

		require(PROJECT_PATH . '_user/Zend/Text/Figlet/Figlet.php');
		$figlet = new \Zend\Text\Figlet\Figlet();
		echo $figlet->render('Zend');
	}
}