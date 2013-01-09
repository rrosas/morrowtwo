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

class Image {
	public $cache_dir;
	public $cache_counter = '%m';
	
	public function __construct() {
		// params
		$default_cache_dir = PROJECT_PATH.'temp/thumbs/';
		$counter = strftime( $this->cache_counter );
		
		// delete all folders that are not the current
		$files = scandir( $default_cache_dir );
		foreach ($files as $file) {
			if ($file{0} == '.') continue;
			if (is_dir( $default_cache_dir.$file ) && $file != $counter) {
				helperFile::rmdir_recurse( $default_cache_dir.$file );
			}
		}

		// // set cache dir and create if it not exists
		$this->cache_dir = $default_cache_dir . $counter . '/';
		if (!file_exists( $this->cache_dir )) {
			mkdir( $this->cache_dir );
		}
	}
	
	protected function _unsharpMask($img) { 
		$matrix = array(array( -1, -1, -1 ), array( -1, 16, -1 ), array( -1, -1, -1 ));
		imageconvolution($img, $matrix, 8, 0);
		return $img;
	} 

	protected function _validateParams($params) {
		// all params with type and default_value
		// used for _render
		$defaults['sharpen'] = array('boolean', true);
		$defaults['hint'] = array('boolean', false);
		$defaults['extrapolate'] = array('boolean', true);
		$defaults['dev'] = array('boolean', false);
		$defaults['crop'] = array('boolean', true);
		$defaults['crop_position'] = array('integer', 2);
		$defaults['overlay_position'] = array('integer', 9);

		// used for _render, but unset if not set
		$defaults['frame'] = array('string', null);
		$defaults['overlay'] = array('string', null);
		$defaults['shortside'] = array('integer', null);
		$defaults['longside'] = array('integer', null);
		$defaults['width'] = array('integer', null);
		$defaults['height'] = array('integer', null);

		// validate params
		foreach ($defaults as $key=>$value) {
			if (isset($params[$key])) {
				// check type
				$type = gettype($params[$key]);
				if ($type !== $value[0]) { throw new \Exception(__CLASS__.': "'.$key.'" has to be of type "'.$value[0].'"'); return; }
			} else {
				// set default
				if (!is_null($value[1])) $params[$key] = $value[1];
			}
		}
		
		// set default width
		if (!isset($params['width']) AND
			!isset($params['height']) AND
			!isset($params['longside']) AND
			!isset($params['shortside'])
			)
			$params['width'] = 100;
		
		return $params;
	}
	
	protected function _addMagnifierIcon($img_obj) {
		$img_width  = imagesx($img_obj);
		$img_height = imagesy($img_obj);
		
		// add the white bar
		$trans = imagecolorallocatealpha($img_obj, 255, 255, 255, 25);
		imagefilledrectangle($img_obj, 0, $img_height-9, $img_width, $img_height, $trans);

		// add the magnifier
		$magnifier = imagecreatefromstring(gzuncompress(base64_decode("eJzrDPBz5+WS4mJgYOD19HAJAtLcIMzBBiRXrilXA1IsxU6eIRxAUMOR0gHkcxZ4RBYD1QiBMOOlu3V/gIISJa4RJc5FqYklmfl5CiGZuakMBoZ6hkZ6RgYGJs77ex2BalRBaoLz00rKE4tSGXwTk4vyc1NTMhMV3DKLUsvzi7KLFXwjFEAa2svWnGdgYPTydHEMqZhTOsE++1CAyNHzm2NZjgau+dAmXlAwoatQmOld3t/NPxlLMvY7sovPzXHf7re05BPzjpQTMkZTPjm1HlHkv6clYWK43Zt16rcDjdZ/3j2cd7qD4/HHH3GaprFrw0QZDHicORXl2JsPsveVTDz//L3N+WpxJ5Hff+10Tjdd2/Vi17vea79Om5w9zzyne9GLnWGrN8atby/ayXPOsu2w4quvVtxNCVVz5nAf3nDpZckBCedpqSc28WTOWnT7rZNXZSlPvFybie9EFc6y3bIMCn3JAoJ+kyyfn9qWq+LZ9Las26Jv482cDRE6Ci0B6gVbo2oj9KabzD8vyMK4ZMqMs2kSvW4chz88SXNzmeGjtj1QZK9M3HHL8L7HITX3t19//VVY8CYDg9Kvy2vDXu+6mGGxNOiltMPsjn/t9eJr0ja/FOdi5TyQ9Lz3fOqstOr99/dnro2vZ1jy76D/vYivPsBoYPB09XNZ55TQBAAJjs5s</body>")));
		imagealphablending($img_obj, true);
		imagecopy($img_obj, $magnifier, $img_width-15, $img_height-14, 0, 0, 11, 11);
		imagedestroy($magnifier);
		
		return $img_obj;
	}
	
