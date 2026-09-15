<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Image;

defined('_JEXEC') or die;

class ImagickLibrary extends AbstractImageLibrary
{
    /**
     * Creates a new instance of the library
     */
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Imagick extension not available');
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::createImageFromPath()
     */
    public function createImageFromPath($mime_type, $path = '', $width = 0, $height = 0)
    {
        $image = new \Imagick();
        
        if (empty($path)) {
            
            $image->newImage($width, $height, new \ImagickPixel('none'));
            
        } else {
        
            try {
                if (strpos($path, 'http') !== false) {
//                     $handle = fopen($path, 'rb'); // needs allow_url_fopen
//                     if (!$handle) {
//                         return false;
//                     }
//                     $image->readImageFile($handle);
                    $image_content = @file_get_contents($path); // needs allow_url_fopen
                    if ($image_content === false) {
                        return false;
                    }                    
                    $image->readImageBlob($image_content);
                    //$image->readImage($path); // works but is waaaaayyy too slow
                } else {
                    $image->readImage(JPATH_ROOT . '/' .$path); // internal image
                }
            } catch (\ImagickException $e) {
                return false;
            }
            
            if ($image !== false && $width > 0 && $height > 0) {
                
                $source_width = $this->getImageWidth($image);
                $source_height = $this->getImageHeight($image);
                
                // crop only if necessary
                if ($source_width !== $width || $source_height !== $height) {
                    
                    $ratio = max($width/$source_width, $height/$source_height);
                    $w = $width / $ratio;
                    $h = $height / $ratio;
                    $x = ($source_width - $width / $ratio) / 2;
                    $y = ($source_height - $height / $ratio) / 2;
                        
                    $this->crop_and_resize($image, (int) $x, (int) $y, $width, $height, (int) $w, (int) $h);
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
        $image = new \Imagick();
        
        try {
            $image->readImageBlob($image_string);
        } catch (\ImagickException $e) {
            return false;
        }
        
        if ($image !== false && $width > 0 && $height > 0) {
            
            $source_width = $this->getImageWidth($image);
            $source_height = $this->getImageHeight($image);
            
            // crop only if necessary
            if ($source_width !== $width || $source_height !== $height) {
                
                $ratio = max($width/$source_width, $height/$source_height);
                $w = $width / $ratio;
                $h = $height / $ratio;
                $x = ($source_width - $width / $ratio) / 2;
                $y = ($source_height - $height / $ratio) / 2;
                
                $this->crop_and_resize($image, (int) $x, (int) $y, $width, $height, (int) $w, (int) $h);
            }
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
        $thumbnail = clone $image;
        
        $this->crop_and_resize($thumbnail, $source_origin_x, $source_origin_y, $target_width, $target_height, $source_width, $source_height);
        
        if (!is_null($filter)) {
            $this->apply_filters($thumbnail, $filter);
        }
        
        switch (strtolower($mime_type))
        {
            case 'image/gif':
                $thumbnail->setImageCompression(\Imagick::COMPRESSION_LZW);
                break;
            case 'image/jpeg':
                $thumbnail->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $thumbnail->setImageCompressionQuality($quality);
                break;
            case 'image/png':
                $quality = ($quality - 100) / 11.111111;
                $quality = round(abs($quality));                
                $thumbnail->setImageCompression(\Imagick::COMPRESSION_ZIP);
                $thumbnail->setOption('png:compression-level', $quality);                
                break;
            case 'image/webp':
                if (\Imagick::queryFormats('WEBP')) {
                    $thumbnail->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $thumbnail->setImageCompressionQuality($quality);
                }
                break;
            case 'image/avif':
                if (\Imagick::queryFormats('AVIF')) {
                    $thumbnail->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
                    $thumbnail->setImageCompressionQuality($quality);
                }
        }
        
        $thumbnail->stripImage(); // Strip out unneeded meta data
        
        $thumbnail->writeImage(JPATH_ROOT . '/' .$path);
        
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
        
        switch (strtolower($mime_type))
        {
            case 'image/gif':
                $image->setImageCompression(\Imagick::COMPRESSION_LZW);
                break;
            case 'image/jpeg':
                $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $image->setImageCompressionQuality($quality);
                break;
            case 'image/png':
                $quality = ($quality - 100) / 11.111111;
                $quality = round(abs($quality));
                $image->setImageCompression(\Imagick::COMPRESSION_ZIP);
                $image->setOption('png:compression-level', $quality);
                break;
            case 'image/webp':
                if (\Imagick::queryFormats('WEBP')) {
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality($quality);
                }
                break;
            case 'image/avif':
                if (\Imagick::queryFormats('AVIF')) {
                    $image->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
                    $image->setImageCompressionQuality($quality);
                }
        }
        
        return $image->writeImage(JPATH_ROOT . '/' .$path);
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
        
        switch (strtolower($mime_type))
        {
            case 'image/gif':
                $image->setImageCompression(\Imagick::COMPRESSION_LZW);
                break;
            case 'image/jpeg':
                $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $image->setImageCompressionQuality($quality);
                break;
            case 'image/png':
                $quality = ($quality - 100) / 11.111111;
                $quality = round(abs($quality));
                $image->setImageCompression(\Imagick::COMPRESSION_ZIP);
                $image->setOption('png:compression-level', $quality);
                break;
            case 'image/webp':
                if (\Imagick::queryFormats('WEBP')) {
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality($quality);
                }
                break;
            case 'image/avif':
                if (\Imagick::queryFormats('AVIF')) {
                    $image->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
                    $image->setImageCompressionQuality($quality);
                }
        }
        
        return $image->getImageBlob();
    }
    
    /**
     * 
     * @param \Imagick $image
     * @param number $source_origin_x
     * @param number $source_origin_y
     * @param number $target_width
     * @param number $target_height
     * @param number $source_width
     * @param number $source_height
     */
    protected function crop_and_resize(&$image, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0)
    {
        $image->cropImage($source_width, $source_height, $source_origin_x, $source_origin_y);
        
        //$image->scaleImage($target_width, $target_height);
        //$image->adaptiveResizeImage($target_width, $target_height);
        
        $image->resizeImage($target_width, $target_height, \Imagick::FILTER_LANCZOS, 1);
        //$image->thumbnailImage($target_width, $target_height, false, true); // produces bigger pngs
    }
    
    /**
     * Apply filters to an image
     *
     * @param \Imagick $image
     * @param integer|array $filter
     */
    protected function apply_filters(&$image, $filter)
    {
        if (is_array($filter)) {
            foreach($filter as $f) { // allow multiple filters
                $this->filter($image, $f);
            }
        } else {
            $this->filter($image, $filter);
        }
    }
    
    /**
     *
     * @param \Imagick $image
     * @param string $filter
     */
    protected function filter(&$image, $filter)
    {
        try {
            switch ($filter)
            {
                case 'sepia': $image->sepiaToneImage(75); break;
                case 'grayscale': ;
                    //$image->setImageType(\Imagick::IMGTYPE_GRAYSCALEMATTE);
                    $image->modulateImage(100,0,100);
                    break;
                case 'sketch': ;
                    if (function_exists('sketchImage')) {
                        $image->sketchImage(5, 4, 45); // not great
                    }
                    break;
                case 'negate': ; $image->negateImage(false); break;
                case 'emboss': ; $image->embossImage(0, 1); break;
                case 'edgedetect': $image->edgeImage(0); break;
                case 'blur': $image->gaussianBlurImage(0, 4); break;
                case 'sharpen': $image->sharpenImage(0, 4); break;
            }
        } catch (\ImagickException $e) {
            //
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::isAvailable()
     */
    public function isAvailable()
    {
        return (extension_loaded('imagick') && class_exists('Imagick'));
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getImageWidth()
     */
    public function getImageWidth($image)
    {
        return $image->getImageWidth();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getImageHeight()
     */
    public function getImageHeight($image)
    {
        return $image->getImageHeight();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::isTransparent()
     */
    public function isTransparent($mime_type, $image)
    {
        return $image->getImageAlphaChannel();
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::rotate()
     */
    public function rotate(&$image, $orientation_angle)
    {
        $image->rotateimage(new \ImagickPixel('none'), intval(360 - $orientation_angle));
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::destroy()
     */
    public function destroy(&$image)
    {
        if (isset($image) && is_object($image) && $image instanceOf \Imagick) {
            $image->destroy();
            unset($image);
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \SYW\Library\Image\AbstractImageLibrary::getLibraryName()
     */
    public function getLibraryName()
    {
        return 'imagick';
    }
}
