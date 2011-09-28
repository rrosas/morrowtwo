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

/**
 * 2010-11-03 Dirk Lüth
 * Added passing of parameter "arguments" to _parseDef (also see formelement.class.php)
 * Example usage (in form defintion):
 * $fields['birthday'] = array('required' => true, 'checktype' => 'MinAge', 'arguments' => 18);
 */


class Form{
	public $elements = array();	
	public $_rawinput = array();

	private $has_errors = false;

	public $submitted = false;
	public $submittedForm = null;
	public $_lcontent;
	public $_locale;

	private $_validator = 'validator';

	public function __construct($settings = null){ #$element_def, $content, $locale) {
		if($settings == null) $settings = $this->morrow_construct_vars();
		
		$this->_lcontent = $settings['content'];
		$this->_locale = $settings['locale'];
		$this->_parseDef($settings['elements']);
	}

	/* for use only in MorrowTwo context! */
	private function morrow_construct_vars(){
		$page = Factory::load('page');
		$alias = $page->get('alias');
			
		#elements definition
		$elements = array();
		$g_deffile = PROJECT_PATH . "_forms/_global.php";
		$deffile = PROJECT_PATH . "_forms/" . $alias  . ".php";
		if(is_file($g_deffile)) include($g_deffile);
		if(is_file($deffile)) include($deffile);
		$settings['elements'] = $elements;
		
		#language content / locale
		$language = Factory::load('language');
		$settings['content'] = $language->getFormContent($alias);
		$settings['locale'] = $language->getLocale();
		
		return $settings;
	}


	public function isSubmitted($form = null){
		if($form != null) {
			if(isset($this->submittedForms[$form])) return true;
			return false;
		}
		return $this->submitted;
	}

	public function submittedForm(){
		if($this->submitted) return $this->submittedForm;
		return null;
	}

	private function _parseDef($formdef){
		foreach($formdef as $formkey=>$element_def){
			foreach($element_def as $key=>$el){
				$required = isset($el['required'])?$el['required']:false;
				$checktype = isset($el['checktype'])?$el['checktype']:null;
				$comparefield = isset($el['compare'])?$el['compare']:null;
				$arguments = isset($el['arguments'])?$el['arguments']:null;
				if(!isset($el['type'])) $el['type'] = "simple";
				switch ($el['type']){
					case 'set':
						$new_element = new FormElementSet($this,$key,$required, $checktype);
						#output (from lcontent)
						$output = isset($this->_lcontent[$formkey][$key]['output'])?$this->_lcontent[$formkey][$key]['output']:array();
						$new_element->setOutput($output);
						#options
						$options = isset($el['options'])?$el['options']:array();
						$new_element->setOptions($options);
						#multiple = true/false
						$multiple = isset($el['multiple'])?$el['multiple']:false;
						$new_element->setMultipleSelect($multiple);
						break;
					default:
						$new_element = new FormElement($this,$key,$required, $checktype);
						if($el['type'] == "checkbox") $new_element->type = "checkbox";
						if(isset($this->_lcontent[$formkey][$key]['example'])){
							$new_element->setExample($this->_lcontent[$formkey][$key]['example']);
						}
						else if(isset($el['example'])){
							$new_element->setExample($el['example']);
						}
				}
				if(isset($this->_lcontent[$formkey][$key]['label'])){
					$new_element->setLabel($this->_lcontent[$formkey][$key]['label']);
				}
				#default value in i18n
				if(isset($this->_lcontent[$formkey][$key]['default'])){
					$new_element->setDefault($this->_lcontent[$formkey][$key]['default']);
				}
				#default value in element definition
				else if(isset($el['default'])){
					$new_element->setDefault($el['default']);
				}
				else $new_element->setDefault(null);
				$new_element->comparefield = $comparefield;
				$new_element->arguments = $arguments;	
				$this->elements[$formkey][$key] = $new_element;
				#new def has been loaded after input
				if(isset($this->_rawinput[$formkey][$key])) $this->elements[$formkey][$key]->setValue($this->_rawinput[$formkey][$key],true);
			}
		}
	}

	

	public function hasErrors(){
		return $this->has_errors;
	}

	public function getErrors($formname = null){
		$errors = array();
		if($formname == null) $formname = $this->submittedForm;
		if($formname !== null){
			foreach($this->elements[$formname] as $key=>$eldef){
				if($eldef->error != null) $errors[$key] = $eldef->error;
			}
		}
		return $errors;
	}

	public function setError($formname, $fieldname, $errkey){
		$eldef = $this->elements[$formname][$fieldname];
		if(!isset($eldef)) return false;
		$eldef->setError($errkey); 
		$this->has_errors = true;
		return true;
	}

	public function getValues($formname = null){
		$values = array();
		if($formname == null) $formname = $this->submittedForm;
		if($formname !== null){
			foreach($this->elements[$formname] as $key=>$eldef){
				$values[$key] = $eldef->value;
			}
		}
		return $values;
	}

	public function getElement($formname, $fieldname){
		if(!$this->_checkElements($formname, $fieldname)) return false;
		return $this->elements[$formname][$fieldname];
	}

	public function removeElement($formname, $fieldname){
		if(!$this->_checkElements($formname, $fieldname)) return false;
		unset($this->elements[$formname][$fieldname]);
		return true;
	}

