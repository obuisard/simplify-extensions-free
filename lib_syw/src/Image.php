<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use SYW\Library\Plugin as SYWPLugin;

/**
 * Management of an image resource
 * Based on PHP GD extension
 * Image support for .gif, .jpg, .jpeg, .png, .webp, .avif
 *
 * @author Olivier Buisard
 *
 */
class Image
{
    /**
     * library used to create images
     */
    protected $image_library;
    
	/*
	 * image resource
	 */
	protected $image = null;

	/*
	 * image path
	 */
	protected $image_path = null;
	protected $image_path_as_data = false;

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
	 * supported mime type
	 */
	protected $supported_mime_types = array('gif', 'jpg', 'jpeg', 'png', 'webp', 'avif');

	/*
	 * The current memory limit
	 */
	private $memory_limit = -1;

	/*
	 * The memory limit set on the server
	 */
	private $initial_memory_limit = -1;
	
	/**
	 * Image resource creation
	 *
	 * @param string $from_path
	 * @param number $width
	 * @param number $height
	 * @param boolean|string $increase_memory_limit (ex: '256M')
	 */
	public function __construct($from_path = '', $width = 0, $height = 0, $increase_memory_limit = false)
	{
	    Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));
	    
	    try {
	        
	        $library_name = ucfirst(strtolower(SYWPLugin::getImageLibrary()));
	        $library_class = sprintf('SYW\\Library\\Image\\%sLibrary', $library_name);
	        
	        if (!class_exists($library_class)) {
	            throw new \RuntimeException('Could not instantiate image library class');
	        }
	        
	        $this->image_library = new $library_class; // will raise error if library is not available
	        
	        $this->set_initial_memory_limit();
	        if (is_bool($increase_memory_limit) && $increase_memory_limit) {
	            $this->increase_memory_limit();
	        } else if (is_string($increase_memory_limit)) {
	            $this->increase_memory_limit($increase_memory_limit);
	        }
	        
	        $this->image = $this->setImage($from_path, $width, $height);
	        if (!$this->image) {
	            throw new \RuntimeException('Could not create image');
	        }
	        
	        if ($this->image) {
	            
	            if (!$this->image_path_as_data && !empty($this->image_path)) {
	                $orientation_angle = $this->getOrientationAngleFix($this->image_path);
	                
	                if ($orientation_angle > 0) {
	                    $this->image_library->rotate($this->image, $orientation_angle);
	                }
	            }
	            
	            $this->is_image_transparent = $this->image_library->isTransparent($this->image_mimetype, $this->image);
	            $this->image_width = $width > 0 ? $width : $this->image_library->getImageWidth($this->image);
	            $this->image_height = $height > 0 ? $height : $this->image_library->getImageHeight($this->image);
	        }
	    } catch (\RuntimeException $e) {
	        $this->image = null;
	        Log::add('Image:construct() - ' . $e->getMessage(), Log::ERROR, 'syw');
	    }
	}
	
	/**
	 * Returns the current image library
	 */
	public function getImageLibrary()
	{
	    return $this->image_library;
	}

	/**
	 * Sets current image library
	 * 
	 * @param string library 
	 */
	public function setImageLibrary($library)
	{
	    $library_name = ucfirst(strtolower($library));
	    $library_class = sprintf('SYW\\Library\\Image\\%sLibrary', $library_name);
	    
	    if (!class_exists($library_class)) {
	        throw new \RuntimeException('Could not instantiate image library class');
	    }
	    
	    $this->image_library = new $library_class;
	}

	/**
	 * Get the image resource
	 * @return NULL|resource|object
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
	
	protected function setImage($from_path = '', $width = 0, $height = 0)
	{
	    if ($from_path && $width > 0 && $height > 0) { // create image with the required dimensions
	        
	        if (substr($from_path, 0, 4) === 'data') {
	            
	            $data_array = explode(';', $from_path);
	            
	            $this->image_mimetype = str_replace('data:', '', $data_array[0]);
	            $this->image_path = $from_path;
	            $this->image_path_as_data = true;
	            
	            $image_string = str_replace('base64,', '', $data_array[1]);
	            
	            return $this->image_library->createImageFromData($this->image_mimetype, base64_decode($image_string), $width, $height);	            
	        }
	            
            // allow image file names with spaces
            $from_path = str_replace('%20', ' ', $from_path);
            
            // check if $from_path is url, make sure it goes thru
            if (substr_count($from_path, 'http') > 0) {
                
                // HTTPS is only supported when the openssl extension is enabled
                // in order to minimize errors, we can replace the https:// with http://
                $from_path = str_replace('https://', 'http://', $from_path);
                
                if (!$this->file_is_valid($from_path)) {
                    return null;
                }
                
                $this->image_path_remote = true;
            }            
            
            $mime_type = $this->get_image_mime_type($from_path);
            if (!$mime_type) {
                return null;
            }
            
            $this->image_mimetype = $mime_type;
            $this->image_path = $from_path;
                
            return $this->image_library->createImageFromPath($this->image_mimetype, $from_path, $width, $height);
	        
	    } elseif ($from_path) { // create image with dimensions of imported picture
	        
	        if (substr($from_path, 0, 4) === 'data') {
	            
	            $data_array = explode(';', $from_path);
	            
	            $this->image_mimetype = str_replace('data:', '', $data_array[0]);
	            $this->image_path = $from_path;
	            $this->image_path_as_data = true;
	            
	            $image_string = str_replace('base64,', '', $data_array[1]);
	            
	            return $this->image_library->createImageFromData($this->image_mimetype, base64_decode($image_string));	            
	        }
	        
            // allow image file names with spaces
            $from_path = str_replace('%20', ' ', $from_path);
            
            // check if $from_path is url, make sure it goes thru
            if (substr_count($from_path, 'http') > 0) {
                
                // HTTPS is only supported when the openssl extension is enabled
                // in order to minimize errors, we can replace the https:// with http://
                $from_path = str_replace('https://', 'http://', $from_path);
                
                if (!$this->file_is_valid($from_path)) {
                    $this->image =  null;
                }
                
                $this->image_path_remote = true;
            }
            
            $mime_type = $this->get_image_mime_type($from_path);
            if (!$mime_type) {
                return null;
            }
            
            $this->image_mimetype = $mime_type;
            $this->image_path = $from_path;
                
            return $this->image_library->createImageFromPath($this->image_mimetype, $from_path);
            
	    } elseif (empty($from_path) && $width > 0 && $height > 0) { // create blank image with required dimensions
	        
	        return $this->image_library->createImageFromPath(null, '', $width, $height);	        
	    }
	        
	    return null;
	}	

	/*
	 * Get the orientation of the image to fix it if necessary (for instance, after import from a mobile device)
	 */
	protected static function getOrientationAngleFix($filename)
	{		
		$angle = 0;
		
		if (function_exists('exif_read_data')) {
			
			$exif = @exif_read_data($filename);
			
			if ($exif && isset($exif['Orientation'])) {
									
				switch ($exif['Orientation']) 
				{						
					case 3: // 180 rotate left
						$angle = 180;					
						break;
					
					case 6: // 270 rotate left
						$angle = 270;
						break;
						
					case 8: // 90 rotate left
						$angle = 90;
						break;
				}
			}
		}
		
		return $angle;
	}
	
	/**
	 * Check if a file is valid
	 * 
	 * @return boolean
	 */
	protected function file_is_valid($path) 
	{
	    $is_valid = true;
	    
	    if (function_exists('curl_version')) {
	        
	        $ch = curl_init();
	        
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_URL, $path);
	        curl_setopt($ch, CURLOPT_HEADER, true);
	        //curl_setopt($ch, CURLOPT_NOBODY, true); // may force the site to return 400 rather than 200!
	        
	        if (@curl_exec($ch) !== false) {
	            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
	                $is_valid = false;
	            }
	        }
	        
	        curl_close($ch);
	        
	    } else {
	        
	        $file_headers = @get_headers($path); // needs allow_url_fopen enabled
	        
	        if (!$file_headers || strpos($file_headers[0], '200') === false) {
	            $is_valid = false;
	        }
	    }
	    
	    return $is_valid;
	}
	
	/**
	 * Get the file format type from the mime type
	 */
	protected function mimetype_to_fileformat($mime_type)
	{
	    $fileformat = null;
	    
	    switch (strtolower($mime_type)) 
	    {
	        case 'image/gif': $fileformat = 'gif' ; break;
	        case 'image/jpeg': $fileformat = 'jpg'; break;
	        case 'image/png': $fileformat = 'png'; break;
	        case 'image/webp': $fileformat = 'webp'; break;
	        case 'image/avif': $fileformat = 'avif'; 
	    }
	    
	    return $fileformat;
	}
	
	/**
	 * Get the mime type from the file format
	 */
	protected function fileformat_to_mimetype($file_format)
	{
	    $mime_type = null;
	    
	    switch (strtolower($file_format)) 
	    {
	        case 'gif': $mime_type = 'image/gif' ; break;
	        case 'jpg': case 'jpeg': $mime_type = 'image/jpeg'; break;
	        case 'png': $mime_type = 'image/png'; break;
	        case 'webp': $mime_type = 'image/webp'; break;
	        case 'avif': $mime_type = 'image/avif'; 
	    }
	    
	    return $mime_type;
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

		if (!in_array(strtolower($extension), $this->supported_mime_types)) {
			return $mime_type;
		}

		if (function_exists('exif_imagetype')) {
			$image_type = @exif_imagetype($path); // WebP support in PHP 7.1.0. No support for Avif
			if ($image_type) {
				return image_type_to_mime_type($image_type);
			}
		}

		if (function_exists('mime_content_type')) {
			$file_type = strtolower(@mime_content_type($path));

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

		// curl (works with external files) - only reads the header
		
		if (function_exists('curl_version')) {
		    
		    $ch = curl_init();
		    
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_URL, $path);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		    curl_setopt($ch, CURLOPT_HEADER, true);
		    curl_setopt($ch, CURLOPT_NOBODY, true);
		    
		    if (@curl_exec($ch) !== false) {
		        $file_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		        if (substr($file_type, 0, 6) === 'image/') {
		            curl_close($ch);
		            return $file_type; // TODO? make sure there is no ; xxx after the mime type
		        }
		    }
		    
		    curl_close($ch);
		}

		// last resort
		
		// getimagesize reads the whole file, slowest method
		// getimagesize needs allow_url_fopen for http images and open_ssl for https images
		$image_info = @getimagesize($path); // WebP support in PHP 7.1.0. No support for Avif
		if ($image_info) {
		    return $image_info['mime'];
		}

		return $mime_type;
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
		
		if (is_null($this->image)) {
		    return $creation_success;
		}

		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		// if to_path extension is missing, use the file extension corresponding to the mime type
		$path_array = explode('.', $to_path);
		if (count($path_array) == 1) {
		    
		    $file_format = $this->mimetype_to_fileformat($mime_type);
		    if (empty($file_format)) {
		        return $creation_success;
		    }
		    $to_path .= '.' . $file_format;
		} else {
			// the file extension has priority over the mime type in case the file extension and the mime type do not correspond
			
		    $mime_type = $this->fileformat_to_mimetype(end($path_array));
		    if (empty($mime_type)) {
		        return $creation_success;
		    }
		}
		
		$creation_success = $this->image_library->createFile($mime_type, $this->image, $to_path, $quality, $filter);

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
	    if (is_null($this->image)) {
	        return null;
	    }
	    
		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		$raw_stream = $this->image_library->createEncodedString($mime_type, $this->image, $quality, $filter);

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
	    
	    if (is_null($this->image)) {
	        return $creation_success;
	    }

		$mime_type = $to_type ? $to_type : $this->image_mimetype;

		// if to_path extension is missing, use the file extension corresponding to the mime type
		$path_array = explode('.', $to_path);
		if (count($path_array) == 1) {
		    
		    $file_format = $this->mimetype_to_fileformat($mime_type);
		    if (empty($file_format)) {
		        return $creation_success;
		    }
		    $to_path .= '.' . $file_format;
		} else {
			// the file extension has priority over the mime type in case the file extension and the mime type do not correspond
			
			$mime_type = $this->fileformat_to_mimetype(end($path_array));
			if (empty($mime_type)) {
			    return $creation_success;
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
		
		if ($high_resolution) {
			$this->thumbnail_high_res = $this->image_library->createThumbnail($mime_type, $this->image, $to_path_high_res, 0, 0, (int) $x, (int) $y, (int) $thumbnail_width, (int) $thumbnail_height, (int) $w, (int) $h, $quality, $filter);
			$this->thumbnail = $this->image_library->createThumbnail($mime_type, $this->image, $to_path, 0, 0, (int) $x, (int) $y, intval($thumbnail_width / 2), intval($thumbnail_height / 2), (int) $w, (int) $h, $quality, $filter);
		    
		    if ($this->thumbnail !== false && $this->thumbnail_high_res !== false) {
		        $creation_success = true;
		    }
		} else {		
			$this->thumbnail = $this->image_library->createThumbnail($mime_type, $this->image, $to_path, 0, 0, (int) $x, (int) $y, (int) $thumbnail_width, (int) $thumbnail_height, (int) $w, (int) $h, $quality, $filter);
		    
		    if ($this->thumbnail !== false) {
		        $creation_success = true;
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
				$this->thumbnail_width = intval($thumbnail_width / 2);
				$this->thumbnail_height = intval($thumbnail_height / 2);
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
	private function set_initial_memory_limit()
	{
		$this->initial_memory_limit = ini_get('memory_limit');
		$this->memory_limit = $this->initial_memory_limit;
	}

	/**
	 * Temporarily increases the servers memory limit to 2480 MB to handle building larger images
	 *
	 * @param string $new_limit
	 */
	private function increase_memory_limit($new_limit = '256M')
	{
		$result = ini_set('memory_limit', $new_limit); // may be prevented by the server
		if ($result !== false) {
			$this->memory_limit = $new_limit;
		}
	}

	/**
	 * Resets the servers memory limit to its original value
	 */
	private function reset_memory_limit()
	{
		$this->memory_limit = $this->initial_memory_limit;
		ini_set('memory_limit', $this->initial_memory_limit);
	}

	/**
	 * Set the new memory limit
	 *
	 * @param String $limit (ex: '256M)
	 */
	public function setMemoryLimit(String $new_limit)
	{
		$this->increase_memory_limit($new_limit);
	}

	/**
	 * Returns the memory allocated by the server
	 *
	 * @return number|string
	 */
	public function getMemoryLimit()
	{
		return $this->memory_limit;
	}

	public function destroy()
	{
	    if (isset($this->thumbnail)) {
	        $this->image_library->destroy($this->thumbnail);
	    }
	    
	    if (isset($this->thumbnail_high_res)) {
	        $this->image_library->destroy($this->thumbnail_high_res);
	    }
	    
	    if (isset($this->image)) {
	        $this->image_library->destroy($this->image);
	    }

		$this->reset_memory_limit();
	}

	public function __destruct()
	{
		$this->destroy();
	}

}
?>