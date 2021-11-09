<?php
/* This file has been prefixed by <PHP-Prefixer> for "PHP-Prefixer Getting Started" */

namespace SYW\Library\Image;

abstract class AbstractImageLibrary
{
    /**
     * Creates new image instance
     *
	 * @param string $path
	 * @param number $width
	 * @param number $height
     * @return Image
     */
    abstract public function createImageFromPath($mime_type, $path = '', $width = 0, $height = 0);
    
    abstract public function createImageFromData($mime_type, $image_string, $width = 0, $height = 0);
    
    /*
     * 
     */
    abstract public function createThumbnail($mime_type, $image, $to_path, $target_origin_x = 0, $target_origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0, $quality = 75, $filter = null);

    abstract public function createFile($mime_type, $image, $path, $quality = 75, $filter = null);
    
    abstract public function createEncodedString($mime_type, $image, $quality = 75, $filter = null);
    
    /**
     * Returns name of current driver instance
     *
     * @return string
     */
    abstract public function getDriverName();
    
    /**
     * Returns whether the library is available
     */
    abstract public function isAvailable();
    
    abstract public function getImageWidth($image);
    
    abstract public function getImageHeight($image);
    
    abstract public function isTransparent($mime_type, $image);
    
    abstract public function rotate(&$image, $orientation_angle);
    
    abstract public function destroy(&$image);
    
    abstract public function getLibraryName();
}
