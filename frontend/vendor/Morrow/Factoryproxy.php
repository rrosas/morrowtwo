<?php

/*////////////////////////////////////////////////////////////////////////////////
	MorrowTwo - a PHP-Framework for efficient Web-Development
	Copyright (C) 2009  Christoph Erdmann, R.David Cummins

	This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>

	MorrowTwo is free software:  you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////*/


namespace Morrow;

/**
 * Allows to create a lightweight proxy object which is useful for Dependency Injection.
 * Use this class if you want to prepare a class A which depends on another class B, but you are not sure if you will use the class A.
 * The generated proxy object is really lightweight and this way you can save memory and initialization time.
 *
 * Example
 * ------------
 * We have a class A which depends on Class B
 *
 * ~~~{.php}
 * class Class A {
 *     public function __construct($class_b) {
 * 	       $class_b->get();
 *     }	
 * }
 * ~~~
 * 
 * ### The problem
 * We have to initialize Class B because we need it in the constructor of Class A.
 * Class A is prepared because we don't know if we will use it.
 * 
 * ~~~{.php}
 * $class_b = Factory::load('Class_B');
 * Factory::prepare('Class_A', $class_b);
 * ~~~
 *
 * ### The solution
 * We create the proxy object of Class B which is a very lightweight object.
 * In Class A we can use Class B as if we have really passed it.
 * 
 * ~~~{.php}
 * $class_b = new Factoryproxy('Class_B');
 * Factory::prepare('Class_A', $class_b);
 * ~~~
 */
class Factoryproxy {
	/**
	 * Holds the parameters which are later passed to the Factory.
	 * @var array $_parameters
	 */
	protected $_parameters;

	/**
	 * Pass the same parameters as you would do for Factory::load().
	 * @param mixed $instance_identifier_and_parameters All the parameters which are later passed to the Factory.
	 */
	public function __construct($instance_identifier_and_parameters) {
		$this->_parameters = func_get_args();
	}

	/**
	 * Returns the parameters which are later passed to the Factory (Used by the Factory).
	 * @param mixed $instance_identifier_and_parameters All the parameters which are later passed to the Factory.
	 */
	public function get($instance_identifier_and_parameters) {
		return $this->_parameters;
	}
}
