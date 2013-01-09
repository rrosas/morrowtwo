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


namespace Morrow\Core\Helpers;

class HtmlOptions{

	static public function getOutput($name, $keys, $values, $selected = array(), $class='',$style = array(), $classes = array(), $extras = array()){

		if(!is_array($selected)) $selected = array_map('strval', array_values((array)$selected));
		
		//dump(array($keys, $values, $selected));

		$_html_result = '';
		foreach ($keys as $i=>$key) {
			// add css styles
			$ostyle = (is_array($style) && isset($style[$i])) ? $style[$i] : '';

			// add generated option to output
			$_html_result .= HelperHtmlOptions::getOption($key, $values[$i], $selected, $ostyle, $class);
		}

		// add extras
		$extra_str = '';
		foreach($extras as $_key => $_value){
			$extra_str .= ' '.$_key.'="'.HelperString::htmlSpecialChars($_value).'"';
		}

		if (!empty($name)) {
			$_html_result = "<select name=\"{$name}\" {$extra_str}>{$_html_result}</select>\n";
		}
		
		return $_html_result;
	}


	static public function getOption($key, $value, $selected, $stylevalue, $classall, $classvalue = ''){
		if(!is_array($value)) {
			$_html_result = '<option class="' .HelperString::htmlSpecialChars($classall).' '.HelperString::htmlSpecialChars($classvalue)
				.'" style="' .HelperString::htmlSpecialChars($stylevalue)
				.'" label="' .HelperString::htmlSpecialChars($value)
				.'" value="' .HelperString::htmlSpecialChars($key) . '"';
			
			if (!is_array($value) && in_array((string)$key, $selected))
				$_html_result .= ' selected="selected"';
				$_html_result .= '>' . HelperString::htmlSpecialChars($value) . '</option>' . chr(10);
			} else {
				$_html_result = HelperHtmlOptions::getOptGroup($key, $value, $selected, $stylevalue, $classall, $classvalue);
			}
		return $_html_result;

	}


	static public function getOptGroup($key, $values, $selected, $stylevalue, $classall, $classvalue){
		$style = '';
		if(!isset($stylevalue)) $style = '';
		else if(!is_array($stylevalue)) $style = $stylevalue;
		else if(isset($stylevalue[$key])) $style = $stylevalue[$key];
		$class = $classvalue;

		$optgroup_str = '<optgroup class="' .HelperString::htmlSpecialChars($classall)
			.'" style="' .HelperString::htmlSpecialChars($style)
			.'" label="' . HelperString::htmlSpecialChars($key) . '">' . chr(10);
		foreach($values as $key=> $value){
			if(isset($stylevalue[$key])) $style = $stylevalue[$key];
			if(isset($classvalue[$key])) $class = $classvalue[$key];
			$optgroup_str .= HelperHtmlOptions::getOption($key, $value,$selected, $style, $classall, $class); 

		}
		$optgroup_str .= "</optgroup>" . chr(10);
		return $optgroup_str;
	}

}
