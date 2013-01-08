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


namespace Morrow\Libraries;

class FormElement{
	public $type = "simple";
	protected $_fh;

	public $required;
	public $checktype;
	public $comparefield = null;
	public $arguments = null;

	#aus lang
	public $default;
	public $example;
	public $label; 
	public $name;

	#aus user input 
	public $value = null;

	#aus formhandler / Lang
	public $error = null;

	public function __construct($formhandler, $name, $required=false, $checktype=null) {
		$this->_fh = $formhandler;
		$this->required = $required;
		$this->checktype = $checktype;
		$this->name = $name;
	}

	public function getType(){
		return $this->type;
	}

	public function setDefault($value){
		if(!isset($value)) $value = '';
		$this->default = $value;
		if ($this->value === null) {
			$this->value = $this->default;
		}
	}

	public function getDefault(){
		return $this->default;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getName(){
		return $this->name;
	}

	public function setLabel($label){
		$this->label = $label;
	}

	public function getLabel(){
		return $this->label;
	}

	public function setRequired($required=true){
		$this->required = $required;
	}

	public function isRequired(){
		return $this->required;
	}


	public function setValue($value, $overwrite = false){
		$session = \Morrow\Factory::load("Morrow\Libraries\session");
		$tf_key = 'TMP_FILES.' . $this->name;
		if(is_array($value)){
			#special: file
			if(isset($value['tmp_name'])){
				if(empty($value['tmp_name'])){
					if($session->get($tf_key)!=''){
						if(!empty($value['name']) && $value['error'] > 0){
							$session->delete($tf_key);
						}
						else{
							$value = $session->get($tf_key);
							#put file back to tmp
							file_put_contents($value['tmp_name'],$value['src']);
							unset($value['src']);
						}
					}
					else {
						$value = '';
					}
				}
				else{
					#save file to session
					if(is_file($value['tmp_name'])){	
						$session->set($tf_key, $value);
						$session->set($tf_key . ".src", file_get_contents($value['tmp_name']));
					}
				}
			}
			else{
				#check out-filter
				if(isset($value['__filter'])){
					$filtermethod = 'out' . $value['__filter'];
					unset($value['__filter']);
					$empty = true;
					foreach($value as $v){
						if(!empty($v)) $empty = false;
					}
					if($empty) $value = '';
					else if(class_exists('formfilter') && method_exists('formfilter',$filtermethod)){
						$value = formfilter::$filtermethod($value);
					}
				}
			}
		}

		#form called without submitting, remove any file-data stored in the session
		if(!$this->_fh->isSubmitted() && $session->get($tf_key)!='') {
			$session->delete($tf_key);
		}

		if(empty($value) && !$overwrite){
			$value = $this->value;
		}

		if(!empty($this->example) && $value === $this->example) $value = "";
		$this->value = $value;
		#the value should no longer be null
		if($this->value === null) $this->value = '';
	}

	public function getValue(){
		if ($this->value === null) return '';

		return $this->value;
	}

	public function setExample($value){
		$this->example = $value;
	}

	public function getExample(){
		return $this->example;
	}

	public function setError($value){
		$this->error = $value;
	}

	public function validate($formname, $validator_class = 'validator'){
		if($this->value === null){
			throw new \Exception("You must set all element values before validating (" . $this->name . ")");
		}

		$compare = null;
		if(isset($this->comparefield) && isset($this->_fh->elements[$formname][$this->comparefield])){
			$compare = $this->_fh->elements[$formname][$this->comparefield]->getValue();
		}

		if($this->required && $this->value === ''){
			$this->setError(\Morrow\Factory::load('Morrow\Libraries\language')->_("This field is required."));
			return false;
		}
		#else if($this->value !== '' && $this->checktype != null){
		else if($this->checktype != null && ($this->value !== '' || $this->comparefield != null)){
			$validator = \Morrow\Factory::load('Morrow\Libraries\\' . $validator_class);
			$function = "check" . $this->checktype;
			if(!method_exists($validator,$function)){
				throw new \Exception("Method $function does not exist in class " . get_class($validator) . "!");
			}
			else{
				if(!$validator->$function($this->value, $errorkey, $compare, $this->arguments, $this->_fh->_locale)){
					$this->setError($errorkey);
					#if it was a file, remove it from session
					$session = \Morrow\Factory::load("Morrow\Libraries\session");
					$tf_key = 'TMP_FILES.' . $this->name;
					if($session->get($tf_key)!='') {
						$session->delete($tf_key);
					}
					return false;
				}
			}
		}
		return true;
	}

}
