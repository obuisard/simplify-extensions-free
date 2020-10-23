<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;

/**
 * Management of an image resource
 * Based on PHP GD extension
 * Image support for .gif, .jpg, .jpeg, .png, .webp
 *
 * @author Olivier Buisard
 *
 */
class Image
{
	/*
	 * image resource
	 */
	protected $image = null;

	/*
	 * image path
	 */
	protected $image_path = null;

	/*
	 * is the path of the image remote
	 */
	protected $image_path_remote = false;

	/*
	 * the image mime type
	 */
	protected $image_mimetype = null;

	/*
	 * the image width
	 */
	protected $image_width = 0;

	/*
	 * the image height
	 */
	protected $image_height = 0;

	/*
	 * image transparency
	 */
	protected $is_image_transparent = false;

	/*
	 * the image thumbnail, path, width and height
	 */
	protected $thumbnail = null;
	protected $thumbnail_path = null;
	protected $thumbnail_width = 0;
	protected $thumbnail_height = 0;

	/*
	 * the image thumbnail, path, width and height, twice as big for high-resolution displays
	 */
	protected $thumbnail_high_res = null;
	protected $thumbnail_high_res_path = null;
	protected $thumbnail_high_res_width = 0;
	protected $thumbnail_high_res_height = 0;

	/*
	 * The memory limit currently established on the server
	 */
	private $memory_limit = -1;

	/**
	 * Get the image resource
	 * @return NULL|resource
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * Get the original image path
	 * @return NULL|string
	 */
	public function getImagePath()
	{
		return $this->image_path;
	}

	/**
	 * Is the image remote?
	 * @return boolean
	 */
	public function isImagePathRemote()
	{
		return $this->image_path_remote;
	}

	/**
	 * Get the mime type of the image
	 * @return NULL|string
	 */
	public function getImageMimeType()
	{
		return $this->image_mimetype;
	}

	/**
	 * Get the image width
	 * @return number
	 */
	public function getImageWidth()
	{
		return $this->image_width;
	}

	/**
	 * Get the image height
	 * @return number
	 */
	public function getImageHeight()
	{
		return $this->image_height;
	}

	/**
	 * Get the thumbnail resource
	 * @return NULL|resource
	 */
	public function getThumbnail($high_res = false)
	{
		if ($high_res) {
			return $this->thumbnail_high_res;
		}
		return $this->thumbnail;
	}

	/**
	 * Get the thumbnail path
	 * @return NULL|string
	 */
	public function getThumbnailPath($high_res = false)
	{
		if ($high_res) {
			return $this->thumbnail_high_res_path;
		}
		return $this->thumbnail_path;
	}

	/**
	 * Get the thumbnail width
	 * @return number
	 */
	public function getThumbnailWidth($high_res = false)
	{
		if ($high_res) {
			return $this->thumbnail_high_res_width;
		}
		return $this->thumbnail_width;
	}

	/**
	 * Get the thumbnail height
	 * @return number
	 */
	public function getThumbnailHeight($high_res = false)
	{
		if ($high_res) {
			return $this->thumbnail_high_res_height;
		}
		return $this->thumbnail_height;
	}

