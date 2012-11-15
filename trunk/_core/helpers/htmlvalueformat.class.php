<?php
/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>
    This file was contributed by Stefan Söhle.

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


namespace Morrow\Helpers;
	
/*
e.g.:
~~ htmlvalueformat::format($value, $currency, $numberFormat) ~

------ $currency -------------------------------------
	array(
      'format'=>'[minus][plus][zero][unit]&nbsp;[value]',   // OBBLIGATORY. Makes the used items 
                                                            // also obligatory, when no default.
      'unit'=>'&pound;',                  // OPTIONAL
      'thou' => ',',                      // OPTIONAL default: ''
      'separator' => '.',                 // OPTIONAL default: '.'
      'decimal'=>2,                       // OPTIONAL default: '2'
      'minus'=>'-',                       // OPTIONAL default: '-#
      'plus'=>'&nbsp;',                   // OPTIONAL default: '+'
      'zero'=>'&nbsp;',                   // OPTIONAL default: ''
      )


------ $numberFormat -----------------------------------
   tag attribute depending to the value, e.g:

   array(
       'TAG' => 'div',                     // OPTIONAL (default: span)
       'ALL' => 'class="null"',            // for all values which where not selected
       '==0' => 'class="null"',            // $value==0
       '<0'  => 'id="minus"',              // $value>0
       '>10' => 'style="color:blue;"',     // $value>10
      )

*/

class HtmlValueFormat {
	protected static function defaults($what) {
		$defaults['format']['thou'] = '';
		$defaults['format']['separator'] = '.';
		$defaults['format']['decimal'] = '2';
		$defaults['format']['minus'] = '-';
		$defaults['format']['plus'] = '+';
		$defaults['format']['zero'] = '*';
		$defaults['attr']['tag'] = 'span';
		return $defaults[$what];
	}
	
	public static function format($VALUE, $currency, $numberFormat=false) {
		// format -------------------------------------------------
		$currency = self::loadDefaults('format' , $currency);
		if($VALUE < '0') {
			$currency['plus'] = '';
			$currency['zero'] = '';
		}
		elseif($VALUE > '0') {
			$currency['minus'] = '';
			$currency['zero'] = '';
		}
		else { 
 			$currency['plus'] = '';
 			$currency['minus'] = '';
		}
		$fvalue = number_format(abs($VALUE) , $currency['decimal'] , $currency['separator'] , $currency['thou']);
		$currency['value'] = $fvalue;
		$fvalue = $currency['format'];
 		foreach($currency as $index=>$trash) {
 			$muster = "/\[$index\]/";
 			$fvalue = preg_replace($muster , $currency[$index] , $fvalue);
		}
		// clean up all not used parser
		$fvalue = preg_replace("/\[(.*?)\]/" , "", $fvalue);
		
		// html attributes -----------------------------------------
		if($numberFormat!=false) {
			$numberFormat = self::loadDefaults('attr' , $numberFormat);
				// tag
			$_tag = $numberFormat['tag'];
			unset($numberFormat['tag']);
			$_attr = NULL;
				// isset ALL
			if(isset($numberFormat['all'])) {
				$_attr = $numberFormat['all'];
				unset($numberFormat['all']);
			}
				// selected
			foreach($numberFormat as $lim=>$attr) {
				eval ("if($VALUE$lim) \$_attr = '$attr';");
			}
			$fvalue = "<$_tag $_attr>$fvalue</$_tag>";
		}
		return $fvalue;
	}
		
	protected static function loadDefaults($what, $numberFormat) {
		$defaults = self::defaults($what);
 		foreach($defaults as $index=>$default) {
 			if(!isset($numberFormat[$index])) {
 				$numberFormat[$index] = $default;
 				}
 			}
 		return $numberFormat;
 	}
}
