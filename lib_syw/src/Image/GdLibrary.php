<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Image;

defined('_JEXEC') or die;

class GdLibrary extends AbstractImageLibrary
{
    /**
     * Creates a new instance of the library
     */
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('GD extension not available');
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createImageFromPath()
     */
    public function createImageFromPath($mime_type, $path = '', $width = 0, $height = 0)
    {
        $image = false;
        
        if (empty($path)) {
            
            $image = @imagecreatetruecolor($width, $height);
            
        } else {
        
            switch (strtolower($mime_type))
            {
                case 'image/gif': $image =  @imagecreatefromgif($path); break;
                case 'image/jpeg': $image =  @imagecreatefromjpeg($path); break;
                case 'image/png': $image =  @imagecreatefrompng($path); break;
                case 'image/webp':
                    if (function_exists('imagewebp')) {
                        $image =  @imagecreatefromwebp($path);
                    }
                    break;
                case 'image/avif':
                    if (function_exists('imageavif')) {
                        $image =  @imagecreatefromavif($path);
                    }
                    break;
                default: // unsupported type
            }
            
            if ($image !== false && $width > 0 && $height > 0) {
                
                $source_width = $this->getImageWidth($image);
                $source_height = $this->getImageHeight($image);
                
                // crop only if necessary
                if ($source_width !== $width || $source_height !== $height) {
                    
                    $source_image = $image;
                    
                    $ratio = max($width/$source_width, $height/$source_height);
                    $w = $width / $ratio;
                    $h = $height / $ratio;
                    $x = ($source_width - $width / $ratio) / 2;
                    $y = ($source_height - $height / $ratio) / 2;
                    
                    $image = @imagecreatetruecolor($width, $height);
                    if ($image !== false) {                    
                    	$this->crop_and_resize($mime_type, $image, $source_image, 0, 0, (int) $x, (int) $y, $width, $height, (int) $w, (int) $h);
                    }
                    
                    unset($source_image);
                }
            }
        }
        
        return $image;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createImageFromData()
     */
    public function createImageFromData($mime_type, $image_string, $width = 0, $height = 0)
    {
        $image = @imagecreatefromstring($image_string); // no support for WebP nor for Avif
        
        if ($image !== false && $width > 0 && $height > 0) {
            
            $source_image = $image;
            
            $source_width = $this->getImageWidth($image);
            $source_height = $this->getImageHeight($image);
            
            // crop only if necessary
            if ($source_width !== $width || $source_height !== $height) {
                
                $ratio = max($width/$source_width, $height/$source_height);
                $w = $width / $ratio;
                $h = $height / $ratio;
                $x = ($source_width - $width / $ratio) / 2;
                $y = ($source_height - $height / $ratio) / 2;
                
                $image = @imagecreatetruecolor($width, $height);
                if ($image !== false) {
                	$this->crop_and_resize($mime_type, $image, $source_image, 0, 0, (int) $x, (int) $y, $width, $height, (int) $w, (int) $h);
                }
            }
            
            unset($source_image);
        }
        
        return $image;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createThumbnail()
     */
    public function createThumbnail($mime_type, $image, $path, $target_origin_x = 0, $target_origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0, $quality = 75, $filter = null)
    {        
        $thumbnail = @imagecreatetruecolor($target_width, $target_height);
        if ($thumbnail !== false) {
            
            $creation_success = false;
            
            $this->crop_and_resize($mime_type, $thumbnail, $image, $target_origin_x, $target_origin_y, $source_origin_x, $source_origin_y, $target_width, $target_height, $source_width, $source_height);
            
            if (!is_null($filter)) {
                $this->apply_filters($thumbnail, $filter);
            }
            
            switch (strtolower($mime_type)) 
            {
                case 'image/gif':
                    $creation_success = imagegif($thumbnail, $path);
                    break;
                case 'image/jpeg':
                    $creation_success = imagejpeg($thumbnail, $path, $quality);
                    break;
                case 'image/png':                    
                    $quality = ($quality - 100) / 11.111111;
                    $quality = round(abs($quality));
                    $creation_success = imagepng($thumbnail, $path, $quality);
                    break;
                case 'image/webp':
                    if (function_exists('imagewebp')) {
                        $creation_success = imagewebp($thumbnail, $path, $quality);
                    }
                    break;
                case 'image/avif':
                    if (function_exists('imageavif')) {
                        $creation_success = imageavif($thumbnail, $path, $quality);
                    }
            }
            
            if (!$creation_success) {
                return false;
            }
        }
        
        return $thumbnail;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createFile()
     */
    public function createFile($mime_type, $image, $path, $quality = 75, $filter = null)
    {
        if (!is_null($filter)) {
            $this->apply_filters($image, $filter);
        }
        
        $creation_success = false;
        
        switch (strtolower($mime_type)) 
        {
            case 'image/gif': 
                $creation_success = imagegif($image, $path); 
                break;
            case 'image/jpeg': 
                $creation_success = imagejpeg($image, $path, $quality); 
                break;
            case 'image/png':
                $quality = ($quality - 100) / 11.111111;
                $quality = round(abs($quality));
                $creation_success = imagepng($image, $path, $quality);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    $creation_success = imagewebp($image, $path, $quality);
                }
                break;
            case 'image/avif':
                if (function_exists('imageavif')) {
                    $creation_success = imageavif($image, $path, $quality);
                }
        }
        
        return $creation_success;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createFile()
     */
    public function createEncodedString($mime_type, $image, $quality = 75, $filter = null)
    {
        if (!is_null($filter)) {
            $this->apply_filters($image, $filter);
        }
        
        ob_start();
        
        switch ($mime_type) {
            case 'image/gif': 
                imagegif($image); 
                break;
            case 'image/jpeg': 
                imagejpeg($image, null, $quality); 
                break;
            case 'image/png':
                $quality = ($quality - 100) / 11.111111;
                $quality = round(abs($quality));
                imagepng($image, null, $quality);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($image, null, $quality);
                }
                break;
            case 'image/avif':
                if (function_exists('imageavif')) {
                    imageavif($image, null, $quality);
                }
        }
        
        $raw_stream = ob_get_contents(); // read from buffer
        
        ob_end_clean();
        
        return $raw_stream;
    }
    
    /**
     * Copy a resource source into a resource target with specific dimensions
     *
     * @param resource $source
     * @param number $target_origin_x
     * @param number $target_origin_y
     * @param number $source_origin_x
     * @param number $source_origin_y
     * @param number $target_width
     * @param number $target_height
     * @param number $source_width
     * @param number $source_height
     * @return resource
     */
    protected function crop_and_resize($mime_type, &$target, $source, $target_origin_x = 0, $target_origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0)
    {
        if ($mime_type === 'image/gif') {
            
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
            
            imagecopyresized($target, $source, $target_origin_x, $target_origin_y, $source_origin_x, $source_origin_y, $target_width, $target_height, $source_width, $source_height);
        } else {
            
            if ($mime_type !== 'image/jpeg') { // no transparency in jpegs
                
                imagecolortransparent($target, imagecolorallocatealpha($target, 0, 0, 0, 127)); // all transparent
                imagealphablending($target, false); // turn off blending to keep alpha channel from originial
                imagesavealpha($target, true); // keep alpha info for PNG (WebP ?)
            }
            
            imagecopyresampled($target, $source, $target_origin_x, $target_origin_y, $source_origin_x, $source_origin_y, $target_width, $target_height, $source_width, $source_height);
        }
    }
    
    /**
     * Apply GD filters to an image
     *
     * @param resource|\GdImage $image
     * @param integer|array $filter
     */
    protected function apply_filters(&$image, $filter)
    {
        if (function_exists('imagefilter')) { // make sure there is imagefilter support in PHP
            if (is_array($filter)) {
                foreach($filter as $f) { // allow multiple filters
                    if (is_array($f)) { // old way ex: array('type' => IMG_FILTER_COLORIZE, 'arg1' => 70, 'arg2' => 35, 'arg3' => 0)
                        extract($f);                        
                        if (is_int($type)) {
                            if (!isset($arg1)) {
                                imagefilter($image, $type);
                            } elseif (!isset($arg2)) {
                                imagefilter($image, $type, $arg1);
                            } elseif (!isset($arg3)) {
                                imagefilter($image, $type, $arg1, $arg2);
                            } elseif (!isset($arg4)) {
                                imagefilter($image, $type, $arg1, $arg2, $arg3);
                            } else {
                                imagefilter($image, $type, $arg1, $arg2, $arg3, $arg4);
                            }
                        }
                        unset($type); unset($arg1); unset($arg2); unset($arg3); unset($arg4);
                    } elseif (is_int($f)) {
                        imagefilter($image, $f);
                    } else {
                        $this->filter($image, $f);
                    }
                }                
            } elseif (is_int($filter)) {
                imagefilter($image, $filter);
            } else {
                $this->filter($image, $filter);
            }
        } else {
            //Log::add('SYWImage:createThumbnail() - The imagefilter function for PHP is not available', Log::ERROR, 'syw');
        }
    }
    
    /**
     * 
     * @param resource|\GdImage $image
     * @param string $filter
     */
    protected function filter(&$image, $filter)
    {
        switch ($filter)
        {
            case 'sepia':
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 30);
                break;
            case 'grayscale': imagefilter($image, IMG_FILTER_GRAYSCALE); break;
            case 'sketch': imagefilter($image, IMG_FILTER_MEAN_REMOVAL); break;
            case 'negate': imagefilter($image, IMG_FILTER_NEGATE); break;
            case 'emboss': imagefilter($image, IMG_FILTER_EMBOSS); break;
            case 'edgedetect': imagefilter($image, IMG_FILTER_EDGEDETECT); break;
            case 'blur': imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR); break;
            case 'sharpen': imagefilter($image, IMG_FILTER_SMOOTH, -9); break;
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::isAvailable()
     */
    public function isAvailable()
    {
        return (extension_loaded('gd') && function_exists('gd_info'));
    }    
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getImageWidth()
     */
    public function getImageWidth($image)
    {
        return imagesx($image);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getImageHeight()
     */
    public function getImageHeight($image)
    {
        return imagesy($image);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::isTransparent()
     */
    public function isTransparent($mime_type, $image)
    {
        if (!empty($mime_type) && $mime_type !== 'image/jpeg') {
            return (imagecolortransparent($image) >= 0) ? true : false; // ONLY works for gif files
        }
        
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::rotate()
     */
    public function rotate(&$image, $orientation_angle)
    {
        $image = imagerotate($image, $orientation_angle, 0);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::destroy()
     */
    public function destroy(&$image)
    {
        if (isset($image)) {
            if (is_resource($image) || (is_object($image) && $image instanceOf \GdImage)) {
                imagedestroy($image);
                unset($image);
            }
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getLibraryName()
     */
    public function getLibraryName()
    {
        return 'gd';
    }
}
