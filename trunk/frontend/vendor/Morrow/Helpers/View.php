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

use Morrow\Factory;

class View {
	public static $cycles = array();
	
	public static function cycle() {
		$values = func_get_args();
		$name = array_shift($values);
		
		if (!isset(self::$cycles[$name])) self::$cycles[$name] = -1;
		$index =& self::$cycles[$name];
		if (!isset($values[++$index])) $index = 0;
		return $values[ $index ];
	}
		
	public static function strip($buffer) {
		$pat = array("=^\s+=", "=\s{2,}=", "=\s+\$=", "=>\s*<([a-z])=");
		$rep = array("", " ", "", "><$1");
		$buffer = preg_replace($pat, $rep, $buffer);
		return $buffer;
	}
		
	public static function mailto($address, $text = '', $html = null) {
		if (empty($text)) $text = $address;
		$address = str_replace('@', '--', $address);
		$id = uniqid('scrambled_');

		$link = '<a href="mailto:'.$address.'" '.$html.' rel="nofollow">'.$text.'</a>';
		$link = strrev($link);
		$returner = '<span id="'.$id.'">'.htmlspecialchars($link).'</span>';
		$returner .= '<script>';
		$returner .= 'var el_'.$id.' = document.getElementById("'.$id.'");';
		$returner .= 'var content_'.$id.' = el_'.$id.'.textContent ? el_'.$id.'.textContent : el_'.$id.'.innerText;'; // innerText = IE
		$returner .= 'el_'.$id.'.innerHTML = content_'.$id.'.split("").reverse().join("").replace(/--/g, "@");';
		$returner .= '</script>';
		return $returner;
	}

	public static function hidelink($url, $text = '', $html = '') {
		if (empty($text)) $text = $url;
		$id = uniqid('scrambled_');
		$link = '<a href="'.$url.'" '.$html.' rel="nofollow">'.htmlspecialchars($text).'</a>';
		$link = strrev($link);
		$returner = '<span id="'.$id.'">'.htmlspecialchars($link).'</span>';
		$returner .= '<script>';
		$returner .= 'var el_'.$id.' = document.getElementById("'.$id.'");';
		$returner .= 'var content_'.$id.' = el_'.$id.'.textContent ? el_'.$id.'.textContent : el_'.$id.'.innerText;'; // innerText = IE
		$returner .= 'el_'.$id.'.innerHTML = content_'.$id.'.split("").reverse().join("");';
		$returner .= '</script>';
		return $returner;
	}

	public static function loremipsum($word_count = 200, $random = true) {
		$text = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum.';
		$count = str_word_count($text);

		$multiplier = ceil($word_count/$count);
		$text = str_repeat($text, $multiplier);
		
		$text = explode(' ', $text);
		if ($random) shuffle($text);
		$returner = array_slice($text, 0, $word_count);
		
		$returner = ucfirst(implode(' ', $returner)).'.';
		return $returner;
	}

	public static function thumb($filepath, $params = array()) {
		try {
			$path = Factory::load('Image')->get($filepath, $params);
			$path = str_replace(PUBLIC_PATH, '', $path);
		} catch (\Exception $e) {
			if (isset($params['fallback'])) {
				$path = Factory::load('Image')->get($params['fallback'], $params);
				$path = str_replace(PUBLIC_PATH, '', $path);
			} else {
				if (!isset($params['width'])) $params['width'] = 100;
				if (!isset($params['height'])) $params['height'] = $params['width'];

				$text = 'Bild fehlt.';
				$path = 'http://dummyimage.com/'.urlencode($params['width']).'x'.urlencode($params['height']).'/&text='.rawurlencode($text);
			}
		}
		return $path;
	}
	
	public static function image($filepath, $calls = array()) {
		$id         = md5(serialize(array($filepath, $calls)));
		$cache      = new Cache('temp/image/');
		$comparator = filemtime($filepath);
		$path       = 'temp/image/' . $id . '.png';
		
		if (!$cache->load($id, $comparator)) {
			$image = new ImageObject($filepath);
			
			foreach ($calls as $method => $parameters) {
				call_user_func_array(array($image, $method), $parameters);
			}
			
			$image = $image->get('png', true, 9, null);
			
			$cache->save($id, $path, '+1 year', $comparator);
			file_put_contents($path, $image);
		}
		
		return str_replace(PUBLIC_PATH, '', $path);
	}
}

