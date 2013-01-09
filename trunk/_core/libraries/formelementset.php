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


namespace Morrow\Core\Libraries;

class FormElementSet extends FormElement{
	public $type = "set";
	public $options = array();
	public $multiple = false;

	public function setDefault($value){
		if(!isset($value)) {
			$value = '';
			if($this->multiple) $value = array();
		}
		else if($this->multiple && !is_array($value)) $value = array($value);
		$this->default = $value;
		if ($this->value === null || ($this->multiple && count($this->value)==0)) $this->value = $this->default;
		#$this->checktype = null;
	}

	public function getOptions(){
		return $this->options;
	}
	
	public function setOptions($values){
		$this->options = $values;
	}
	
	public function addOptions($values){
		$this->options = array_merge($this->options,$values);
	}

	public function setMultipleSelect($multiple){
		return $this->multiple = $multiple;
	}

	public function isMultipleSelect(){
		return $this->multiple;
	}


	public function setValue($value, $overwrite = false){
		if($this->multiple) {
			$this->example = '';
			if(is_array($value) && count($value)>0){
				$this->value = array();
				foreach($value as $idx=>$item){
					$this->value[] = $item;
				}
	
			}
			else if($overwrite)  $this->value = array();
		}
		else {
			parent::setValue($value, $overwrite);
		}

	}

	protected function _getKeysRecursive($array) {
		$returner = array();
		
		foreach ($array as $key=>$value) {
			if (is_array($value)) $returner = array_merge($returner, $this->_getKeysRecursive($value));
			else $returner[] = $key;
		}
		
		return $returner;
	}
	
	public function validate($formname, $validator_class = 'validator'){
		$options_to_check = $this->_getKeysRecursive($this->options);
		
		// remove values that have been set but are not in options
		if(is_array($this->value)){
			foreach($this->value as $idx=>$item){
				if(!in_array($item, $options_to_check)){
					unset($this->value[$idx]);
				}
			}
		}
		else if(!in_array($this->value, $options_to_check)){
			$this->value = '';
		}
	
		if($this->multiple) {
			$this->comparefield = null;
			if($this->required && (!is_array($this->value) || count($this->value) == 0)){
				$this->setError(Factory::load('Libraries\language')->_('This field is required.'));
				return false;
			}
			return true;


		}
		else{
			return parent::validate($formname, $validator_class);
		}

	}

}
