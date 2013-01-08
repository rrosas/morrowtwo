<?php

/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>
    This file was contributed by Dirk Lüth.

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

class ImageObject {
	protected $image;
	protected $width;
	protected $height;
	
	public function __construct($image = null) {
		// check existance of GD library
			if(!function_exists('gd_info')) {
				throw new \Exception(__METHOD__ . ': GD library not found');
			}
		
		// fix for missing PHP function "imageconvolution"
			if(!function_exists('imageconvolution')) {
				function ImageConvolution($src, $filter, $filter_div, $offset){
				    if ($src==NULL) {
				        return false;
				    }
				 
				    $sx = imagesx($src);
				    $sy = imagesy($src);
				    $srcback = ImageCreateTrueColor ($sx, $sy);
				    ImageAlphaBlending($srcback, false);
				    ImageAlphaBlending($src, false);
				    ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);
				 
				    if($srcback==NULL){
				        return 0;
				    }
				 
				    for ($y=0; $y<$sy; ++$y){
				        for($x=0; $x<$sx; ++$x){
				            $new_r = $new_g = $new_b = 0;
				            $alpha = imagecolorat($srcback, @$pxl[0], @$pxl[1]);
				            $new_a = ($alpha >> 24);
				 
				            for ($j=0; $j<3; ++$j) {
				                $yv = min(max($y - 1 + $j, 0), $sy - 1);
				                for ($i=0; $i<3; ++$i) {
				                        $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
				                    $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
				                    $new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
				                    $new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
				                    $new_b += ($rgb & 0xFF) * $filter[$j][$i];
				                    $new_a += ((0x7F000000 & $rgb) >> 24) * $filter[$j][$i];
				                }
				            }
				 
				            $new_r = ($new_r/$filter_div)+$offset;
				            $new_g = ($new_g/$filter_div)+$offset;
				            $new_b = ($new_b/$filter_div)+$offset;
				            $new_a = ($new_a/$filter_div)+$offset;
				 
				            $new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
				            $new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
				            $new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);
				            $new_a = ($new_a > 127)? 127 : (($new_a < 0)? 0:$new_a);
				 
				            $new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				            if ($new_pxl == -1) {
				                $new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
				            }
				            if (($y >= 0) && ($y < $sy)) {
				                imagesetpixel($src, $x, $y, $new_pxl);
				            }
				        }
				    }
				    imagedestroy($srcback);
				    return true;
				}
			}
		
		if($image !== null && !empty($image)) {
			$this->load($image);
		}
	}
	
	public function __destruct() {
		if(is_resource($this->image) && get_resource_type($this->image) === 'gd') {
			@imagedestroy($this->image);
		}
		
		unset($this->width);
		unset($this->height);
		unset($this->image);
	}
	
	public function __get($property) {
		switch($property) {
			case 'width':
				return $this->width;
				break;
			case 'height':
				return $this->height;
				break;
		}
	}
	
	public function load($image) {
	// try to initialize from source image
			if(is_resource($image) && get_resource_type($image) === 'gd') {
				// source image is a GD resource
					$this->width  = @imagesx($image);
					$this->height = @imagesy($image);
					
					if(@imageistruecolor($image)) {
						$this->image = $image;
					} else {
						$this->image = @imagecreatetruecolor($this->width, $this->height);
						@imagecopy($this->image, $image, 0, 0, 0, 0, $this->width, $this->height);
					}
			} else {
				// source image is NOT a GD resource
					if(is_file($image) && is_readable($image)) {
						// source image is a path to an existing and readable file
							$size = @getimagesize($image);
							
							if($size != false) {
								$this->width  = $size[0];
								$this->height = $size[1];
								
								switch($size[2]) {
									case 1: // GIF
										$this->image = @imagecreatefromgif($image);
										break;
									case 2: // JPEG
										$this->image = @imagecreatefromjpeg($image);
										break;
									case 3: // PNG
										$this->image = @imagecreatefrompng($image);
										break;
								}
							}
					} else {
						// source image could still be a string or a remote url
							if(preg_match('/^(?:http|https|ftp):\/\//i', $image)) {
								if($temp = @file_get_contents($image)) {
									$image = $temp;
								}
							}
							
							$image = @imagecreatefromstring($image);
							
							if($image != false) {
								$this->width  = @imagesx($image);
								$this->height = @imagesy($image);
								$this->image  = $image;
							}
					}
			}
		
		// clean up
			unset($image);
		
		// check for successful initialization
			if(!is_resource($this->image) || !get_resource_type($this->image) === 'gd') {
				throw new \Exception(__METHOD__ . ': Source image could not be initialized');
			}
		
		@imagealphablending($this->image, false);
		@imagesavealpha($this->image, true);
		
		return $this;
	}
	
	public function grayscale() {
		if(self::$imagefilter === true) {
			@imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		} else {
			$temp = new \stdClass();
			
			for($x = 0; $x < $this->width; $x++) {
				for($y = 0; $y < $this->height; $y++) {
					$temp->source    = @imagecolorat($this->image, $x, $y);
					$temp->intensity = $this->color2grayscale($temp->source);
					
					$temp->final = $this->rgb2color($temp->intensity['r'], $temp->intensity['g'], $temp->intensity['b'], $temp->intensity['a']); 
					if(@imagesetpixel($this->image, $x, $y, $temp->final) === false) {
						throw new \Exception('Error setting color in image');
					}
				}
			}
			
			unset($temp);
		}
		
		return $this;
	}
	
	public function invert() {
		if(self::$imagefilter === true) {
			@imagefilter($this->image, IMG_FILTER_NEGATE);
		} else {
			$temp = new \stdClass();
			
			for($x = 0; $x < $this->width; $x++) {
				for($y = 0; $y < $this->height; $y++) {
					$temp->source    = @imagecolorat($this->image, $x, $y);
					$temp->final     = $this->color2rgb($temp->source);
					
					$temp->final['r']  = 255 - $temp->final['r'];
					$temp->final['g']  = 255 - $temp->final['g'];
					$temp->final['b']  = 255 - $temp->final['b'];
					
					$temp->final = $this->rgb2color($temp->final['r'], $temp->final['g'], $temp->final['b'], $temp->final['a']); 
					if(@imagesetpixel($this->image, $x, $y, $temp->final) === false) {
						throw new \Exception('Error setting color in image');
					}
				}
			}
			
			unset($temp);
		}
		
		return $this;
	}
	
	public function brightness($percent = 25) {
		if(self::$imagefilter === true) {
			@imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $percent);
		} else {
			$factor = ((int) $percent) / 100; 
			$temp   = new \stdClass();
		
			for($x = 0; $x < $this->width; $x++) {
				for($y = 0; $y < $this->height; $y++) {
					$temp->source    = @imagecolorat($this->image, $x, $y);
					$temp->source    = $this->color2rgb($temp->source);
					
					$temp->final     = $temp->source;
					$temp->final['r']  = min(255, max(0, $temp->final['r'] + ($temp->final['r'] * $factor)));
					$temp->final['g']  = min(255, max(0, $temp->final['g'] + ($temp->final['g'] * $factor)));
					$temp->final['b']  = min(255, max(0, $temp->final['b'] + ($temp->final['b'] * $factor)));
					$temp->final     = $this->rgb2color($temp->final['r'], $temp->final['g'], $temp->final['b'], $temp->final['a']);
					 
					if(@imagesetpixel($this->image, $x, $y, $temp->final) === false) {
						throw new \Exception('Error setting color in image');
					}
				}
			}
			
			unset($temp);
		}
		
		return $this;
	}
	
	public function hsl($hue = 0, $saturation = NULL, $lightness = NULL) {
		$hue = min(180, max(-180, (int) $hue));
		$hue = ($hue < 0) ? 360 + $hue : $hue;
		
		$saturation = ($saturation !== NULL) ? min(100, max(-100, (int) $saturation)) / 100 : NULL;
		$lightness  = ($lightness !== NULL) ? min(100, max(-100, (int) $lightness)) / 100 : NULL;
		
		$temp  = new \stdClass();
	
		for($x = 0; $x < $this->width; $x++) {
			for($y = 0; $y < $this->height; $y++) {
				$temp->source      = @imagecolorat($this->image, $x, $y);
				$temp->source      = $this->color2rgb($temp->source);
				
				$temp->final       = $this->rgb2hsl($temp->source['r'], $temp->source['g'], $temp->source['b'], $temp->source['a']);
				
				// process parameters
					// hue
						$temp->final['h'] += $hue;
					
					// saturation
						if($saturation !== NULL) {
							$temp->final['s'] = $saturation;
						}
						
					// lightness
						if($lightness !== NULL) {
							$temp->final['l']  = min(1, max(0, $temp->final['l'] + $lightness));
						}
				
				$temp->final = $this->hsl2rgb($temp->final['h'], $temp->final['s'], $temp->final['l'], $temp->final['a']);
				$temp->final = $this->rgb2color($temp->final['r'], $temp->final['g'], $temp->final['b'], $temp->final['a']);
				 
				if(@imagesetpixel($this->image, $x, $y, $temp->final) === false) {
					throw new \Exception('Error setting color in image');
				}
			}
		}
		
		unset($temp);
		
		return $this;
	}
	
	public function resize($width, $height, $mode = 0, $enlarge = true) {
		/*
		 * mode = 0: scale to width & height (ignoring aspect ratio)
		 * mode = 1: scale to width (keeping aspect ratio for height)
		 * mode = 2: scale to height (keeping aspect ratio for height)
		*/
		// check parameter
			if(!preg_match('/^\d+$/', $width)) {
				throw new \Exception(__METHOD__ . ': Parameter "width" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $height)) {
				throw new \Exception(__METHOD__ . ': Parameter "height" has to be numeric');
			}
		
		// process
			if($enlarge === true || $width > $this->width || $height > $this->height) {
				try {
					switch($mode) {
						case 1:
							if($width / $this->width > $height / $this->height) {
								// portrait
									$width  = $width;
									$height = (int) round($this->height * ($width / $this->width));
							} else {
								// landscape
									$width  = (int) round($this->width * ($height / $this->height));
									$height = $height;
							}
							break;
						case 2:
							if($width / $this->width > $height / $this->height) {
								// portrait
									$width  = (int) round($this->width * ($height / $this->height));
									$height = $height;
							} else {
								// landscape
									$width  = $width;
									$height = (int) round($this->height * ($width / $this->width));
							}
							break;
					}
					
					if(!$temp = @imagecreatetruecolor($width, $height)) {
						throw new \Exception('Temporary image could not be created');
					}
					if(@imagealphablending($temp, false) === false) {
						throw new \Exception('Error processing temporary image');
					}
					if(@imagesavealpha($temp, true) === false) {
						throw new \Exception('Error processing temporary image');
					}
					
					if(@imagecopyresampled($temp, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height) === false) {
						throw new \Exception('Final image could not be resampled');
					}
				} catch(Exception $e) {
					throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
				}
				
				$this->width  = $width;
				$this->height = $height;
				$this->image  = $temp;
			
			// clean up
				unset($temp);
			}
		
		return $this;
	}
	
	public function crop($width, $height, $x = false, $y = false) {
		// check parameter
			if(!preg_match('/^\d+$/', $width)) {
				throw new \Exception(__METHOD__ . ': Parameter "width" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $height)) {
				throw new \Exception(__METHOD__ . ': Parameter "height" has to be numeric');
			}
			
			if($x !== false && !preg_match('/^\d+$/', $x)) {
				throw new \Exception(__METHOD__ . ': Parameter "x" has to be numeric or false');
			}
			
			if($y !== false && !preg_match('/^\d+$/', $y)) {
				throw new \Exception(__METHOD__ . ': Parameter "y" has to be numeric or false');
			}
		
		// process
			try {
				if($x === false) {
					$x = floor($this->width / 2 - $width / 2);
				}
				
				if($y === false) {
					$y = floor($this->height / 2 - $height / 2);
				}

				if(!$temp = @imagecreatetruecolor($width, $height)) {
					throw new \Exception('Temporary image could not be created');
				}
				if(@imagealphablending($temp, false) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagesavealpha($temp, true) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				if(@imagecopy($temp, $this->image, 0, 0, $x, $y, $width, $height) === false) {
					throw new \Exception('Final image could not be resampled');
				}
		 	} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->width  = $width;
			$this->height = $height;
			$this->image  = $temp;
		
		// clean up
			unset($temp);
			unset($x);
			unset($y);
		
		return $this;
	}
	
	public function flip($axes = 1) {
		// check parameter
			if(!preg_match('/^[12]$/', $axes)) {
				throw new \Exception(__METHOD__ . ': Parameter "axes" has to be 1 or 2');
			}
		
		// process
			try {
				if(!$temp = @imagecreatetruecolor($this->width, $this->height)) {
					throw new \Exception('Temporary image could not be created');
				}
				
				if(@imagealphablending($temp, false) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagesavealpha($temp, true) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				switch($axes) {
					case 1:
						for($x = 0; $x < $this->width; $x++) {
							for($y = 0; $y < $this->height; $y++) {
								if(@imagecopy($temp, $this->image, $x, $this->height - $y - 1, $x, $y, 1, 1) === false) {
									throw new \Exception('Error while flipping the source image');
								}
							}
						}
						break;
					case 2:
						for($x = 0; $x < $this->width; $x++) {
							for($y = 0; $y < $this->height; $y++) {
								if(@imagecopy($temp, $this->image, $this->width - $x - 1, $y, $x, $y, 1, 1) === false) {
									throw new \Exception('Error while flipping the source image');
								}
							}
						}
						break;
				}
			} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->image  = $temp;
		
		// clean up
			unset($temp);
		
		return $this;
			
	}
	
	public function blur($amount = 100, $alpha = true) {
		// check parameter
			if(!preg_match('/^\d+$/', $amount)) {
				throw new \Exception(__METHOD__ . ': Parameter "amount" has to be numeric');
			}
		
		// calibrate parameters to Photoshop
			$amount = 100 - min(100, max(1, $amount));
			
		// process
			try {
				$temp = new \stdClass();
				
				if(!$temp->final = @imagecreatetruecolor($this->width, $this->height)) {
					throw new \Exception('Temporary image could not be created');
				}
				
				if(@imagealphablending($temp->final, false) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagesavealpha($temp->final, true) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				if(@imagecopymerge($temp->final, $this->image, 0, 0, 0, 0, $this->width, $this->height, 100) === false) {
					throw new \Exception('Error while processing the source image');
				}
				
				$matrix = array(
					array(1, 1, 1),  
					array(1, $amount, 1), 
					array(1, 1, 1)
				);
				
				if(@imageconvolution($temp->final, $matrix, $amount + 8, 0) === false) {
					throw new \Exception('Error while blurring');
				}
				
				// restore alpha transparency
					if($alpha === true) {
						$temp->color = new \stdClass();
						
						for($x = 0; $x < $this->width; $x++) {
							for($y = 0; $y < $this->height; $y++) {
								$temp->color->source = @imagecolorat($this->image, $x, $y);
								$temp->color->source = $this->color2rgb($temp->color->source);
								$temp->color->final  = @imagecolorat($temp->final, $x, $y);
								$temp->color->final  = $this->color2rgb($temp->color->final);
								
								$temp->color->final = $this->rgb2color($temp->color->final['r'], $temp->color->final['g'], $temp->color->final['b'], $temp->color->source['a']);  
								if(@imagesetpixel($temp->final, $x, $y, $temp->color->final) === false) {
				                	throw new \Exception('Error setting color in temporary image');
								}
							}
						}
					}
			} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->image = $temp->final;
		
		// clean up
			unset($temp);
			unset($matrix);
		
		return $this;
	}
	
	public function sharpen($amount = 50, $radius = 1, $threshold = 0) {
		// check parameter
			if(!preg_match('/^\d+$/', $amount)) {
				throw new \Exception(__METHOD__ . ': Parameter "amount" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $radius)) {
				throw new \Exception(__METHOD__ . ': Parameter "radius" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $threshold)) {
				throw new \Exception(__METHOD__ . ': Parameter "threshold" has to be numeric');
			}
		
		// calibrate parameters to Photoshop
			$amount    = min(500, $amount);
			$amount    = $amount * 0.016; 
			$radius    = min(50, $radius);
			$radius    = $radius * 2;
			$threshold = min(255, $threshold);
		
		// process
			if($radius > 0) {
				try {
					$temp = new \stdClass();
					if(!$temp->final = @imagecreatetruecolor($this->width, $this->height)) {
						throw new \Exception('Temporary image could not be created');
					}
					if(@imagealphablending($temp->final, false) === false) {
						throw new \Exception('Error processing temporary image');
					}
					if(@imagesavealpha($temp->final, true) === false) {
						throw new \Exception('Error processing temporary image');
					}
					if(!$temp->blur = @imagecreatetruecolor($this->width, $this->height)) {
						throw new \Exception('Temporary image could not be created');
					}
					if(@imagealphablending($temp->blur, false) === false) {
						throw new \Exception('Error processing temporary image');
					}
					if(@imagesavealpha($temp->blur, true) === false) {
						throw new \Exception('Error processing temporary image');
					}
					
					if(@imagecopy($temp->final, $this->image, 0, 0, 0, 0, $this->width, $this->height) === false) {
						throw new \Exception('Error while processing the source image');
					}
					
					if(@imagecopy($temp->blur, $this->image, 0, 0, 0, 0, $this->width, $this->height) === false) {
						throw new \Exception('Error while processing the source image');
					}
					
					$matrix = array(
						array(1, 2, 1),  
						array(2, 4, 2), 
						array(1, 2, 1)
					);
						
					if(@imageconvolution($temp->blur, $matrix, 16, 0) === false) {
						throw new \Exception('Error while sharpening');
					}
					
					// compare pixel
						$temp->color = new \stdClass();
						
						for($x = 0; $x < $this->width; $x++) {
							for($y = 0; $y < $this->height; $y++) {
								$temp->color->source = @imagecolorat($this->image, $x, $y);
								$temp->color->source = $this->color2rgb($temp->color->source);
				                 
								$temp->color->blur = @imagecolorat($temp->blur, $x, $y);
								$temp->color->blur = $this->color2rgb($temp->color->blur);
				                 
								// set pixel according to threshold setting
									$temp->color->final = array();
									
									$temp->color->final['r'] = (abs($temp->color->source['r'] - $temp->color->blur['r']) >= $threshold || $threshold == 0)
										? max(0, min(255, ($amount * ($temp->color->source['r'] - $temp->color->blur['r'])) + $temp->color->source['r']))  
										: $temp->color->source['r'];
									$temp->color->final['g'] = (abs($temp->color->source['g'] - $temp->color->blur['g']) >= $threshold || $threshold == 0)  
										? max(0, min(255, ($amount * ($temp->color->source['g'] - $temp->color->blur['g'])) + $temp->color->source['g']))  
										: $temp->color->source['g'];
									$temp->color->final['b'] = (abs($temp->color->blur['b'] - $temp->color->blur['b']) >= $threshold || $threshold == 0)  
										? max(0, min(255, ($amount * ($temp->color->blur['b'] - $temp->color->blur['b'])) + $temp->color->blur['b']))  
										: $temp->color->blur['b'];
									$temp->color->final['a'] = $temp->color->source['a'];
		
								if(($temp->color->source['r'] != $temp->color->final['r']) || ($temp->color->source['g'] != $temp->color->final['g']) || ($temp->color->source['b'] != $temp->color->final['b']) || ($temp->color->source['a'] != $temp->color->final['a'])) {
									$temp->color->final = $this->rgb2color($temp->color->final['r'], $temp->color->final['g'], $temp->color->final['b'], $temp->color->final['a']); 
		                    		if(@imagesetpixel($temp->final, $x, $y, $temp->color->final) === false) {
		                    			throw new \Exception('Error setting color in temporary image');
		                    		}
								}
							}
						}
					
					@imagedestroy($temp->blur);
				} catch(Exception $e) {
					throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
				}
			}
			
			$this->image  = $temp->final;
	
		// clean up
			unset($temp);
			unset($matrix);
		
		return $this;
	}
	
	public function addReflection($aperture = 80, $height = 140, $alpha = 40) {
		// check parameter
			if(!preg_match('/^\d+$/', $aperture)) {
				throw new \Exception(__METHOD__ . ': Parameter "aperture" has to be numeric');
			}
			
			if($height === false) {
				$height = round($this->height * ($aperture / 100));
			}
			
			if(!preg_match('/^\d+$/', $height)) {
				throw new \Exception(__METHOD__ . ': Parameter "height" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $alpha)) {
				throw new \Exception(__METHOD__ . ': Parameter "alpha" has to be numeric');
			}
		
		// calibrate parameters
			$aperture = min(100, $aperture);
			$aperture = round($this->height * ($aperture / 100));
			$alpha    = ((100 - min(100, $alpha)) / 100) * 127;

		// process
			try {
				$temp = new \stdClass();
				if(!$temp->final = @imagecreatetruecolor($this->width, $this->height + $height)) {
					throw new \Exception('Temporary image could not be created');
				}
				if(!$temp->reflection = new Image($this->image)) {
					throw new \Exception('Temporary image could not be created');
				}
				
				$temp->reflection->crop($this->width, $aperture, 0, $this->height - $aperture);
				$temp->reflection->resize($this->width, $height);
				$temp->reflection->flip();
				
				if(@imagealphablending($temp->final, false) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagesavealpha($temp->final, true) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagecopy($temp->final, $this->image, 0, 0, 0, 0, $this->width, $this->height) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				$temp->copy =& $temp->reflection->getResource();
				
				for($y = 0; $y < $height; $y++) {
					$a = round(((127 - $alpha) / $height) * $y + $alpha);
					
					for($x = 0; $x < $this->width; $x++) {
						$color = @imagecolorat($temp->copy, $x, $y);
						$color = $this->color2rgb($color);

						$color = @imagecolorallocatealpha($temp->final, $color['r'], $color['g'], $color['b'], min(127, $a + $color['a']));
						if(@imagesetpixel($temp->final, $x, $this->height + $y, $color) === false) {
							throw new \Exception('Error processing temporary image');
						}
					}
				}
				
				// clean up
					unset($a);
					unset($temp->copy);
					unset($temp->reflection);
			} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->height = $this->height + $height;
			$this->image  = $temp->final;
	
		// clean up
			unset($temp);
		
		return $this;
	}
	
	public function addBorder($color = '#000', $stroke = 1) {
		// convert color
			$color = $this->hex2rgb($color);
			
		// check parameter
			if($color === null) {
				throw new \Exception(__METHOD__ . ': Parameter "color" has to be a hex value');
			}
			
			if(!preg_match('/^\d+$/', $stroke)) {
				throw new \Exception(__METHOD__ . ': Parameter "stroke" has to be numeric');
			}
		
		// process
			try {
				$color  = $this->rgb2color($color['r'], $color['g'], $color['b'], $color['a']);
				$width  = $this->width + 2 * $stroke;
				$height = $this->height + 2 * $stroke;
				
				if(!$temp = @imagecreatetruecolor($width, $height)) {
					throw new \Exception('Temporary image could not be created');
				}
				
				if(@imagefill($temp, 0, 0, $color) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagecopy($temp, $this->image, $stroke, $stroke, 0, 0, $this->width, $this->height) === false) {
					throw new \Exception('Error processing temporary image');
				}
			} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->width  = $width;
			$this->height = $height;
			$this->image  = $temp;
		
		// clean up
			unset($color);
			unset($width);
			unset($height);
			unset($temp);
		
		return $this;
	}
	
	public function addShadow($color = '#000', $background = '#fff', $alpha = 50, $angle = 135, $distance = 2, $size = 5, $spread = 0) {
		// convert color
			$color      = $this->hex2rgb($color);
			$background = $this->hex2rgb($background);
			
		// check parameter
			if($color === null) {
				throw new \Exception(__METHOD__ . ': Parameter "color" has to be a hex value');
			}
			
			if($background === null) {
				throw new \Exception(__METHOD__ . ': Parameter "background" has to be a hex value');
			}
			
			if(!preg_match('/^\d+$/', $alpha)) {
				throw new \Exception(__METHOD__ . ': Parameter "alpha" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $angle)) {
				throw new \Exception(__METHOD__ . ': Parameter "angle" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $distance)) {
				throw new \Exception(__METHOD__ . ': Parameter "distance" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $size)) {
				throw new \Exception(__METHOD__ . ': Parameter "size" has to be numeric');
			}
			
			if(!preg_match('/^\d+$/', $spread)) {
				throw new \Exception(__METHOD__ . ': Parameter "spread" has to be numeric');
			}
		
		// calibrate parameters
			$alpha  = ((100 - min(100, $alpha)) / 100) * 127;
			$angle  = deg2rad(max(0, min(359, $angle)));
			$spread = min($size - 2, $spread);
			
			$offset = new \stdClass();
			$offset->x = round(sin($angle) * $distance);
			$offset->y = round(cos($angle) * $distance * -1);
			
			$offset->shadow = new \stdClass();
			$offset->shadow->x = max(0, $offset->x);
			$offset->shadow->y = max(0, $offset->y);
			
			$offset->final = new \stdClass();
			$offset->final->x = abs(min(0, $offset->x)) + $size;
			$offset->final->y = abs(min(0, $offset->y)) + $size;
		
		// process
			try {
				$width  = $this->width + $size * 2;
				$height = $this->height + $size * 2;
				
				$temp = new \stdClass();
				if(!$temp->final = @imagecreatetruecolor($width + abs($offset->x), $height + abs($offset->y))) {
					throw new \Exception('Temporary image could not be created');
				}
				if(@imagealphablending($temp->final, false) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagesavealpha($temp->final, true) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(!$temp->shadow = @imagecreatetruecolor($width, $height)) {
					throw new \Exception('Temporary image could not be created');
				}
				
				$rgbBackground = ($background['r'] << 16) + ($background['g'] << 8) + $background['b'] + (127 << 24);
				
				if(@imagefill($temp->final, 0, 0, $rgbBackground) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				$rgbBackground = $this->rgb2color($background['r'], $background['g'], $background['b'], $background['a']);
				$rgbShadow = $this->rgb2color($color['r'], $color['g'], $color['b'], $alpha);
				
				if(@imagefill($temp->shadow, 0, 0, $rgbBackground) === false) {
					throw new \Exception('Error processing temporary image');
				}
				if(@imagefilledrectangle($temp->shadow, $size - $spread, $size - $spread, $this->width + $size + $spread - 1, $this->height + $size + $spread - 1, $rgbShadow) === false) {
					throw new \Exception('Error processing temporary image');
				}
				
				$temp->shadow = new Image($temp->shadow);
				
				for($i = 0; $i < $size - $spread - 1; $i++) {
					$temp->shadow->blur(100, false);
				}
				$temp->shadow->blur(100);
				
				$temp->diff = array(
					'r' => abs($color['r'] - $background['r']),
					'g' => abs($color['g'] - $background['g']), 
					'b' => abs($color['b'] - $background['b'])
				);
				arsort($temp->diff);
				$dm = current($temp->diff);
				$dc = key($temp->diff);
				
				for($y = 0; $y < $height; $y++) {
					for($x = 0; $x < $width; $x++) {
						$rgb   = imagecolorat($temp->shadow->getResource(), $x, $y);
					
						switch($dc) {
							case 'r':
								$cv = ($rgb >> 16) & 0xFF;
								break;
							case 'g':
								$cv = ($rgb >> 8) & 0xFF;
								break;
							case 'b':
								$cv = $rgb & 0xFF;
								break;
						}
						
						$c = abs($cv - $background[$dc]);
						$a = ($dm > 0) ? 127 - round(127 * ($c / $dm)) : 127;
						
						if($a < 127) {
							$ac = $this->rgb2color($color['r'], $color['g'], $color['b'], $a);
							if(@imagesetpixel($temp->final, $x + $offset->shadow->x, $y + $offset->shadow->y, $ac) === false) {
								throw new \Exception('Error processing temporary image');
							}
						}
					}
				}
				
				// clean up
					unset($temp->shadow);
					unset($temp->diff);
					unset($dm);
					unset($dc);

				if(@imagecopy($temp->final, $this->image, $offset->final->x, $offset->final->y, 0, 0, $this->width, $this->height) === false) {
					throw new \Exception('Error processing temporary image');
				}
			} catch(Exception $e) {
				throw new \Exception(__METHOD__ . ': ' . $e->getMessage());
			}
			
			$this->width  = $width + abs($offset->x);
			$this->height = $height + abs($offset->y);
			$this->image  = $temp->final;
			
		// clean up
			unset($offset);
			unset($width);
			unset($height);
			unset($rgbBackground);
			unset($rgbShadow);
			unset($temp);
		
		return $this;
	}
	
	public function show($type = 'png', $interlace = false, $quality = NULL, $filter = NULL) {
		header('Content-type: image/' . $type);
		echo $this->get($type, $interlace, $quality, $filter);
	}
	
	public function get($type = 'png', $interlace = false, $quality = NULL, $filter = 248) {
		if($interlace === true) {
			@imageinterlace($this->image, 1);
		}
		
		ob_start();
		
		switch($type) {
			case 'png':
				$quality = ($quality === NULL) ? 9 : max(0, min(9, (int) $quality));
				
				@imagepng($this->image, NULL, $quality, $filter);
				break;
			case 'jpeg':
				$quality = ($quality === NULL) ? 100 : max(0, min(100, (int) $quality));
				
				@imagejpeg($this->image, NULL, $quality);
				break;
			case 'gif':
				@imagegif($this->image);
				break;
		}
			
		return trim(ob_get_clean());
	}
	
	public function &getResource() {
		return $this->image;
	}
	
	public function hex2rgb($value) {
		$return = null;
		
		$value = strtolower($value);
		
		if(preg_match('/^#[a-f0-9]{3,3}|[a-f0-9]{6,6}$/', $value)) {
			if(preg_match('/^#[a-f0-9]{3,3}$$/', $value)) {
				$value = preg_replace('/([a-f0-9])/', '\1\1', $value);
			}
			
			$return = array(
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			);
			
			sscanf(substr($value, 1), "%2x%2x%2x", $return['r'], $return['g'], $return['b']);
		}
		
		return $return;
	}
	
	public function rgb2hex($r, $g, $b) {
		return sprintf("%s%02X%02X%02X", '#', $r, $g, $b);
	}
	
	public function color2rgb($color) {
		$return = null;
		
		if(preg_match('/^\d+$/', $color)) {
			$return = array(
				'r' => ($color >> 16) & 0xFF,
				'g' => ($color >> 8) & 0xFF,
				'b' => $color & 0xFF,
				'a' => ($color & 0x7F000000) >> 24,
			);
		}
		
		return $return;
	}
	
	public function rgb2color($r, $g, $b, $a = 0) {
		return ($r << 16) + ($g << 8) + $b + ($a << 24);
	}
	
	public function color2grayscale($color) {
		$return = null;
		
		if(preg_match('/^\d+$/', $color)) {
			$return = self::color2rgb($color);
			
			$return['r'] = $return['g'] = $return['b'] = (($return['r'] * 0.2989) + ($return['g'] * 0.5870) + ($return['b'] * 0.1140));
		}
		
		return $return;
	}
	
	public function rgb2yuv($r, $g, $b, $a = 0) {
		$r = $r / 255;
		$g = $g / 255;
		$b = $b / 255;
		
		$y = ($r * 0.299 + $g * 0.587 + $b * 0.114) * 100;
		$u = (-$r * 0.1471376975169300226 - $g * 0.2888623024830699774 + $b * 0.436) * 100;
		$v = ($r * 0.615 - $g * 0.514985734664764622 - $b * 0.100014265335235378) * 100;
		
		return array(
			'y' => $y,
			'u' => $u,
			'v' => $v,
			'a' => $a,
		);
	}
	
	public function yuv2rgb($y, $u, $v, $a = 0) {
		$y = $y / 100;
		$u = $u / 100;
		$v = $v / 100;
		
		$r = abs(round(($y + 1.139837398373983740 * $v) * 255));
		$g = abs(round(($y - 0.3946517043589703515 * $u - 0.5805986066674976801 * $v) * 255));
		$b = abs(round(($y + 2.03211091743119266 * $u) * 255));
		
		return array(
			'r' => $r,
			'g' => $g,
			'b' => $b,
			'a' => $a,
		);
	}
	
	public function rgb2hsl($r, $g, $b, $a = 0) {
		$r /= 255;
		$g /= 255;
		$b /= 255;
		
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		
		$h = $s = $l = ($max + $min) / 2;
		
		if($max === $min) {
			$h = $s = 0;
		} else {
			$chroma = $max - $min;
			
			$s = ($l <= .5) ? $chroma / (2 * $l) : $chroma / (2 - 2 * $l);
			
			switch($max) {
				case $r:
					$h = (($g - $b) / $chroma) + ($g < $b ? 6 : 0);
					break;
				case $g:
					$h = (($b - $r) / $chroma) + 2;
					break;
				case $b:
					$h = (($r - $g) / $chroma) + 4;
					break;
			}
			
			$h *= 60;
		}
		
		return array(
			'h' => $h,
			's' => $s,
			'l' => $l,
			'a' => $a,
		);
	}
	
	public function hsl2rgb($h, $s, $l, $a = 0) {
		$r = $g = $b = 0;
		
		if($s === 0) {
			$r = $g = $b = $l;
		} else {
			$h /= 360;
			
			$q = ($l < .5) ? $l * (1 + $s) : $l + $s - $l * $s;
			$p = 2 * $l - $q;
			
			$r = $this->hue2rgb($p, $q, $h + 1/3);
			$g = $this->hue2rgb($p, $q, $h);
			$b = $this->hue2rgb($p, $q, $h - 1/3);
		}
		
		return array(
			'r' => $r * 255,
			'g' => $g * 255,
			'b' => $b * 255,
			'a' => $a,
		);
	}
	
	protected function hue2rgb($p, $q, $t) {
		if($t < 0) $t++;
		if($t > 1) $t--;
		if($t < 1/6) return $p + ($q - $p) * 6 * $t;
		if($t < 1/2) return $q;
		if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
		
		return $p;
	}
}