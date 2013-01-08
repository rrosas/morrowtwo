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


namespace Morrow\Helpers;

class HtmlDate {
	static	$defaults = array();

	static public function getOutput($el_key, $date_str=null, $date_format="%d%m%Y", $start_year=null, $end_year=null, $params=array()){
 		if (!function_exists('getOperator'))
			function getOperator($string, &$operator){
				$number = substr($string, 1);
				if($string{0} == '+'){
					$operator = $number;
					return true;
				}
				if($string{0} == '-'){
					$operator = $number*(-1);
					return true;
				}
				return false;
			}

       self::$defaults = array(
			'_Year' => date('Y'),
			'_Month' => date('m'),
			'_Day' => date('d'),
			'_Hour' => '00',
			'_Min' =>  '00',
			'_Sec' =>  '00',
		);

	
		if($start_year == null)  $start_year=date("Y")-1;
		if($end_year == null)  $end_year=date("Y")+5;

		if(getOperator($start_year, $operator )){
			$start_year=date("Y")+$operator;
		}
		if(getOperator($end_year, $operator )){
			$end_year=date("Y")+$operator;
		}

		if($date_str === null) $date_str = "today";
		else if($date_str == ''){
			$date_str = "0000-00-00 00:00:00";
		}

		if(!preg_match("/[0-9]{0,4}-[0-9]{0,2}-[0-9]{0,2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $date_str)) {
			$date_str = date('Y-m-d H:i:s', strtotime($date_str));
		}

		preg_match("/([0-9]{0,4})-([0-9]{0,2})-([0-9]{0,2}) ([0-9]{0,2}):([0-9]{0,2}):([0-9]{0,2})/", $date_str, $matches);
		self::$defaults['_Year'] = $matches[1];
		self::$defaults['_Month'] = $matches[2];
		self::$defaults['_Day'] = $matches[3];
		self::$defaults['_Hour'] = $matches[4];
		self::$defaults['_Min'] = $matches[5];
		self::$defaults['_Sec'] = $matches[6];

		#groups
		$sep_match = "([^%]*)";
                preg_match_all("/(%[a-zA-Z]{1})?(" . $sep_match . "?)/", $date_format, $matches);
		$options = $matches[1];
		$separators = $matches[2];

                $_html_result = '';
		foreach($options as $k=>$op){
			$_html_result .= self::getOptions($el_key, $op, $start_year, $end_year, $params);
			$_html_result .= $separators[$k];
		}
		foreach(self::$defaults as $ln=>$left){

			$_html_result .= '<input type="hidden" name="'. $el_key . '[' .$ln.']" value="'.$left.'" />';
		}

		return $_html_result;
	}

	protected static function getOptions($el_key, $format, $start_year, $end_year, $params = array()){
		$class = ''; 
		$style = array();
		$classes = array(); 
		$extras = array();

		foreach($params as $key=>$value){
			if($key == "class") $class = $value;
			if($key == "style") $style = $value;
			if($key == "classes") $classes = $value;
			else $extras[$key] = $value;
		}

		$day_codes = array("%d", "%e");
		$month_codes = array("%b", "%B","%h", "%m");
		$year_codes = array("%y", "%Y");

		$days = range(0,31);
		$months = range(0,12);
		$years = range($start_year-1,$end_year);

		$hours = range(-1,23);
		$min = range(-1,59);
		$sec = range(-1,59);

		switch ($format){
			case '%p': return '';break;
			case '%I': $format = '%H';
			case '%H': 
				$name = "[_Hour]";
				$sel = self::$defaults['_Hour'];
                                $options = $hours;
                                $stt = date('Y') . "-01-01 %s:00:00";
                                unset(self::$defaults['_Hour']);
                                break;
			case '%M': 
				$name = "[_Min]";
				$sel = self::$defaults['_Min'];
                                $options = $min;
                                $stt = date('Y') . "-01-01 00:%s:00";
                                unset(self::$defaults['_Min']);
                                break;
			case '%S': 
				$name = "[_Sec]";
				$sel = self::$defaults['_Sec'];
                                $options = $min;
                                $stt = date('Y') . "-01-01 00:00:%s";
                                unset(self::$defaults['_Sec']);
                                break;
			case "%d":
			case "%e":
				$name = "[_Day]";
				$sel= self::$defaults['_Day'];
				$options = $days;
				$stt = date('Y') . "-01-%s";
				unset(self::$defaults['_Day']);
				break;
			case "%b":
			case "%B":
			case "%h":
			case "%m":
				$name = "[_Month]";
				$sel= self::$defaults['_Month'];
				$options = $months;
				$stt = date('Y') . "-%s-01";
				unset(self::$defaults['_Month']);
				break;
			case "%y":
			case "%Y":
				$name = "[_Year]";
				$sel= self::$defaults['_Year'];
				$options = $years;
				$stt = "%s-01-01s";
				unset(self::$defaults['_Year']);
				break;
			default:
				return $format;
		}
			
		$selected = array();
		$output = array();
		foreach($options as $k=>$opt){
			if($k==0) {
				$options[$k] = '';
				$output[$k] = '';
			}
			else{
				$options[$k] = sprintf("%02s",$opt);
				$output[$k] = strftime($format , strtotime(sprintf($stt, $opt)));
				if($opt == $sel) $selected[] = $opt;
			}
		}
		return HelperHtmlOptions::getOutput($el_key . $name, $options, $output, $selected, $class, $style, $classes, $extras);
	}
}

function getOperator($string, &$operator) {
	$number = substr($string, 1);
	if($string{0} == '+'){
		$operator = $number;
		return true;
	}
	if($string{0} == '-'){
		$operator = $number*(-1);
		return true;
	}
	return false;
}
