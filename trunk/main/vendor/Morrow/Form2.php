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
 * The Validator class provides several rules for validating data.
 * 
 * If you write a validator keep in mind that the validator should never throw an exception if `$input` is a scalar or an array.
 * Because if you validate data coming from a web client, data can only be a string or an array.
 */
class Form2 {
	protected $_input;
	protected $_errors;
	public static $error_class = 'error';

	public function __construct($input, $errors) {
		$this->_input	= $input;
		$this->_errors	= $errors;
	}

	public function label($id, $value, $attributes = array()) {
		if (isset($this->_errors[$id])) {
			if (isset($attributes['class'])) {
				$attributes['class'] .= ' ' . self::$error_class;
			} else {
				$attributes['class'] = self::$error_class;
			}
		}

		return '<label for="' . $id . '" ' . $this->_getAttributesString($attributes) . '>' . $value . '</label>';
	}

	public function hidden($id, $value, $attributes = array()) {
		return '<input type="hidden" id="' . $id . '" name="' . $id . '" value="' . htmlspecialchars($values, ENT_QUOTES, 'utf-8') . '" ' . $this->_getAttributeString($params, 'input') . '" />"';
	}

	protected function _getAttributesString($attributes) {
		$returner = '';
		foreach ($attributes as $key => $value) {
			$returner .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'utf-8') . '"';
		}
		return $returner;
	}
}