	protected function _addOverlayImage($img_obj, $overlay_file, $overlay_position) {
		$img_width  = imagesx($img_obj);
		$img_height = imagesy($img_obj);

		$overlay = imagecreatefrompng($overlay_file);
		$overlay_size = getimagesize($overlay_file);

		// copy to right position
		if ($overlay_position == '1') imagecopy($img_obj, $overlay, 0, 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '2') imagecopy($img_obj, $overlay, $img_width/2-$overlay_size[0]/2, 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '3') imagecopy($img_obj, $overlay, $img_width-$overlay_size[0], 0, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '4') imagecopy($img_obj, $overlay, 0, $img_height/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '5') imagecopy($img_obj, $overlay, $img_width/2-$overlay_size[0]/2, $img_height/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '6') imagecopy($img_obj, $overlay, $img_width-$overlay_size[0], $img_height/2-$overlay_size[1]/2, 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '7') imagecopy($img_obj, $overlay, 0, $img_height-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '8') imagecopy($img_obj, $overlay, $img_width/2-$overlay_size[0]/2, $img_height-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		if ($overlay_position == '9') imagecopy($img_obj, $overlay, $img_width-$overlay_size[0], $img_height-$overlay_size[1], 0, 0, $overlay_size[0], $overlay_size[1]); // ecke links oben
		
		return $img_obj;
	}
	
	protected function _addFrame($img_obj, $frame_file) {
		$img_width  = imagesx($img_obj);
		$img_height = imagesy($img_obj);

		$imagesize = getimagesize($frame_file);
		if ($imagesize[0] != $imagesize[1] OR $imagesize[0]%3 OR !file_exists($frame_file)) { throw new \Exception('wrong dimensions of "frame"-image or width and height is not a multiplier of 3'); return; }

		// "frame"-Bild laden und initialisieren
		$frame = imagecreatefrompng($frame_file);
		$frame_blocksize = $imagesize[0]/3;

		// Neues Bild erstellen und bisher erzeugtes Bild hereinkopieren
		$_FRAME['image'] = imagecreatetruecolor($img_width+2*$frame_blocksize, $img_height+2*$frame_blocksize);
		imagecopy($_FRAME['image'], $img_obj, $frame_blocksize, $frame_blocksize, 0, 0, $img_width, $img_height);

		// Jetzt die ganzen anderen Rahmen herum zeichnen
		// die Ecken
		imagecopy($_FRAME['image'], $frame, 0, 0, 0, 0, $frame_blocksize, $frame_blocksize); // ecke links oben
		imagecopy($_FRAME['image'], $frame, $img_width+$frame_blocksize, 0, 2*$frame_blocksize, 0, $frame_blocksize, $frame_blocksize); // ecke rechts oben
		imagecopy($_FRAME['image'], $frame, $img_width+$frame_blocksize, $img_height+$frame_blocksize, 2*$frame_blocksize, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize); // ecke rechts unten
		imagecopy($_FRAME['image'], $frame, 0, $img_height+$frame_blocksize, 0, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize); // ecke links unten
		// jetzt die Seiten
		imagecopyresized($_FRAME['image'], $frame, $frame_blocksize, 0, $frame_blocksize, 0, $img_width, $frame_blocksize, $frame_blocksize, $frame_blocksize); // oben
		imagecopyresized($_FRAME['image'], $frame, $img_width+$frame_blocksize, $frame_blocksize, 2*$frame_blocksize, $frame_blocksize, $frame_blocksize, $img_height, $frame_blocksize, $frame_blocksize); // rechts
		imagecopyresized($_FRAME['image'], $frame, $frame_blocksize, $img_height+$frame_blocksize, $frame_blocksize, 2*$frame_blocksize, $img_width, $frame_blocksize, $frame_blocksize, $frame_blocksize); // unten
		imagecopyresized($_FRAME['image'], $frame, 0, $frame_blocksize, 0, $frame_blocksize, $frame_blocksize, $img_height, $frame_blocksize, $frame_blocksize); // links

		return $_FRAME['image'];
	}
	
	protected function _addRenderTime($img_obj, $start_time) {
		$img_width  = imagesx($img_obj);

		// stop time
		$time['end'] = microtime(true);
		$time = round(($time['end'] - $start_time)*1000);

		// define colors
		$white_trans = imagecolorallocatealpha($img_obj, 255, 255, 255, 25);
		$black = ImageColorAllocate ($img_obj, 0, 0, 0);

		// white bar above
		imagefilledrectangle($img_obj, 0, 0, $img_width, 10, $white_trans);

		// add text
		imagestring($img_obj, 1, 5, 2, 'time: '.$time.'ms', $black);
		
		return $img_obj;
	}
	
	protected function _getCacheFilename($file_path, &$params) {
		if (!is_readable($file_path)) { throw new \Exception(__CLASS__.': file "'.$file_path.'" does not exist or is not readable'); }

		$types = array('jpg', 'gif', 'png');
		if (!isset($params['type']) OR !is_string($params['type']) OR !in_array($params['type'], $types))
			$params['type'] = 'jpg';
		
		// create hash for caching
		$filemtime = filemtime($file_path);
		$hash = md5($file_path.$filemtime.implode('',$params));
		$filename = $this->cache_dir.$hash.'.'.$params['type'];
		return $filename;
	}

	protected function _getParamsFromHash($cache_filename) {
		$cache_params_filename = $this->cache_dir . $cache_filename . '_params';
		
		if (!file_exists($cache_params_filename)) return false;
		
		$data = unserialize( file_get_contents( $cache_params_filename ));
		unlink( $cache_params_filename );
		return $data;
	}
		
	protected function _im_get($file) {
		$params['quality'] = '80%';
		$params['density'] = '96';
		$params['strip'] = '';
		$params['thumbnail'] = '"1000x1000"';
		$params['fill'] = '"#999"';
		$params['font'] = 'Arial';
		$params['pointsize '] = '60';

		// create parameters from $_GET
		foreach ($params as $key=>$value) $params[$key] = "-$key $value";

		$params = implode(' ', $params);
		$command = "convert $params '{$file}[0]'";

		// create target filename
		$target = tempnam('', 'im');
		unlink($target);
		$command .= " 'jpg:$target' 2>&1";

		$returner = shell_exec( $command );
		if (!is_file($target)) {
			return false;
		}

		return ($target);
	}

	// returns an image object of a given file path
	public function load($file_path) {
		if (!is_readable($file_path)) { throw new \Exception(__CLASS__.': file "'.$file_path.'" does not exist or is not readable'); }

		$data = getimagesize($file_path);

		if ($data[2] == 1)	$img_obj = imagecreatefromgif($file_path);
		elseif ($data[2] == 2)	$img_obj = imagecreatefromjpeg($file_path);
		elseif ($data[2] == 3)	$img_obj = imagecreatefrompng($file_path);
		else { throw new \Exception(__CLASS__.': file "'.$file_path.'" is not a valid image format'); }
		
		return $img_obj;
	}
	
	public function prepareGet($file_path, $params) {
		$cache_filename = $this->_getCacheFilename($file_path, $params);
		
		// only create params file if thumb was not created so far
		if (file_exists( $cache_filename )) return $cache_filename;

		// save params and file path to params file
		$data = array(
			'filename' => $file_path,
			'params' => $params
		);
		$cache_params_filename = $cache_filename . '_params';
		file_put_contents( $cache_params_filename, serialize($data) );
		
		return $cache_filename;
	}
		
	public function getFromHash($cache_filename) {
		$data = $this->_getParamsFromHash($cache_filename);
		$cache_file_name = $this->_getCacheFilename($data['filename'], $data['params']);

		// first try gd to process
		try {
			$thumb = $this->get($data['filename'], $data['params'], false, $cache_file_name);
		}
		// now try imagemagick to preprocess
		// im creates a temporary image which should be deleted after gd processing
		catch (Exception $e) {
			$thumb = $this->_im_get( $data['filename'] );
			$tmp_file = $thumb;
			
			// if preprocessing was successful use the new image for gd processing
			if (is_file($thumb)) {
				$thumb = $this->get( $thumb, $data['params'], false, $cache_file_name );
				unlink($tmp_file);
			}
		}

		if (!is_file($thumb)) return $data;
		return $thumb;
	}

	public function get($file_path, $params, $return_ressource = false, $filename = null) {
		// get cache filename if not passed
		if (is_null($filename)) {
			$filename = $this->_getCacheFilename($file_path, $params);
		}
		
		// if there is a cache file return it
		if (file_exists($filename) && (!isset($params['dev']) || $params['dev'] == false)) {
			if ($return_ressource) return $this->load($filename);
			else return $filename;
		}
		
		// validate params
		$params = $this->_validateParams($params);

		// load ressource
		$img_obj = $this->load($file_path);
		
		// use preFilter
		$img_obj = $this->preFilter($img_obj, $params);
		
		// render params
		$img_obj = $this->_render($img_obj, $params);
		
		// use preFilter
		$img_obj = $this->postFilter($img_obj, $params);

		// so this line means: if you want the ressource returned the image will not be cached
		if ($return_ressource) return $img_obj;

		// save data to file
		if ($params['type'] === 'jpg') imagejpeg($img_obj, $filename, 80);
		elseif ($params['type'] === 'png') imagepng($img_obj, $filename);
		elseif ($params['type'] === 'gif') imagegif($img_obj, $filename);
		
		return $filename;
		}

	
	protected function preFilter($img_obj, $params) {
		return $img_obj;
	}

	protected function postFilter($img_obj, $params) {
		return $img_obj;
	}
	
	protected function _render($img_obj, $params) {
		// start time measurement
		$time['start'] = microtime(true);

		// get info of img_obj
		$_SRC['width']		= imagesx($img_obj);
		$_SRC['height']		= imagesy($img_obj);

		// calculate ratio
		if (isset($params['longside'])) {
			if ($_SRC['width'] < $_SRC['height']) {
				$_DST['height']	= $params['longside'];
				$_DST['width']	= round($params['longside']/($_SRC['height']/$_SRC['width']));
			} else {
				$_DST['width']	= $params['longside'];
				$_DST['height']	= round($params['longside']/($_SRC['width']/$_SRC['height']));
			}
		} elseif (isset($params['shortside'])) {
			if ($_SRC['width'] < $_SRC['height']) {
				$_DST['width']	= $params['shortside'];
				$_DST['height']	= round($params['shortside']/($_SRC['width']/$_SRC['height']));
			} else {
				$_DST['height']	= $params['shortside'];
				$_DST['width']	= round($params['shortside']/($_SRC['height']/$_SRC['width']));
			}
		} else {
			// calculate destination dimension
			if (isset($params['width'])) $_DST['width'] = $params['width'];
			else $_DST['width'] = round($params['height']/($_SRC['height']/$_SRC['width']));

			if (isset($params['height'])) $_DST['height']	= $params['height'];
			else $_DST['height'] = round($params['width']/($_SRC['width']/$_SRC['height']));
		}

		// should the image get cropped
		$_DST['offset_w'] = 0;
		$_DST['offset_h'] = 0;
				
		if($params['crop'] === true) {
			$width_ratio = $_SRC['width']/$_DST['width'];
			$height_ratio = $_SRC['height']/$_DST['height'];

			// crop on width
			if ($width_ratio > $height_ratio) {
				switch($params['crop_position']) {
					case '1':
						$_DST['offset_w'] = 0;
						break;
					case '2':
						$_DST['offset_w'] = round(($_SRC['width']-$_DST['width']*$height_ratio)/2);
						break;
					case '3':
						$_DST['offset_w'] = round(($_SRC['width']-$_DST['width']*$height_ratio));
						break;
				}
				$_SRC['width'] = round($_DST['width']*$height_ratio);
			}
			// crop on height
			elseif ($width_ratio < $height_ratio) {
				switch($params['crop_position']) {
					case '1':
						$_DST['offset_h'] = 0;
						break;
					case '2':
						$_DST['offset_h'] = round(($_SRC['height']-$_DST['height']*$width_ratio)/2);
						break;
					case '3':
						$_DST['offset_h'] = round(($_SRC['height']-$_DST['height']*$width_ratio));
						break;
				}
				$_SRC['height'] = round($_DST['height']*$width_ratio);
			}
		}

		// if the source image is smaller than the destination image, all calculations before were crap
		if ($params['extrapolate'] === false && $_DST['height'] > $_SRC['height'] && $_DST['width'] > $_SRC['width']) {
			$_DST['width'] = $_SRC['width'];
			$_DST['height'] = $_SRC['height'];
		}

		### now create the image
		$_SRC['image'] = $img_obj;

		// if the source image is too big first scale down linear to an image four times bigger than the target image 
		if ($_DST['width']*4 < $_SRC['width'] AND $_DST['height']*4 < $_SRC['height']) {
			// multiplier of target dimension
			$_TMP['width'] = round($_DST['width']*4);
			$_TMP['height'] = round($_DST['height']*4);
			$_TMP['image'] = imagecreatetruecolor($_TMP['width'], $_TMP['height']);
			
			// preserve image transparancy
			imagealphablending($_TMP['image'], false);
			imagesavealpha($_TMP['image'],true);

			imagecopyresized($_TMP['image'], $_SRC['image'], 0, 0, $_DST['offset_w'], $_DST['offset_h'], $_TMP['width'], $_TMP['height'], $_SRC['width'], $_SRC['height']);
			$_SRC['image'] = $_TMP['image'];
			$_SRC['width'] = $_TMP['width'];
			$_SRC['height'] = $_TMP['height'];

			// when the image is prescaled there must not be cropped a specific region
			$_DST['offset_w'] = 0;
			$_DST['offset_h'] = 0;
			unset($_TMP['image']);
		}

		// create destination image
		$_DST['image'] = imagecreatetruecolor($_DST['width'], $_DST['height']);

		// preserve image transparancy
		imagealphablending($_DST['image'], false);
		imagesavealpha($_DST['image'],true);

		imagefill($_DST['image'], 0, 0, imagecolorallocate ($_DST['image'], 255, 255, 255));
		imagecopyresampled($_DST['image'], $_SRC['image'], 0, 0, $_DST['offset_w'], $_DST['offset_h'], $_DST['width'], $_DST['height'], $_SRC['width'], $_SRC['height']);
		
		// sharpen the image
		if ($params['sharpen'] === true) $_DST['image'] = $this->_unsharpMask($_DST['image']);

		// add the magnifier icon
		if ($params['hint'] === true) $_DST['image'] = $this->_addMagnifierIcon($_DST['image']);

		// add an overlay image
		if (!empty($params['overlay'])) $_DST['image'] = $this->_addOverlayImage($_DST['image'], $params['overlay'], $params['overlay_position']);

		// add frame
		if (!empty($params['frame'])) $_DST['image'] = $this->_addFrame($_DST['image'], $params['frame']);

		// add calculation time
		if ($params['dev'] === true) $_DST['image'] = $this->_addRenderTime($_DST['image'], $time['start']);
			
		return $_DST['image'];
	}
}