	public function loadDef($element_def){
		$this->_parseDef($element_def);
	}


	public function setValues($formname, $values, $overwriteall = false){
		if(!$this->_checkElements($formname)) return false;
		foreach($this->elements[$formname] as $key=>$elobj){
						#special treatment for checkboxes ... grrr
			#if(isset($values[$key]) || ($elobj->type == "checkbox" && $this->submitted)) $overwrite = true;
			$overwrite = $overwriteall;
			if(isset($values[$key])) $overwrite = true;
			else $values[$key] = '';
			$elobj->setValue($values[$key], $overwrite);	
		}
	}

	public function clearValues($formname = null){
               if($formname == null) $formname = $this->submittedForm;
               if($formname !== null){
                       foreach($this->elements[$formname] as $key=>$eldef){
                               $eldef->setValue(null, true);
                       }
                       return true;
               }
               return false;
       }

       public function resetValues($formname = null){
               if($formname == null) $formname = $this->submittedForm;
               if($formname !== null){
                       foreach($this->elements[$formname] as $key=>$eldef){
                               $eldef->setValue('', true);
                               $eldef->setValue($eldef->getDefault());
                       }
                       return true;
               }
               return false;
       }



	#only for setting user input (from _POST/_GET)
	public function setInput($input){
		$this->_rawinput = $input;
		$this->submitted = false;
		$this->submittedForms = array();	
		$this->submittedForm = null;
		#check submitted an which form
		foreach($this->elements as $formkey=>$def){
			$overwrite = false;
			if(isset($input[$formkey])) {
				$this->submitted = true;
				$this->submittedForms[$formkey] = true;	
				$this->submittedForm = $formkey;	
				$overwrite = true;
			}
			else $input[$formkey] = array();
			$this->setValues($formkey,$input[$formkey],$overwrite);
		}
		return;

	}

	public function setValidator($validator){
		if(class_exists($validator)){
			$this->_validator = $validator;
			return true;
		}
		else {
			trigger_error("Class '" . $validator . "' does not exist.",E_USER_ERROR);
			return false;
		}
	}

	public function validate($formname=null,$limit = null){
		if($formname == null) $formname = $this->submittedForm;
		if(!$this->_checkElements($formname)) return false;

		foreach($this->elements[$formname] as $key=>$element){	
			if(is_array($limit) && !in_array($key, $limit)) continue;
			if(!$element->validate($formname, $this->_validator)) $this->has_errors = true;
		}
		return !$this->has_errors;
		
	}

	public function fillSet($formname, $fieldname, $sets, $replaceall=false, $default = null){ #classes??
		if(!$this->_checkElements($formname, $fieldname)) return false;

		if($this->elements[$formname][$fieldname]->type != "set"){
			trigger_error("Element '" . $fieldname . "' ist not type 'set'.",E_USER_ERROR);
			return false;
		}
		$options = array_keys($sets);
		$output = array_values($sets);
		#dump($fieldname);
		#dump($options);
		$tmpoutput = array();
		$cnt = 0;
		$dim3 = false;
		foreach($output as $k=>$o){
			$tmpoutput[$cnt] = $o;
			if(is_array($o)){
				$dim3 = true;
				foreach($o as $ki=>$i){
					$options[$cnt] = $ki;
					$tmpoutput[$cnt] = $i;
					$cnt++;
				}
			}
			$cnt++;
		}
		if($dim3) $output = $tmpoutput;
		#dump($options);
		#dump($output);
		if($replaceall) {
			$this->elements[$formname][$fieldname]->setOptions($options);
			$this->elements[$formname][$fieldname]->setOutput($output);
			if($dim3) $this->elements[$formname][$fieldname]->setGroups($sets);
		}
		else{
			$this->elements[$formname][$fieldname]->addOptions($options);
			$this->elements[$formname][$fieldname]->addOutput($output);
			if($dim3) $this->elements[$formname][$fieldname]->addGroups($sets);
		}

		if($default !== null) $this->elements[$formname][$fieldname]->setDefault($default);
	}

	public function multiplyField($formname, $fieldname, $key_label){
		$old_el = $this->elements[$formname][$fieldname];
		$els = array();
		foreach($key_label as $key=>$label){
			$new_name = $fieldname . '(' . $key . ')';
			$new_el = clone $old_el;
			$new_el->setLabel($label);
			$new_el->setName($new_name);
			#maybe there was input for this field?
			if(isset($this->_rawinput[$formname][$new_name])){
				$new_el->setValue($this->_rawinput[$formname][$new_name],true);
			}
			$this->elements[$formname][$new_name] = $new_el;
		}
		unset($this->elements[$formname][$fieldname]);
	}

	private function  _checkElements($formname, $fieldname=null){
		if(!isset($this->elements[$formname])){
			trigger_error("Missing Form-Def for '" . $formname . "' !",E_USER_ERROR);
			return false;
		}
		if($fieldname !== null && !isset($this->elements[$formname][$fieldname])){
			trigger_error("Element '" . $fieldname . "' does not exist in Form-Def '" . $formname . "'",E_USER_ERROR);
			return false;
		}
		return true;
	}

	public function exists($formname){
		if(!isset($this->elements[$formname])) return false;
		return true;
	}

}