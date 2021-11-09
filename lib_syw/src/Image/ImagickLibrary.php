<?php
/* This file has been prefixed by <PHP-Prefixer> for "PHP-Prefixer Getting Started" */

namespace SYW\Library\Image;

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
     * Creates new image instance
     *
	 * @param string $path
	 * @param number $width
	 * @param number $height
     * @return Image
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
                        
                    $this->crop_and_resize($image, $x, $y, $width, $height, $w, $h);
                }
            }
        }
        
        return $image;
    }
    
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
                
                $this->crop_and_resize($image, $x, $y, $width, $height, $w, $h);
            }
        }
        
        return $image;
    }
    
    public function createThumbnail($mime_type, $image, $path, $target_origin_x = 0, $target_origin_y = 0, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0, $quality = 75, $filter = null)
    {
        $thumbnail = $image;
        
        $this->crop_and_resize($thumbnail, $source_origin_x, $source_origin_y, $target_width, $target_height, $source_width, $source_height);
        
        if (!is_null($filter)) {
            $this->apply_filters($image, $filter);
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
    
    public function createFile($mime_type, $image, $path, $quality = 75, $filter = null)
    {
        if (!is_null($filter)) {
            $this->apply_filters($image, $filter);
        }
        
        $creation_success = false;
        
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
    
    protected function crop_and_resize(&$image, $source_origin_x = 0, $source_origin_y = 0, $target_width = 0, $target_height = 0, $source_width = 0, $source_height = 0)
    {
        $image->cropImage($source_width, $source_height, $source_origin_x, $source_origin_y);
        
        $image->resizeImage($target_width, $target_height, \Imagick::FILTER_LANCZOS, 1);
        //$image->thumbnailImage($target_width, $target_height); // produces bigger pngs
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
     * Returns name of current driver instance
     *
     * @return string
     */
    public function getDriverName()
    {
        return 'Imagick';
    }
    
    /**
     * Checks if Imagick library is available
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return (extension_loaded('imagick') && class_exists('Imagick'));
    }
    
    public function getImageWidth($image)
    {
        return $image->getImageWidth();
    }
    
    public function getImageHeight($image)
    {
        return $image->getImageHeight();
    }
    
    public function isTransparent($mime_type, $image)
    {
        // 0 = No transparency
        // 1 = Has transparency
        return $image->getImageAlphaChannel();
    }
    
    public function rotate(&$image, $orientation_angle)
    {
        $image->rotateimage(new \ImagickPixel('none'), intval(360 - $orientation_angle));
    }
    
    public function destroy(&$image)
    {
        if (isset($image) && is_object($image) && $image instanceOf \Imagick) {
            $image->destroy();
            unset($image);
        }
    }
    
    public function getLibraryName()
    {
        return 'imagick';
    }
}
