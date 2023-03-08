<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Image;

defined('_JEXEC') or die;

abstract class AbstractImageLibrary
{
    /**
     * Creates new image instance
     *
	 * @param string $path
	 * @param number $width
	 * @param number $height
     * @return resource|\stdClass
     */
    abstract public function createImageFromPath($mime_type, $path = '', $width = 0, $height = 0);
    
    /**
     * 
     * @param string $mime_type
     * @param string $image_string
     * @param number $width
     * @param number $height
     * @return resource|\stdClass
     */
    abstract public function createImageFromData($mime_type, $image_string, $width = 0, $height = 0);
    
    /**
     * 
     * @param string $mime_type
     * @param resource|\stdClass $image
     * @param string $to_path
     * @param number $target_origin_x
     * @param number $target_origin_y
     * @param number $source_origin_x
     * @param number $source_origin_y
     * @param number $target_width
     * @param number $target_height
     * @param number $source_width
     * @param number $source_height
     * @param number $quality
     * @param integer|array $filter
     * @return resource|\stdClass
     */
    abstract public function createThumbnail($mime_type, $image, $to_path, $target_origin_x = 0, $target_origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0, $quality = 75, $filter = null);

    /**
     * 
     * @param string $mime_type
     * @param resource|\stdClass $image
     * @param string $path
     * @param number $quality
     * @param integer|array $filter
     */
    abstract public function createFile($mime_type, $image, $path, $quality = 75, $filter = null);
    
    /**
     *
     * @param string $mime_type
     * @param resource|\stdClass $image
     * @param number $quality
     * @param integer|array $filter
     */
    abstract public function createEncodedString($mime_type, $image, $quality = 75, $filter = null);
       
    /**
     * Whether the library is present or not
     * 
     * @return boolean
     */
    abstract public function isAvailable();
    
    /**
     * The image width
     * 
     * @param resource|\stdClass $image
     * @return int
     */
    abstract public function getImageWidth($image);
    
    /**
     * The image height
     * 
     * @param resource|\stdClass $image
     * @return int
     */
    abstract public function getImageHeight($image);
    
    /**
     * Whether the image is transparent
     * 
     * @param string $mime_type
     * @param resource|\stdClass $image
     * @return integer|boolean
     */
    abstract public function isTransparent($mime_type, $image);
    
    /**
     * Rotate the image 
     * 
     * @param resource|\stdClass $image
     * @param float $orientation_angle
     */
    abstract public function rotate(&$image, $orientation_angle);
    
    /**
     * Remove the image object from memory
     * 
     * @param resource|\stdClass $image
     */
    abstract public function destroy(&$image);
    
    /**
     * The used library name
     * 
     * @return string
     */
    abstract public function getLibraryName();
}
