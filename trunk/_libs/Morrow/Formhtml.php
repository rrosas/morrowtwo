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

class FormHtml {
	public static $config = array();
	
	public static function setConfig($formname, $config) {
		self::$config[$formname] = $config;
	}

	public static function getLabel($formname, $el_name, $params = array()) {
		$special_params = array('errorclass','errorstyle', 'hide_required', 'dtype', 'displaytype');

		$display_type = 'text';
		if (isset($params['dtype'])) $display_type = $params['dtype'];
		elseif (isset($params['displaytype'])) $display_type = $params['displaytype'];

		$_form = Factory::load('Form');

		if (!$_form->getElement($formname, $el_name)) {
			throw new \Exception("FormHtml::getLabel : missing definition for '$el_name'");
			return '';
		}

		$_elobj = $_form->getElement($formname, $el_name);

		$el_label = $_elobj->label;
		
		// merge params with global params
		if (isset(self::$config[$formname])) {
			$params = array_merge( self::$config[$formname], $params );
		}
		
		// overwrite value
		if (isset($params['value'])) $el_label = $params['value'];

		// required wrapper
		if ($_elobj->required && (!isset($params['readonly']) || $params['readonly'] != "true")) {
			$el_label = $el_label . ' *';
		}

		// errorclass errorstyle
		if (!empty($_elobj->error) && $_form->isSubmitted()) {
			if (!isset($params['class'])) $params['class'] = '';
			if (!isset($params['style'])) $params['style'] = '';
			if (isset($params['errorclass'])) $params['class'] .= ' '.$params['errorclass'];
			if (isset($params['errorstyle'])) $params['style'] .= ' '.$params['errorstyle'];
		}

		$id = "mw_" . $formname . "_" . $el_name;

		$classname = 'formhtmlelement' . $display_type;
		if (class_exists($classname)) {
			$display_obj = Factory::load('\\' . $classname);
			$content = $display_obj->getLabel($el_label, $id, $params);
		} else $content = "<label for=\"" . $id . "\" " .  Helpers\Htmlformattributes::getAttributeString($params, 'label') . ">$el_label</label>";

		return $content;
	}

	public static function getElement($formname, $el_name, $params = array()) {
		$_form = Factory::load('Form');

		$special_params = array('errorclass','errorstyle', 'value', 'dtype','displaytype', 'opt_classes','separator', 'styles');

		$display_type = null;
		if (isset($params['dtype'])) $display_type = $params['dtype'];
		elseif (isset($params['displaytype'])) $display_type = $params['displaytype'];
		
		if (!$_form->getElement($formname, $el_name)) {
			throw new \Exception("FormHtml::getElement : missing definition for '$el_name'");
			return '';
		}

		$id = "mw_" . $formname . "_" . $el_name;

		$_elobj = $_form->getElement($formname, $el_name);
		
		// merge params with global params
		if (isset(self::$config[$formname])) {
			$params = array_merge( self::$config[$formname], $params );
		}

		// errorclass errorstyle
		if (!empty($_elobj->error) && $_form->isSubmitted($formname)) {
			if (!isset($params['class'])) $params['class'] = '';
			if (!isset($params['style'])) $params['style'] = '';
			if (isset($params['errorclass'])) $params['class'] .= ' '.$params['errorclass'];
			if (isset($params['errorstyle'])) $params['style'] .= ' '.$params['errorstyle'];
		}

		// set the value
		$el_value = "";
		if ($_elobj->value !== null) $el_value = $_elobj->value;
		// if the value hasn't been set in the definition and not by the user, then check whether the value was passed to the method
		if (isset($params['value']) && !$_form->isSubmitted($formname) && empty($el_value)) $el_value = $params['value'];
		if (!empty($value) && !$_form->isSubmitted($formname) && empty($el_value)) $el_value = $value;

		// get modifiers
		$modi_sets = array();

		if ($_elobj->example != "") {
			$example = $_elobj->example;
			if ($el_value === '') {
				$el_value = $example;
				$modi_sets[] = "onclick=\"if(this.value=='$example')this.value=''\"";
			}
		}

		$form_el_name = $formname . "[" . $el_name . "]";

		// filter
		$outfilter = null;
		$infilter = null;
		if (method_exists('\Morrow\Formfilter', 'out' . $display_type)) {
			$outfilter = 'out' . $display_type;
		}

		if (method_exists('\Morrow\Formfilter', 'in' . $display_type)) {
			$infilter = 'in' . $display_type;
		}

		if ($infilter != null && !empty($el_value)) $el_value = formfilter::$infilter($el_value);

		$content = '';
		if ($outfilter != null) {
			$content = '<input type="hidden" name="' . $form_el_name . '[__filter]" value="' . $display_type . '" />';
		}

		$multiple = false;

		$default_dtype = 'text';

		$options = array();
		if ($_elobj->type == "set") {
			$options = array();
			if (count($_elobj->options)>0) $options = $_elobj->options;
			$multiple =  $_elobj->multiple;
			$default_dtype = 'select';

			$groups = array(
				"check",
				"checkbox",
				"radio",
				"radiogroup",
				"checkgroup",
				"checkboxgroup"
			);

			if (in_array($display_type, $groups)) $display_type = 'group';
		}

		$display_obj = null;
		$classname = 'formhtmlelement' . $display_type;
		if (!is_null($display_type) && class_exists($classname)) {
			$display_obj = Factory::load('\\' . $classname);
		} else {
			$display_obj = Factory::load('Formhtmlelement' . $default_dtype);
		}
		
		if (isset($params['readonly']) && $params['readonly']===true) {
			$content .= $display_obj->getReadonly($form_el_name, $el_value, $id, $params, $options, $multiple);
		} else { 
			$content .= $display_obj->getDisplay($form_el_name, $el_value, $id, $params, $options, $multiple);
		}
		return $content;
	}

	public static function getError($formname, $el_name, $params = array()) {
		// params: name, opt: tag, class
		$special_params = array('tag');

		// $_form = $smarty-> _framework -> formhandler;
		$_form = Factory::load('Form');

		if (!$_form->getElement($formname, $el_name)) {
			throw new \Exception("FormHtml::getError : missing definition for '$el_name'");
			return '';
		}

		// merge params with global params
		if (isset(self::$config[$formname])) {
			$params = array_merge( self::$config[$formname], $params );
		}
		
		$_elobj = $_form->getElement($formname, $el_name);

		$tagname = "span";
		if (isset($params['tag'])) $tagname = $params['tag'];
		if (!isset($params['class'])) $params['class'] = 'error';

		$content = '';
		if (!empty($_elobj->error) && $_form->isSubmitted()) {
			$content = $_elobj->error;
			
			if (isset($params['value'])) {
				$content = $params['value'];
			}
			if (!empty($value)) {
				$content = $value;
			}

			$display_type = "text";
			if (isset($params['dtype'])) $display_type = $params['dtype'];
			elseif (isset($params['displaytype'])) $display_type = $params['displaytype'];
			$classname = 'formhtmlelement' . $display_type;
			if (class_exists($classname)) {
				$display_obj = Factory::load('\\' . $classname);
				$content = $display_obj->getError($content, $params, $tagname);
			} else $content = "<$tagname " . Helpers\Htmlformattributes::getAttributeString($params, $tagname) .">$content</$tagname>";
		}

		return $content;
	}

	public static function getInputImage($el_name) {
		$tf_key = 'TMP_FILES.' . $el_name . '.tmp_name';
		$session = Factory::load('Session');
		return $session->get($tf_key);
	}
}
