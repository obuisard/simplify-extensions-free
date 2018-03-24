<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;

class Image 
{	
	protected $image = null;
	protected $image_path = null;
	protected $image_path_remote = false;
	protected $image_mimetype = null;
	protected $image_width = 0;
	protected $image_height = 0;
	
	protected $thumbnail = null;
	protected $thumbnail_path = null;
	protected $thumbnail_width = 0;
	protected $thumbnail_height = 0;
	
	protected $thumbnail_high_res = null;
	protected $thumbnail_high_res_path = null;
	protected $thumbnail_high_res_width = 0;
	protected $thumbnail_high_res_height = 0;
	
	public function getImage() 
	{		
		return $this->image;
	}
	
	public function getImagePath()
	{
		return $this->image_path;
	}
	
	public function isImagePathRemote()
	{
		return $this->image_path_remote;
	}
	
	public function getImageMimeType()
	{
		return $this->image_mimetype;
	}	
	
	public function getImageWidth()
	{
		return $this->image_width;
	}
	
	public function getImageHeight()
	{
		return $this->image_height;
	}
	
	public function getThumbnail()
	{
		return $this->thumbnail;
	}
	
	public function getThumbnailWidth()
	{
		return $this->thumbnail_width;
	}
	
	public function getThumbnailHeight() 
	{
		return $this->thumbnail_height;
	}
	