	/**
	 * Image resource creation
	 *
	 * @param string $from_path
	 * @param number $width
	 * @param number $height
	 * @param boolean|string $increase_memory_limit
	 */
	public function __construct($from_path = '', $width = 0, $height = 0, $increase_memory_limit = false)
	{
		$this->image = false;

		if (is_bool($increase_memory_limit) && $increase_memory_limit) {
			$this->set_memory_limit();
			$this->increase_memory_limit();
		} else if (is_string($increase_memory_limit)) {
			$this->set_memory_limit();
			$this->increase_memory_limit($increase_memory_limit);
		}

		Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));

		if ($from_path && $width > 0 && $height > 0) { // create image with the required dimensions
			
			if (substr($from_path, 0, 4) === 'data') {
				
				$data_array = explode(';', $from_path);
				
				$image_string = str_replace('base64,', '', $data_array[1]);
				$image_string = base64_decode($image_string);
				$original_image = @imagecreatefromstring($image_string); // no support for WebP
				
				if (!$original_image) {
					$this->image = null;
				} else {
					$this->image_mimetype = str_replace('data:', '', $data_array[0]);
					$this->image_path = $from_path;
					
					$original_width = imagesx($original_image);
					$original_height = imagesy($original_image);
					
					// crop only if necessary
					if ($original_width == $width && $original_height == $height) {
						$this->image = $original_image;
						
						$this->image_width = $width;
						$this->image_height = $height;
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
							
							$this->copy_resource($this->image, $original_image, 0, 0, $x, $y, $width, $height, $w, $h);
						}
					}
				}
			} else {
				
				// allow image file names with spaces
				$from_path = str_replace('%20', ' ', $from_path);
				
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
					
					$this->image_mimetype = $this->get_image_mime_type($from_path);
					if (!$this->image_mimetype) {
						$this->image = null;
					} else {
						switch ($this->image_mimetype){
							case 'image/gif': $original_image = @imagecreatefromgif($from_path); break;
							case 'image/jpeg': $original_image = @imagecreatefromjpeg($from_path); break;
							case 'image/png': $original_image = @imagecreatefrompng($from_path); break;
							case 'image/webp': $original_image = @imagecreatefromwebp($from_path); break;
							default: $original_image = false; // unsupported type
						}
						
						if (!$original_image) {
							$this->image = null;
						} else {
							$original_width = imagesx($original_image);
							$original_height = imagesy($original_image);
							
							// crop only if necessary
							if ($original_width == $width && $original_height == $height) {
								$this->image = $original_image;
								
								$this->image_width = $width;
								$this->image_height = $height;
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
									
									$this->copy_resource($this->image, $original_image, 0, 0, $x, $y, $width, $height, $w, $h);
								}
							}
							
							unset($original_image);
						}
					}
				}
			}
			
		} elseif ($from_path) { // create image with dimensions of imported picture
			
			if (substr($from_path, 0, 4) === 'data') {
				
				$data_array = explode(';', $from_path);
				
				$this->image_mimetype = str_replace('data:', '', $data_array[0]);
				$this->image_path = $from_path;
				
				$image_string = str_replace('base64,', '', $data_array[1]);
				$image_string = base64_decode($image_string);
				$this->image = @imagecreatefromstring($image_string); // no support for WebP
				
				if (!$this->image) {
					$this->image = null;
				}
			} else {
				// allow image file names with spaces
				$from_path = str_replace('%20', ' ', $from_path);
				
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
					
					$this->image_mimetype = $this->get_image_mime_type($from_path);
					if (!$this->image_mimetype) {
						$this->image = null;
					} else {
						switch ($this->image_mimetype){
							case 'image/gif': $this->image = @imagecreatefromgif($from_path); break;
							case 'image/jpeg': $this->image = @imagecreatefromjpeg($from_path); break;
							case 'image/png': $this->image = @imagecreatefrompng($from_path); break;
							case 'image/webp': $this->image = @imagecreatefromwebp($from_path); break;
							default: $this->image = false; // unsupported type
						}
						
						if (!$this->image) {
							$this->image = null;
						}
					}
				}
			}
		} elseif (empty($from_path) && $width > 0 && $height > 0) { // create blank image with required dimensions
			
			$this->image = imagecreatetruecolor($width, $height);
			if (!$this->image) {
				$this->image = null;
			} else {
				$this->image_width = $width;
				$this->image_height = $height;
			}
		} else {
			$this->image = null;
		}
		
		if ($this->image) {
			
			if ($this->image_mimetype && $this->image_mimetype !== 'image/jpeg') {
				$this->is_image_transparent = (imagecolortransparent($this->image) >= 0) ? true : false;
			}
			
			if ($this->image_width == 0) {
				$this->image_width = imagesx($this->image);
			}
			
			if ($this->image_height == 0) {
				$this->image_height = imagesy($this->image);
			}
		}
	}

	/*
	 * Get the mime type of an image file, using as little memory as possible
	 */
	protected function get_image_mime_type($path)
	{
		$mime_type = false;

		// for safety

		$path_array = explode('?', $path);
		$path = $path_array[0];

		$extension = explode('.', $path);
		$extension = end($extension);
		if (!$extension) {
			return $mime_type;
		}

		if (!in_array(strtolower($extension), array('gif', 'jpg', 'jpeg', 'png', 'webp'))) {
			return $mime_type;
		}

		if (function_exists('exif_imagetype')) {
			$image_type = @exif_imagetype($path); // WebP support in PHP 7.1.0
			if ($image_type) {
				return image_type_to_mime_type($image_type);
			}
		}

		if (function_exists('mime_content_type')) {
			$file_type = strtolower(mime_content_type($path));

			if (substr($file_type, 0, 6) === 'image/') {
				return $file_type;
			}
		} else if (function_exists('finfo_open')) { // finfo_file is a replacement for mime_content_type
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // finfo reads the header of the file only
			$file_type = finfo_file($finfo, $path);
			finfo_close($finfo);

			if (substr($file_type, 0, 6) === 'image/') {
				return $file_type;
			}
		}

		// last resort
		// getimagesize reads the whole file, slowest method
		$image_info = @getimagesize($path); // WebP support in PHP 7.1.0
		if ($image_info) {
			return $image_info['mime'];
		}

		return $mime_type;
	}

	/**
	 * Copy a resource source into a resource target with specific dimensions
	 *
	 * @param resource $target
	 * @param resource $source
	 * @param number $origin_x
	 * @param number $origin_y
	 * @param number $source_origin_x
	 * @param number $source_origin_y
	 * @param number $width
	 * @param number $height
	 * @param number $source_width
	 * @param number $source_height
	 */
	protected function copy_resource($target, $source, $origin_x = 0, $origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $width, $height, $source_width = 0, $source_height = 0)
	{
		if ($this->image_mimetype === 'image/gif') {

			if (imagecolortransparent($source) >= 0) {

				$tidx = imagecolortransparent($source);
				$palletsize = imagecolorstotal($source);
				if ($tidx >= 0 && $tidx < $palletsize) {
					$rgba = imagecolorsforindex($source, $tidx);
				} else {
					$rgba = imagecolorsforindex($source, 0);
				}

				$background = imagecolorallocate($source, $rgba['red'], $rgba['green'], $rgba['blue']);

				// Set the transparent color values for the new image
				imagecolortransparent($target, $background);
				imagefill($target, 0, 0, $background);
			}

			imagecopyresized($target, $source, 0, 0, $source_origin_x, $source_origin_y, $width, $height, $source_width, $source_height);
		} else {

			if ($this->image_mimetype !== 'image/jpeg') { // no transparency in jpegs

				imagecolortransparent($target, imagecolorallocatealpha($target, 0, 0, 0, 127)); // all transparent
				imagealphablending($target, false); // turn off blending to keep alpha channel from originial
				imagesavealpha($target, true); // keep alpha info for PNG (WebP ?)
			}

			imagecopyresampled($target, $source, 0, 0, $source_origin_x, $source_origin_y, $width, $height, $source_width, $source_height);
		}
	}

	/**
	 * Apply GD filters to a target resource
	 *
	 * @param resource $to_resource
	 * @param integer|array $filter
	 */
	protected function apply_filters($to_resource, $filter)
	{
		if (function_exists('imagefilter')) { // make sure there is imagefilter support in PHP
			if (is_array($filter)) {
				foreach($filter as $f) { // allow multiple filters
					if (is_array($f)) {
						extract($f);
						if (!isset($arg1)) {
							imagefilter($to_resource, $type);
						} elseif (!isset($arg2)) {
							imagefilter($to_resource, $type, $arg1);
						} elseif (!isset($arg3)) {
							imagefilter($to_resource, $type, $arg1, $arg2);
						} elseif (!isset($arg4)) {
							imagefilter($to_resource, $type, $arg1, $arg2, $arg3);
						} else {
							imagefilter($to_resource, $type, $arg1, $arg2, $arg3, $arg4);
						}
						unset($arg1); unset($arg2); unset($arg3); unset($arg4);
					} else {
						imagefilter($to_resource, $f);
					}
				}
			} else {
				imagefilter($to_resource, $filter);
			}
		} else {
			Log::add('SYWImage:createThumbnail() - The imagefilter function for PHP is not available', Log::ERROR, 'syw');
		}
	}

	/**
	 * Set the image background color
	 *
	 * @param number $r
	 * @param number $g
	 * @param number $b
	 * @param number $alpha
	 */
	public function setBackgroundColor($r, $g, $b, $alpha = -1)
	{
		if ($alpha >= 0 && $alpha < 128) {
			$color = imagecolorallocatealpha($this->image, $r, $g, $b, $alpha);
		} else {
			$color = imagecolorallocate($this->image, $r, $g, $b);
		}
		imagefill($this->image, 0, 0, $color);
	}

	/**
	 * Insert an image resource into the image
	 *
	 * @param resource $image_insert
	 * @param number $x
	 * @param number $y
	 */
	public function addImage($image_insert, $x, $y)
	{
        if ($x < 0) { // center
        	$x = ceil(($this->image_width - $image_insert->image_width) / 2);
        }
        imagecopy($this->image, $image_insert->image, $x, $y, 0, 0, $image_insert->image_width, $image_insert->image_height);
    }

    /**
     * Add text at specific coordinates to image
     *
     * @param string $text
     * @param string $font_path
     * @param number $font_size
     * @param number $x
     * @param number $y
     * @param number $font_r
     * @param number $font_g
     * @param number $font_b
     */
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

	/**
	 * Add centered text to image
	 *
	 * @param string $text
     * @param string $font_path
     * @param number $font_size
	 * @param number $font_r
	 * @param number $font_g
	 * @param number $font_b
	 * @param number $max_width
	 * @param number $max_height
	 * @param number $offset_y
	 * @param number $spacing
	 */
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

	/**
	 * Output the image to file
	 *
	 * @param string $to_path the output file path - if the file extension is missing, it will use the image original path extension or the file extension corresponding to to_type
	 * @param string $to_type the output file mime-type (ex: image/png) - if unset, will use the image mime-type - file extension from to_path has priority over the mime type
	 * @param number $quality the output image quality 0 - 100 no matter the image type
	 * @param NULL|integer|array $filter
	 * @return boolean true if the image was created successfully, false otherwise
	 */
	public function toFile($to_path, $to_type = '', $quality = 75, $filter = null)
	{
		$creation_success = false;

		if (!is_null($filter)) {
			$this->apply_filters($this->image, $filter);
		}

		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		// if to_path extension is missing, use the file extension corresponding to the mime type
		$path_array = explode('.', $to_path);
		if (count($path_array) == 1) {
			switch ($mime_type) {
				case 'image/gif': $to_path .= '.gif' ; break;
				case 'image/jpeg': $to_path .= '.jpg'; break;
				case 'image/png': $to_path .= '.png'; break;
				case 'image/webp': $to_path .= '.webp'; break;
				default: return $creation_success;
			}
		} else {
			// the file extension has priority over the mime type in case the file extension and the mime type do not correspond
			$extension = end($path_array);
			switch (strtolower($extension)) {
				case 'gif': $mime_type = 'image/gif' ; break;
				case 'jpg': case 'jpeg': $mime_type = 'image/jpeg'; break;
				case 'png': $mime_type = 'image/png'; break;
				case 'webp': $mime_type = 'image/webp'; break;
				default: return $creation_success;
			}
		}

		switch ($mime_type) {
			case 'image/gif': $creation_success = imagegif($this->image, $to_path); break;
			case 'image/jpeg': $creation_success = imagejpeg($this->image, $to_path, $quality); break;
			case 'image/png':
				$quality = ($quality - 100) / 11.111111;
				$quality = round(abs($quality));
				$creation_success = imagepng($this->image, $to_path, $quality);
			break;
			case 'image/webp': $creation_success = imagewebp($this->image, $to_path, $quality); break;
		}

		return $creation_success;
	}

	/**
	 * Output the image to base64 encoded string
	 *
	 * @param string $to_type the output file mime-type (ex: image/png) - if unset, will use the image mime-type
	 * @param number $quality the output image quality 0 - 100 no matter the image type
	 * @param NULL|integer|array $filter
	 * @return NULL|string the base64 encoded image
	 */
	public function toEncodedString($to_type = '', $quality = 75, $filter = null)
	{
		if (!is_null($filter)) {
			$this->apply_filters($this->image, $filter);
		}

		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		ob_start();

		switch ($mime_type) {
			case 'image/gif': imagegif($this->image); break;
			case 'image/jpeg': imagejpeg($this->image, null, $quality); break;
			case 'image/png':
				$quality = ($quality - 100) / 11.111111;
				$quality = round(abs($quality));
				imagepng($this->image, null, $quality);
			break;
			case 'image/webp': imagewebp($this->image, null, $quality); break;
		}

		$raw_stream = ob_get_contents(); // read from buffer

		ob_end_clean();

		if (empty($raw_stream)) {
			return null;
		}

		return 'data:' . $mime_type . ';base64,' . base64_encode($raw_stream);
	}

	/**
	 * Output the image as thumbnail to file
	 *
	 * @param string $to_path the output file path - if the file extension is missing, it will use the image original path extension or the file extension corresponding to to_type
	 * @param string $to_type the output file mime-type (ex: image/png) - if unset, will use the image mime-type - file extension from to_path has priority over the mime type
	 * @param number $width
	 * @param number $height
	 * @param boolean $crop
	 * @param number $quality the output image quality 0 - 100 no matter the image type
	 * @param integer|array $filter
	 * @param boolean $high_resolution
	 * @return boolean true if the thumbnail was created successfully, false otherwise
	 */
	public function toThumbnail($to_path, $to_type = '', $width = 80, $height = 80, $crop = true, $quality = 75, $filter = null, $high_resolution = false)
	{
		$creation_success = false;

		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		// if to_path extension is missing, use the file extension corresponding to the mime type
		$path_array = explode('.', $to_path);
		if (count($path_array) == 1) {
			switch ($mime_type) {
				case 'image/gif': $to_path .= '.gif' ; break;
				case 'image/jpeg': $to_path .= '.jpg'; break;
				case 'image/png': $to_path .= '.png'; break;
				case 'image/webp': $to_path .= '.webp'; break;
				default: return $creation_success;
			}
		} else {
			// the file extension has priority over the mime type in case the file extension and the mime type do not correspond
			$extension = end($path_array);
			switch (strtolower($extension)) {
				case 'gif': $mime_type = 'image/gif' ; break;
				case 'jpg': case 'jpeg': $mime_type = 'image/jpeg'; break;
				case 'png': $mime_type = 'image/png'; break;
				case 'webp': $mime_type = 'image/webp'; break;
				default: return $creation_success;
			}
		}

		$to_path_high_res= '';
		if ($high_resolution) {
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
		if ($thumbnail !== false) {

			$this->copy_resource($thumbnail, $this->image, 0, 0, $x, $y, $thumbnail_width, $thumbnail_height, $w, $h);

			if (!is_null($filter)) {
				$this->apply_filters($thumbnail, $filter);
			}

			if ($high_resolution) {
				$this->thumbnail = imagecreatetruecolor($thumbnail_width / 2, $thumbnail_height / 2);
				if ($this->thumbnail !== false) {
					$this->thumbnail_high_res = $thumbnail;
				} else { // if one of the 2 thumbnails fails, do not create any of them

					if (is_resource($thumbnail)) {
						imagedestroy($thumbnail);
					}

					unset($thumbnail);

					return $creation_success;
				}
			} else {
				$this->thumbnail = $thumbnail;
			}

			unset($thumbnail);

			switch ($mime_type) {
				case 'image/gif':
					if ($high_resolution) {
						if (imagegif($this->thumbnail_high_res, $to_path_high_res)) {
	
							// keep transparency
							$rgba = imagecolorsforindex($this->thumbnail_high_res, imagecolortransparent($this->thumbnail_high_res));
							$background = imagecolorallocate($this->thumbnail_high_res, $rgba['red'], $rgba['green'], $rgba['blue']);
							imagecolortransparent($this->thumbnail, $background);
							imagefill($this->thumbnail, 0, 0, $background);

							if (imagecopyresized($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
								$creation_success = imagegif($this->thumbnail, $to_path);
							}
						}
					} else {
						$creation_success = imagegif($this->thumbnail, $to_path);
					}
					break;
				case 'image/jpeg':
					if ($high_resolution) {
						if (imagejpeg($this->thumbnail_high_res, $to_path_high_res, $quality)) {
							if (imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
								$creation_success = imagejpeg($this->thumbnail, $to_path, $quality);
							}
						}
					} else {
						$creation_success = imagejpeg($this->thumbnail, $to_path, $quality);
					}
					break;
				case 'image/png':

					$quality = ($quality - 100) / 11.111111;
					$quality = round(abs($quality));

					if ($high_resolution) {
						if (imagepng($this->thumbnail_high_res, $to_path_high_res, $quality)) {

							imagealphablending($this->thumbnail, false);
							imagesavealpha($this->thumbnail, true);

							if (imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
								$creation_success = imagepng($this->thumbnail, $to_path, $quality);
							}
						}
					} else {
						$creation_success = imagepng($this->thumbnail, $to_path, $quality);
					}
					break;
				case 'image/webp':
					if ($high_resolution) {
						if (imagewebp($this->thumbnail_high_res, $to_path_high_res, $quality)) {

							imagealphablending($this->thumbnail, false); // true?
							imagesavealpha($this->thumbnail, true);

							if (imagecopyresampled($this->thumbnail, $this->thumbnail_high_res, 0, 0, 0, 0, $thumbnail_width / 2, $thumbnail_height / 2, $thumbnail_width, $thumbnail_height)) {
								$creation_success = imagewebp($this->thumbnail, $to_path, $quality);
							}
						}
					} else {
						$creation_success = imagewebp($this->thumbnail, $to_path, $quality);
					}
					break;
			}
		}

		if ($creation_success) {
			$this->thumbnail_path = $to_path;
			$this->thumbnail_width = $thumbnail_width;
			$this->thumbnail_height = $thumbnail_height;
			if ($high_resolution) {
				$this->thumbnail_high_res_path = $to_path_high_res;
				$this->thumbnail_high_res_width = $thumbnail_width;
				$this->thumbnail_high_res_height = $thumbnail_height;
				$this->thumbnail_width = $thumbnail_width / 2;
				$this->thumbnail_height = $thumbnail_height / 2;
			}
		} else {
			if (is_resource($this->thumbnail)) {
				imagedestroy($this->thumbnail);
			}

			if ($high_resolution && is_resource($this->thumbnail_high_res)) {
				imagedestroy($this->thumbnail_high_res);
			}
		}

		return $creation_success;
	}

	/**
	 * Is the image transparent ?
	 *
	 * @return boolean
	 */
	public function isTransparent()
	{
		return $this->is_image_transparent;
	}

	/**
	 * Stores the original value of the server's memory limit
	 */
	private function set_memory_limit()
	{
		$this->memory_limit = ini_get('memory_limit');
	}

	/**
	 * Temporarily increases the servers memory limit to 2480 MB to handle building larger images
	 *
	 * @param string $new_limit
	 */
	private function increase_memory_limit($new_limit = '256M')
	{
		ini_set('memory_limit', $new_limit);
	}

	/**
	 * Resets the servers memory limit to its original value
	 */
	private function reset_memory_limit()
	{
		if ($this->memory_limit > 0) {
			ini_set('memory_limit', $this->memory_limit);
		}
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

		$this->reset_memory_limit();
	}

	public function __destruct()
	{
		$this->destroy();
	}

}
?>