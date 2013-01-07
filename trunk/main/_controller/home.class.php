<?php

namespace Morrow;

class PageController extends DefaultController {
	public function run() {
		
		/*
		dump( Helpers\General::cleanPath( FW_PATH ) );
		dump( Libraries\Time::create()->get('datetime') );
		$this->benchmark->start('fd');
		dump(Factory::load('benchmark')->get());
		*/
		
		// Extending morrow with external classes and without name conflicts
		//dump(Factory::load('test')->get());
		
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
		
		/*
		try {
			echo \DateTime::createFromFormat('2012-08-01');
		} catch (\Exception $e) {
			echo 'Wurst!';
		}
		*/
	}
}