	public function __construct($from_path = '', $width = 0, $height = 0)
	{
		$this->image = false;
		
		Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));
				
		if ($from_path && $width > 0 && $height > 0) {
			// create image with the required dimensions
			
			// test removed to allow image file names with spaces
			
			//if (substr_count($from_path, 'http') <= 0 && !file_exists($from_path)) { // check local image file
				//$this->image =  null;
			//} else {
			
				// check if $from_path is url, make sure it goes thru
				if (substr_count($from_path, 'http') > 0) {
					$this->image_path_remote = true;
						
					// HTTPS is only supported when the openssl extension is enabled
					// in order to minimize errors, we can replace the https:// with http://
					$from_path = str_replace('https://', 'http://', $from_path);
						
					$file_headers = @get_headers($from_path); // @ to avoid warnings
					if (!$file_headers || substr_count($file_headers[0], '200') <= 0) {
						$this->image =  null;
					}
				}
				
				if (!$this->image) {
			
					$this->image_path = $from_path;
					$image_info = @getimagesize($from_path); // @ to avoid warnings
					if (!$image_info) {
						$this->image = null;
					} else {	
						$original_width = $image_info[0];
						$original_height = $image_info[1];
						$this->image_mimetype = $image_info['mime'];
							
						// crop only if necessary
						if ($original_width == $width && $original_height == $height) {
							switch ($this->image_mimetype){
								case 'image/gif': $this->image = imagecreatefromgif($from_path); break;
								case 'image/jpeg': $this->image = imagecreatefromjpeg($from_path); break;
								case 'image/png': $this->image = imagecreatefrompng($from_path); break;
								default: $this->image = null; break; // unsupported type
							}
							
							if (is_null($this->image) || !$this->image) {
								$this->image = null;
							} else {
								$this->image_width = $width;
								$this->image_height = $height;
							}
						} else {
					
							$original_image = null;
							switch ($this->image_mimetype){
								case 'image/gif': $original_image = imagecreatefromgif($from_path); break;
								case 'image/jpeg': $original_image = imagecreatefromjpeg($from_path); break;
								case 'image/png': $original_image = imagecreatefrompng($from_path); break;
								default: $original_image = null; break; // unsupported type
							}
							
							if (is_null($original_image) || !$original_image) {
								$this->image = null;
							} else {
							
								$ratio = max($width/$original_width, $height/$original_height);
								$w = $width / $ratio;
								$h = $height / $ratio;
								$x = ($original_width - $width / $ratio) / 2;
								$y = ($original_height - $height / $ratio) / 2;
								
								$this->image = imagecreatetruecolor($width, $height);
								if (!$this->image) {
									$this->image = null;
								} else {
									$this->image_width = $width;
									$this->image_height = $height;
								
									if (imagecolortransparent($original_image) >= 0) { // only works with .gif!
										// Get the transparent color values for the current image
										//$rgba = imageColorsForIndex($original_image, imagecolortransparent($this->image));
										//$color = imageColorAllocate($original_image, $rgba['red'], $rgba['green'], $rgba['blue']);
								
										// Set the transparent color values for the new image
										//imagecolortransparent($this->image, $color);
										//imagefill($this->image, 0, 0, $color);
								
										imagecopyresized($this->image, $original_image, 0, 0, $x, $y, $width, $height, $w, $h);
									} else {
										//imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
										//imagealphablending($this->image, false);
										//imagesavealpha($this->image, true);
								
										imagecopyresampled($this->image, $original_image, 0, 0, $x, $y, $width, $height, $w, $h);
									}
								}
							}		
						}	
					}			
				}
			//}
			
		} elseif ($from_path) {
			// create image with dimensions of imported picture
			
			//$from_path = str_replace(' ', '%20', $from_path); // replaces spaces - does not work with file_exists
		
			// test removed to allow image file names with spaces
			
			//if (substr_count($from_path, 'http') <= 0 && !file_exists($from_path)) { // check local image file
				//$this->image =  null;
			//} else {
				
				// check if $from_path is url, make sure it goes thru
				if (substr_count($from_path, 'http') > 0) {
					$this->image_path_remote = true;
					
					// HTTPS is only supported when the openssl extension is enabled
					// in order to minimize errors, we can replace the https:// with http://
					$from_path = str_replace('https://', 'http://', $from_path);
					
					$file_headers = @get_headers($from_path); // @ to avoid warnings
					if (!$file_headers || substr_count($file_headers[0], '200') <= 0) {
						$this->image =  null;
					}
				}
				
				if (!$this->image) {
					$this->image_path = $from_path;
					$image_info = @getimagesize($from_path); // @ to avoid warnings
					if (!$image_info) {
						$this->image = null;
					} else {
						$this->image_width = $image_info[0]; // if width is 0, the file may not contain an image or multiple ones
						$this->image_height = $image_info[1];
						$this->image_mimetype = $image_info['mime'];
											
						switch ($this->image_mimetype){
							case 'image/gif': $this->image = imagecreatefromgif($from_path); break;
							case 'image/jpeg': $this->image = imagecreatefromjpeg($from_path); break;
							case 'image/png': $this->image = imagecreatefrompng($from_path); break;
							default: $this->image = null; break; // unsupported type
						}
						
						if (!$this->image) {
							$this->image = null;
						}					
					}
				}
			//}
		} elseif (empty($from_path) && $width > 0 && $height > 0) {
			// create blank image with required dimensions
			
			$this->image = imagecreatetruecolor($width, $height);			
			if (!$this->image) {
				$this->image = null;
			} else {
				$this->image_width = $width;
				$this->image_height = $height;
				//$white = imagecolorallocate($this->image, 255, 255, 255);
				//imagefill($this->image, 0, 0, $white);
			}
		} else {
			$this->image = null;
		}
	}
	
	public function setBackgroundColor($r, $g, $b, $alpha = -1) 
	{
		if ($alpha >= 0 && $alpha < 128) {
			$color = imagecolorallocatealpha($this->image, $r, $g, $b, $alpha);
		} else {
			$color = imagecolorallocate($this->image, $r, $g, $b);
		}
		imagefill($this->image, 0, 0, $color);
	}
	
	public function addImage($image_insert, $x, $y) 
	{
        if ($x < 0) { // center
        	$x = ceil(($this->image_width - $image_insert->image_width) / 2);
        }
        imagecopy($this->image, $image_insert->image, $x, $y, 0, 0, $image_insert->image_width, $image_insert->image_height);
    }
	
	public function addText($text, $font_path, $font_size, $x, $y, $font_r, $font_g, $font_b) 
	{	
		$text_color = imagecolorallocate($this->image, $font_r, $font_g, $font_b);
		
		if (empty($font_path)) {
			$text_width = imagefontwidth($font_size) * strlen($text);
			$y -= imagefontheight($font_size);
		} else {
			$text_box = imagettfbbox($font_size, 0, $font_path, $text);
			$text_width = $text_box[2] - $text_box[0];
		}
		 
		if ($x < 0) { // center
			$x = ceil(($this->image_width - $text_width) / 2);
		}
	
		if (empty($font_path)) {
            imagestring($this->image, $font_size, $x, $y, $text, $text_color);
		} else {
			imagettftext($this->image, $font_size, 0, $x, $y, $text_color, $font_path, $text);
		}
	}
	
	public function addCenteredText($text, $font_path, $font_size, $font_r, $font_g, $font_b, $max_width, $max_height, $offset_y = 0, $spacing = 0)
	{
		$text_color = imagecolorallocate($this->image, $font_r, $font_g, $font_b);

		// create lines depending on length of the text

		$words = explode(' ', $text);

		/*$lines = array();
		
		if (empty($font_path)) {
			$font_width = imagefontwidth($font_size);
			$font_height = imagefontheight($font_size);
		} else {
	        $ttf_box = imagettfbbox($font_size, 0, $font_path, $text);
			$font_width = $ttf_box[2] - $ttf_box[0];
			$font_height = $ttf_box[1] - $ttf_box[7];
		}*/

		do { // keep decreasing the font size if the text takes too much height or the text is too wide

			if (empty($font_path)) {
				$font_width = imagefontwidth($font_size);
				$font_height = imagefontheight($font_size);
			} else {
	            $ttf_box = imagettfbbox($font_size, 0, $font_path, $text);
	            $font_width = $ttf_box[2] - $ttf_box[0];
	            $font_height = $ttf_box[1] - $ttf_box[7];
			}

			$lines = array();
			$line = '';
            $number_of_words_taken = 0;
			foreach ($words as $word) {

                $line_width = 0;
                $space_width = 0;
				if (empty($font_path)) {
					if (!empty($line)) {
                        $line_width = $font_width * strlen($line);
                        $space_width = $font_width * strlen(' ');
                    }
					$word_width = $font_width * strlen($word);
				} else {
                    if (!empty($line)) {
                        $line_box = imagettfbbox($font_size, 0, $font_path, $line);
                        $line_width = $line_box[2] - $line_box[0];
                        $space_box = imagettfbbox($font_size, 0, $font_path, ' ');
                        $space_width = $space_box[2] - $space_box[0];
                    }
					$word_box = imagettfbbox($font_size, 0, $font_path, $word);
					$word_width = $word_box[2] - $word_box[0];
				}

				if (($line_width + $space_width + $word_width) <= $max_width) {
                    $number_of_words_taken++;
					if (!empty($line)) {
						$line .= ' '.$word;
					} else {
						$line = $word;
					}
				} elseif ($word_width <= $max_width) {
                    if (!empty($line)) {
                        $lines[] = $line;
                    }
                    $number_of_words_taken++;
                    $line = $word;
                } else {
                    break; // cannot take the line with the word or the word by itelf so need to reduce the font size
                }
			}

			if (!empty($line)) {
				$lines[] = $line;
			}

			$font_size--;
			if ($font_size < 1) {
				break;
			}

			$text_height = $font_height * count($lines) + ($spacing * (count($lines) - 1));

		} while ($text_height > $max_height || count($words) > $number_of_words_taken);

		$font_size++;

		// add each line to the image

		$total_lines = count($lines);
		$line_number = 1;
		foreach ($lines as $line) {
            if (empty($font_path)) {
            	$center_x = ceil(($this->image_width - ($font_width * strlen($line))) / 2);
            	$center_y = ceil((($this->image_height - ($font_height * $total_lines)) / 2) + (($line_number - 1) * $font_height));
            	imagestring($this->image, $font_size, $center_x, $center_y + $offset_y, $line, $text_color);
            } else {
            	$line_box = imagettfbbox($font_size, 0, $font_path, $line);
            	$font_width = $line_box[2] - $line_box[0];
            	//$font_height = $line_box[1] - $line_box[7];
            	
            	$center_x = ceil(($this->image_width - $font_width) / 2);
            	$center_y = ceil((($this->image_height - ($font_height * $total_lines) - ($spacing * ($total_lines - 1))) / 2) + (($line_number - 1) * ($font_height + $spacing)) + $font_height);
            	imagettftext($this->image, $font_size, 0, $center_x, $center_y + $offset_y, $text_color, $font_path, $line);
            }
            
			$line_number++;
		}
	}
	
	/*
	 * @return true if the image was created successfully, false otherwise
	*/
	public function createImage($to_path, $type = 'png', $quality = 0) 
	{		
		$creation_success = false;
		
		switch ($type) {
			case 'gif': $creation_success = imagegif($this->image, $to_path); break;
			case 'jpeg': case 'jpg': $creation_success = imagejpeg($this->image, $to_path, $quality); break;
			default: $creation_success = imagepng($this->image, $to_path, $quality); break;
		}
		
		return $creation_success;
	}
		
	/*
	 * @return true if the thumbnail was created successfully, false otherwise
	 */
	public function createThumbnail($width, $height, $crop, $quality, $filter, $to_path, $handle_high_resolution = false)
	{			
		$creation_success = false;
		
		$to_path_high_res= '';
		if ($handle_high_resolution) {
			$width = $width * 2;
			$height = $height * 2;
			$to_path_high_res = str_replace(".", "@2x.", $to_path); 
		}

		if ($crop) {
            $ratio = max($width/$this->image_width, $height/$this->image_height);
            $thumbnail_width = $width;
            $thumbnail_height = $height;
            $w = $width / $ratio;
            $h = $height / $ratio;
            $x = ($this->image_width - $width / $ratio) / 2;
            $y = ($this->image_height - $height / $ratio) / 2;
        } else {
            $ratio = min($width/$this->image_width, $height/$this->image_height);
            $thumbnail_width = $this->image_width * $ratio;
            $thumbnail_height = $this->image_height * $ratio;
            $w = $this->image_width;
            $h = $this->image_height;
            $x = 0;
            $y = 0;
		}
			
		$thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
		if ($thumbnail == false) {
			return $creation_success;
		} else {
			
			if ($this->isTransparent() && $this->image_mimetype == 'image/gif') {
				// Get the transparent color values for the current image
				
				$tidx = imagecolortransparent($this->image);
				$palletsize = imagecolorstotal($this->image);
				if ($tidx >= 0 && $tidx < $palletsize) {
					$rgba = imagecolorsforindex($this->image, $tidx);
				} else {
					$rgba = imagecolorsforindex($this->image, 0);
				}				
				
				$background = imagecolorallocate($this->image, $rgba['red'], $rgba['green'], $rgba['blue']);
			
				// Set the transparent color values for the new image
				imagecolortransparent($thumbnail, $background);
				imagefill($thumbnail, 0, 0, $background);
			
				imagecopyresized($thumbnail, $this->image, 0, 0, $x, $y, $thumbnail_width, $thumbnail_height, $w, $h);
			} else {		
				imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
				imagealphablending($thumbnail, false);
				imagesavealpha($thumbnail, true);
			
				imagecopyresampled($thumbnail, $this->image, 0, 0, $x, $y, $thumbnail_width, $thumbnail_height, $w, $h);
			}
		
			if (!is_null($filter)) {
				if (function_exists('imagefilter')) { // make sure there is imagefilter support in PHP
					if (is_array($filter)) {
						foreach($filter as $f) { // allow multiple filters
							if (is_array($f)) {
								extract($f);
								if (!isset($arg1)) {
									imagefilter($thumbnail, $type);
								} elseif (!isset($arg2)) {
									imagefilter($thumbnail, $type, $arg1);
								} elseif (!isset($arg3)) {
									imagefilter($thumbnail, $type, $arg1, $arg2);
								} elseif (!isset($arg4)) {
									imagefilter($thumbnail, $type, $arg1, $arg2, $arg3);
								} else {
									imagefilter($thumbnail, $type, $arg1, $arg2, $arg3, $arg4);
								}
							} else {
								imagefilter($thumbnail, $f);
							}
						}
					} else {
						imagefilter($thumbnail, $filter);
					}
				} else {
					Log::add('SYWImage:createThumbnail() - The imagefilter function for PHP is not available', Log::ERROR, 'syw');
				}
			}			
			
			switch ($this->image_mimetype) {
				case 'image/gif': 
					if ($handle_high_resolution) {
						$this->thumbnail_high_res = $thumbnail;
						$creation_high_res_success = imagegif($this->thumbnail_high_res, $to_path_high_res); 
						$this->thumbnail = imagecreatetruecolor($thumbnail_width / 2, $thumbnail_height / 2);	
						if ($this->thumbnail == false) {
							return $creation_success;
						}						
						
						// keep transparency
						$rgba = imagecolorsforindex($this->thumbnail_high_res, imagecolortransparent($this->thumbnail_high_res));
						$background = imagecolorallocate($this->thumbnail_high_res, $rgba['red'], $rgba['green'], $rgba['blue']);
						imagecolortransparent($this->thumbnail, $background);
						imagefill($this->thumbnail, 0, 0, $background);
												
						if (!imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
							$creation_low_res_success = false;
						} else {
							$creation_low_res_success = imagegif($this->thumbnail, $to_path);
						}
						if ($creation_high_res_success && $creation_low_res_success) {
							$creation_success = true;
						}
					} else {
						$this->thumbnail = $thumbnail;
						$creation_success = imagegif($this->thumbnail, $to_path);
					}
					break;
				case 'image/jpeg': 
					if ($handle_high_resolution) {
						$this->thumbnail_high_res = $thumbnail;
						$creation_high_res_success = imagejpeg($this->thumbnail_high_res, $to_path_high_res, $quality); 
						$this->thumbnail = imagecreatetruecolor($thumbnail_width / 2, $thumbnail_height / 2);	
						if ($this->thumbnail == false) {
							return $creation_success;
						}	
						if (!imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
							$creation_low_res_success = false;
						} else {
							$creation_low_res_success = imagejpeg($this->thumbnail, $to_path, $quality);
						}
						if ($creation_high_res_success && $creation_low_res_success) {
							$creation_success = true;
						}
					} else {
						$this->thumbnail = $thumbnail;
						$creation_success = imagejpeg($this->thumbnail, $to_path, $quality);
					}
					break;
				case 'image/png': 
					if ($handle_high_resolution) {
						$this->thumbnail_high_res = $thumbnail;
						$creation_high_res_success = imagepng($this->thumbnail_high_res, $to_path_high_res, $quality); 
						$this->thumbnail = imagecreatetruecolor($thumbnail_width / 2, $thumbnail_height / 2);
						if ($this->thumbnail == false) {
							return $creation_success;
						}	
						
						// keep transparency
						// needed ? imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
						imagealphablending($this->thumbnail, false);
						imagesavealpha($this->thumbnail, true);
																		
						if (!imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
							$creation_low_res_success = false;
						} else {
							$creation_low_res_success = imagepng($this->thumbnail, $to_path, $quality);
						}
						if ($creation_high_res_success && $creation_low_res_success) {
							$creation_success = true;
						}
					} else {
						$this->thumbnail = $thumbnail;
						$creation_success = imagepng($this->thumbnail, $to_path, $quality);
					}
					break;
				default: return $creation_success;
			}
		}
		
		if ($creation_success) {
			$this->thumbnail_path = $to_path;
			$this->thumbnail_width = $thumbnail_width;
			$this->thumbnail_height = $thumbnail_height;
			if ($handle_high_resolution) {
				$this->thumbnail_high_res_path = $to_path_high_res;
				$this->thumbnail_high_res_width = $thumbnail_width;
				$this->thumbnail_high_res_height = $thumbnail_height;
				$this->thumbnail_width = $thumbnail_width / 2;
				$this->thumbnail_height = $thumbnail_height / 2;
			}
		}
		
		if (is_resource($thumbnail)) {
			imagedestroy($thumbnail);
		}
		
		return $creation_success;
	}
	
	public function isTransparent()
	{
		return (imagecolortransparent($this->image) >= 0);
		
		//$tidx = imagecolortransparent($this->image);
		//$palletsize = imagecolorstotal($this->image);
		//if ($tidx >= 0 && $tidx < $palletsize) {
			//return true;
		//}
		
		//return false;
	}
	
	public function destroy()
	{
		if (is_resource($this->thumbnail)) {
			imagedestroy($this->thumbnail);
		}
		if (is_resource($this->thumbnail_high_res)) {
			imagedestroy($this->thumbnail_high_res);
		}
		if (is_resource($this->image)) {
			imagedestroy($this->image);
		}
	}
	
	public function __destruct()
	{
		$this->destroy();
	}
	
}
?>