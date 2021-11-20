<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Cache as SYWCache;
use SYW\Library\Image as SYWImage;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;

class Helper
{
    static $fsLoaded = false;
    
    static function getHtmlLinkTag($module, $url, $target, $text, $showTooltips = true, $popup_width = '600', $popup_height = '480')
    {
        $module_params = json_decode($module->params);
        
        $bootstrap_version = isset($module_params->bootstrap_version) ? $module_params->bootstrap_version : 'joomla';
        if ($bootstrap_version === 'joomla') {
            $bootstrap_version = 5;
        } else {
            $bootstrap_version = intval($bootstrap_version);
        }
        
        return self::createHtmlLink($url, $target, $text, $module->id, $showTooltips, $popup_width, $popup_height, $bootstrap_version);
    }
    
    // for B/C
    static function createHtmlLink($url, $target, $text, $id = '', $showTooltips = true, $popup_width = '600', $popup_height = '480', $bootstrap_version = 5)
    {
        $linkText = (empty($text)) ? $url : $text;
        
        $titleAttribute = '';
        $classAttribute = '';
        $extraClass = '';
        if ($showTooltips) {
            if (!empty($text)) {
                $titleAttribute = ' title="'.$url.'"';
                $classAttribute = ' class="hasTooltip"';
                $extraClass = ' hasTooltip';
            }
        }
        
        switch ($target) {
            case 1:	// open in a new window
                return '<a'.$classAttribute.' href="'.$url.'"'.$titleAttribute.' target="_blank" rel="nofollow">'.$linkText.'</a>';
                break;
            case 2:	// open in a popup window
                $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$popup_width.',height='.$popup_height;
                return '<a'.$classAttribute.' href="'.$url.'"'.$titleAttribute.' onclick="window.open(this.href, \'targetWindow\', \''.$attribs.'\'); return false;">'.$linkText.'</a>';
                break;
            case 3:	// open in a modal window
                $link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($linkText, ENT_COMPAT, 'UTF-8').'"';
                if ($bootstrap_version > 0) {
                    $link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#trsmodal_'.$id.'"';
                }
                return '<a href="'.$url.'" class="trsmodal_'.$id.$extraClass.'"' . $link_attributes . '>'.$linkText.'</a>';
                break;
            default: // open in parent window
                return '<a'.$classAttribute.' href="'.$url.'"'.$titleAttribute.' rel="nofollow">'.$linkText.'</a>';
                break;
        }
        
        return '';
    }
    
    static function getImageList(&$params, $module_suffix, $image_list, $images_path)
    {
        $thumb_path = $params->get('thumb_path', 'images');
        
        $subdirectory = 'thumbnails/trs';
        if ($thumb_path == 'cache') {
            $subdirectory = 'mod_trulyresponsiveslides';
        }
        $tmp_path = SYWCache::getTmpPath($thumb_path, $subdirectory);
        
        $image_mime_type = $params->get('image_mime_type', 'original');
        
        $list_images_array = array();
        
        foreach ($image_list as $image_item) {
            
            $imageext = strtolower(File::getExt($image_item));
            
            switch ($image_mime_type) {
                case 'image/jpg': $imageext = 'jpg'; break;
                case 'image/png': $imageext = 'png'; break;
                case 'image/webp': $imageext = 'webp'; break;
                case 'image/avif': $imageext = 'avif';
            }
            
            $imagename = basename($image_item);
            $imagenamenoext = File::stripExt($imagename);
            
            $imgfilename = $tmp_path . '/img_' . $module_suffix . '_' . $imagenamenoext . '.' . $imageext;
            if (is_file(JPATH_ROOT . '/' . $imgfilename)) {
                $list_images_array[] = $imgfilename; // get re-created image that fits the slider
            } else {
                $list_images_array[] = $images_path.$image_item; // keep original
            }
        }
        
        return $list_images_array;
    }
    
    static protected function getImageQualityFromExt($image_extension, $qualities = array('jpg' => 75, 'png' => 3, 'webp' => 80, 'avif' => 80))
    {
        $quality = -1;
        
        switch ($image_extension){
            case 'jpg': case 'jpeg': $quality = $qualities['jpg']; break; // 0 to 100
            case 'png': $quality = round(11.111111 * (9 - $qualities['png'])); break; // compression: 0 to 9
            case 'webp': $quality = $qualities['webp']; break; // 0 to 100
            case 'avif': $quality = $qualities['avif']; // 0 to 100
        }
        
        return $quality;
    }
    
    static function createImages(&$params, $module_suffix, $image_list, $images_path, $breakpoints = array())
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return false;
        } else {
            
            $img_width = $params->get('img_w', 1024);
            $img_height = $params->get('img_h', 640);
            
            $clear_cache = self::IsClearPictureCache($params);
            
            $thumb_path = $params->get('thumb_path', 'images');
            
            $subdirectory = 'thumbnails/trs';
            if ($thumb_path == 'cache') {
                $subdirectory = 'mod_trulyresponsiveslides';
            }
            $tmp_path = SYWCache::getTmpPath($thumb_path, $subdirectory);
            
            $image_mime_type = $params->get('image_mime_type', 'original');
            
            $quality_jpg = $params->get('qualitybg_jpg', 75);
            $quality_png = $params->get('qualitybg_png', 3);
            $quality_webp = $params->get('qualitybg_webp', 80);
            $quality_avif = $params->get('qualitybg_avif', 80);
            
            if ($quality_jpg > 100) {
                $quality_jpg = 100;
            }
            if ($quality_jpg < 0) {
                $quality_jpg = 0;
            }
            
            if ($quality_png > 9) {
                $quality_png = 9;
            }
            if ($quality_png < 0) {
                $quality_png = 0;
            }
            
            if ($quality_webp > 100) {
                $quality_webp = 100;
            }
            if ($quality_webp < 0) {
                $quality_webp = 0;
            }
            
            if ($quality_avif > 100) {
                $quality_avif = 100;
            }
            if ($quality_avif < 0) {
                $quality_avif = 0;
            }
            
            if ($clear_cache) {
                SYWVersion::refreshMediaVersion('mod_trulyresponsiveslides_' . $module_suffix);
            }
            
            foreach ($image_list as $image_item) {
                
                $imageext = strtolower(File::getExt($image_item));
                $original_imageext = $imageext;
                
                switch ($image_mime_type) {
                    case 'image/jpg': $imageext = 'jpg'; break;
                    case 'image/png': $imageext = 'png'; break;
                    case 'image/webp': $imageext = 'webp'; break;
                    case 'image/avif': $imageext = 'avif';
                }
                
                $imagename = basename($image_item);
                $imagenamenoext = File::stripExt($imagename);
                
                $imgfilename = $tmp_path . '/img_' . $module_suffix . '_' . $imagenamenoext;
                if (is_file(JPATH_ROOT . '/' . $imgfilename . '.' . $imageext) && !$clear_cache) { // image already exists
                    // do nothing
                } else { // re-create the image if original image has a different size than the slider
                    
                    $image = new SYWImage($images_path.$image_item);
                    
                    $creation_success = true;
                    
                    if (is_null($image->getImagePath())) {
                        $creation_success = false;
                    } else if (is_null($image->getImageMimeType())) {
                        $creation_success = false;
                    } else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
                        $creation_success = false;
                    } else {
                        if ($image->getImageWidth() == $img_width && $image->getImageHeight() == $img_height && empty($breakpoints)) { // do not re-create the image to avoid loss of quality if sizes are the same
                            if (is_file(JPATH_ROOT . '/' . $imgfilename . '.' . $imageext)) {
                                File::delete(JPATH_ROOT . '/' . $imgfilename . '.' . $imageext); // remove potential thumbnail otherwise it will be used instead of the original
                            }
                        } else {
                            
                            $quality = self::getImageQualityFromExt($imageext, array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif));
                            
                            if ($image->toThumbnail($imgfilename . '.' . $imageext, '', $img_width, $img_height, true, $quality)) {
                                
                                if ($image->getImageMimeType() === 'image/webp' || $image_mime_type === 'image/webp' || $image->getImageMimeType() === 'image/avif' || $image_mime_type === 'image/avif') { // create fallback
                                    
                                    $fallback_extension = $params->get('fallback_imagetype', 'png');
                                    $fallback_mime_type = 'image/png';
                                    if ($fallback_extension == 'jpg') {
                                        $fallback_mime_type = 'image/jpeg';
                                    }
                                    
                                    // create fallback with original image mime type when the original is not webp nor avif
                                    if ($image->getImageMimeType() !== 'image/webp' && $image->getImageMimeType() !== 'image/avif') {
                                        $fallback_extension = $original_imageext;
                                        $fallback_mime_type = $image->getImageMimeType();
                                    }
                                    
                                    $quality = self::getImageQualityFromExt($fallback_extension, array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif));
                                    
                                    $creation_success = $image->toThumbnail($imgfilename . '.' . $fallback_extension, $fallback_mime_type, $img_width, $img_height, true, $quality);
                                }
                            } else {
                                $creation_success = false;
                            }
                        }
                    }
                    
                    $image->destroy();
                    
                    if (!$creation_success) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    static function createThumbnails(&$params, $module_suffix, $image_list, $images_path)
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return false;
        } else {
            
            $crop_picture = $params->get('crop_pic', 0);
            
            $thumb_width = $params->get('thumb_w', 80);
            $thumb_height = $params->get('thumb_h', 60);
            
            $clear_cache = self::IsClearPictureCache($params);
            
            $thumb_path = $params->get('thumb_path', 'images');
            
            $subdirectory = 'thumbnails/trs';
            if ($thumb_path == 'cache') {
                $subdirectory = 'mod_trulyresponsiveslides';
            }
            $tmp_path = SYWCache::getTmpPath($thumb_path, $subdirectory);
            
            $thumbnail_mime_type = $params->get('thumb_mime_type', 'original');
            
            $quality_jpg = $params->get('quality_jpg', 75);
            $quality_png = $params->get('quality_png', 3);
            $quality_webp = $params->get('quality_webp', 80);
            $quality_avif = $params->get('quality_avif', 80);
            
            if ($quality_jpg > 100) {
                $quality_jpg = 100;
            }
            if ($quality_jpg < 0) {
                $quality_jpg = 0;
            }
            
            if ($quality_png > 9) {
                $quality_png = 9;
            }
            if ($quality_png < 0) {
                $quality_png = 0;
            }
            
            if ($quality_webp > 100) {
                $quality_webp = 100;
            }
            if ($quality_webp < 0) {
                $quality_webp = 0;
            }
            
            if ($quality_avif > 100) {
                $quality_avif = 100;
            }
            if ($quality_avif < 0) {
                $quality_avif = 0;
            }
            
            foreach($image_list as $image_item) {
                
                $imageext = strtolower(File::getExt($image_item));
                $original_imageext = $imageext;
                
                switch ($thumbnail_mime_type) {
                    case 'image/jpg': $imageext = 'jpg'; break;
                    case 'image/png': $imageext = 'png'; break;
                    case 'image/webp': $imageext = 'webp'; break;
                    case 'image/avif': $imageext = 'avif';
                }
                
                $imagename = basename($image_item);
                $imagenamenoext = File::stripExt($imagename);
                
                $thumbfilename = $tmp_path . '/thumb_' . $module_suffix . '_' . $imagenamenoext;
                if (is_file(JPATH_ROOT . '/' . $thumbfilename) && !$clear_cache) { // thumbnail already exists
                    // do nothing
                } else { // create the thumbnail
                    
                    $image = new SYWImage($images_path.$image_item);
                    
                    $creation_success = true;
                    
                    if (is_null($image->getImagePath())) {
                        $creation_success = false;
                    } else if (is_null($image->getImageMimeType())) {
                        $creation_success = false;
                    } else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
                        $creation_success = false;
                    } else {
                        
                        $quality = self::getImageQualityFromExt($imageext, array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif));
                        
                        if ($image->toThumbnail($thumbfilename . '.' . $imageext, '', $thumb_width, $thumb_height, $crop_picture, $quality)) {
                            
                            if ($image->getImageMimeType() === 'image/webp' || $thumbnail_mime_type === 'image/webp' || $image->getImageMimeType() === 'image/avif' || $thumbnail_mime_type === 'image/avif') { // create fallback
                                
                                $fallback_extension = $params->get('fallback_imagetype', 'png');
                                $fallback_mime_type = 'image/png';
                                if ($fallback_extension == 'jpg') {
                                    $fallback_mime_type = 'image/jpeg';
                                }
                                
                                // create fallback with original image mime type when the original is not webp nor avif
                                if ($image->getImageMimeType() !== 'image/webp' && $image->getImageMimeType() !== 'image/avif') {
                                    $fallback_extension = $original_imageext;
                                    $fallback_mime_type = $image->getImageMimeType();
                                }
                                
                                $quality = self::getImageQualityFromExt($fallback_extension, array('jpg' => $quality_jpg, 'png' => $quality_png, 'webp' => $quality_webp, 'avif' => $quality_avif));
                                
                                $creation_success = $image->toThumbnail($thumbfilename . '.' . $fallback_extension, $fallback_mime_type, $thumb_width, $thumb_height, $crop_picture, $quality);
                            }
                        } else {
                            $creation_success = false;
                        }
                    }
                    
                    $image->destroy();
                    
                    if (!$creation_success) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    static function getBasicSliderHtml(&$params, $image_list, $alts, $caption_list, $tooltips_lists, $image_directory, $id_suffix)
    {
        $html = '';
        
        $style = '';
        if (Factory::getDocument()->getDirection() == 'rtl') {
            $style = ' style="direction:rtl"';
        }
        
        $html = '<div id="slider_'.$id_suffix.'" class="flexslider"'.$style.'>';
        $html .= '<ul class="slides">';
        
        $i = 0;
        foreach ($image_list as $item) {
            
            $caption = '';
            if (!empty($caption_list) && isset($caption_list[$i]) && $caption_list[$i] != '') {
                $caption = '<div class="caption">'.$caption_list[$i].'</div>';
            }
            
            $alt = Text::sprintf('MOD_TRULYRESPONSIVESLIDER_SLIDENUMBER', $i);
            if (!empty($alts) && isset($alts[$i]) && $alts[$i] != '') {
                $alt = htmlspecialchars($alts[$i], ENT_COMPAT, 'UTF-8');
            }
            
            $html .= '<li>';
            $html .= SYWUtilities::getImageElement($item, $alt, array('width' => $params->get('img_w', 900), 'height' => $params->get('img_h', 600)), false, false, null, true, SYWVersion::getMediaVersion('mod_trulyresponsiveslides_' . $id_suffix));
            $html .= $caption;
            $html .= '</li>';
            
            $i++;
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    static function getBasicSliderJavascript(&$params, $id_suffix)
    {
        $html = '';
        
        $html .= '$("#slider_'.$id_suffix.'").flexslider({ ';
        $html .= self::getAnimation($params->get('type', 'fade')).', ';
        $html .= self::getAnimationLoop($params->get('loop', 0)).', ';
        $html .= self::getDirection($params->get('direction', 'horizontal'), $params->get('type', 'fade')).', ';
        $html .= self::getSlideshowSpeed($params->get('interval', 3000)).', ';
        $html .= self::getAnimationSpeed($params->get('speed', 1000)).', ';
        $html .= self::getPauseOnHover($params->get('pauseonslide', 1)).', ';
        $html .= self::getAutoStart($params->get('autostart', 1)).', ';
        $html .= 'prevText: "'.Text::_('JPREV').'", ';
        $html .= 'nextText: "'.Text::_('JNEXT').'", ';
        
        $html .= self::getRTL() ? self::getRTL().', ' : '';
        
        //$html .= self::getRemoveLoading().', ';
        
        if ($params->get('out_captions', 0)) {
            $html .= self::getOutCaption($id_suffix);
        } else {
            $html .= self::getCaption($id_suffix);
        }
        $html .= '});';
        
        if ($params->get('autostart', 1)) {
            $html .= '$("body").on("modalopen", function () { $("#slider_'.$id_suffix.'").data("flexslider").manualPause = true; $("#slider_'.$id_suffix.'").data("flexslider").manualPlay = false; $("#slider_'.$id_suffix.'").data("flexslider").pause(); }); ';
            $html .= '$("body").on("modalclose", function () { $("#slider_'.$id_suffix.'").data("flexslider").manualPause = false; $("#slider_'.$id_suffix.'").data("flexslider").manualPlay = true; $("#slider_'.$id_suffix.'").data("flexslider").play(); }); ';
        }
        
        return $html;
    }
    
    static function getSliderWithThumbHtml(&$params, $image_list, $alts, $caption_list, $tooltips_lists, $image_directory, $id_suffix)
    {
        $thumb_path = $params->get('thumb_path', 'images');
        
        $subdirectory = 'thumbnails/trs';
        if ($thumb_path == 'cache') {
            $subdirectory = 'mod_trulyresponsiveslides';
        }
        $tmp_path = SYWCache::getTmpPath($thumb_path, $subdirectory);
        
        $tmp_directory = Uri::root(true).'/'.$tmp_path;
        
        $html = '';
        
        $style = '';
        if (Factory::getDocument()->getDirection() == 'rtl') {
            $style = ' style="direction:rtl"';
        }
        
        $html .= '<div id="slider_'.$id_suffix.'" class="flexslider"'.$style.'>';
        $html .= '<ul class="slides">';
        
        $i = 0;
        foreach ($image_list as $item) {
            
            $caption = '';
            if (!empty($caption_list) && isset($caption_list[$i]) && $caption_list[$i] != '') {
                $caption = '<div class="caption">'.$caption_list[$i].'</div>';
            }
            
            $alt = Text::sprintf('MOD_TRULYRESPONSIVESLIDER_SLIDENUMBER', $i);
            if (!empty($alts) && isset($alts[$i]) && $alts[$i] != '') {
                $alt = htmlspecialchars($alts[$i], ENT_COMPAT, 'UTF-8');
            }
            
            $thumbnail_filename = basename($item);
            if (strtolower(File::getExt($thumbnail_filename)) === 'webp' || strtolower(File::getExt($thumbnail_filename)) === 'avif') {
                // we can't have a <picture> tag in the data-thumb attribute, therefore the fallback is used always
                $thumbnail_filename = File::stripExt($thumbnail_filename) . '.' . $params->get('fallback_imagetype', 'png');
            }
            
            $html .= '<li data-thumb="' . $tmp_directory . '/thumb_' . $id_suffix . '_' . $thumbnail_filename . '" data-thumb-alt="' . $alt . '">';
            $html .= SYWUtilities::getImageElement($item, $alt, array('width' => $params->get('img_w', 900), 'height' => $params->get('img_h', 600)), false, false, null, true, SYWVersion::getMediaVersion('mod_trulyresponsiveslides_' . $id_suffix));
            $html .= $caption;
            $html .= '</li>';
            
            $i++;
        }
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    static function getSliderWithThumbJavascript(&$params, $id_suffix)
    {
        $html = '';
        
        $html .= '$("#slider_'.$id_suffix.'").flexslider({ ';
        $html .= self::getAnimation($params->get('type', 'fade')).', ';
        $html .= self::getAnimationLoop($params->get('loop', 0)).', ';
        $html .= self::getDirection($params->get('direction', 'horizontal'), $params->get('type', 'fade')).', ';
        $html .= self::getSlideshowSpeed($params->get('interval', 3000)).', ';
        $html .= self::getAnimationSpeed($params->get('speed', 1000)).', ';
        $html .= self::getPauseOnHover($params->get('pauseonslide', 1)).', ';
        $html .= self::getAutoStart($params->get('autostart', 1)).', ';
        $html .= 'controlNav: "thumbnails", ';
        $html .= 'prevText: "'.Text::_('JPREV').'", ';
        $html .= 'nextText: "'.Text::_('JNEXT').'", ';
        
        $html .= self::getRTL() ? self::getRTL().', ' : '';
        
        //$html .= self::getRemoveLoading().', ';
        
        if ($params->get('out_captions', 0)) {
            $html .= self::getOutCaption($id_suffix);
        } else {
            $html .= self::getCaption($id_suffix);
        }
        $html .= '});';
        
        if ($params->get('autostart', 1)) {
            $html .= '$("body").on("modalopen", function () { $("#slider_'.$id_suffix.'").data("flexslider").manualPause = true; $("#slider_'.$id_suffix.'").data("flexslider").manualPlay = false; $("#slider_'.$id_suffix.'").data("flexslider").pause(); }); ';
            $html .= '$("body").on("modalclose", function () { $("#slider_'.$id_suffix.'").data("flexslider").manualPause = false; $("#slider_'.$id_suffix.'").data("flexslider").manualPlay = true; $("#slider_'.$id_suffix.'").data("flexslider").play(); }); ';
        }
        
        return $html;
    }
    
    static function getOutCaptionsHtml(&$params, $slides_count, $caption_list, $tooltip_list, $id_suffix)
    {
        $html = '';
        
        if (is_null($caption_list) || empty($caption_list)) { // no captions to show
            return '';
        }
        
        $extraclass_outside_caption = '';
        if ($params->get('out_caption_class', '')) {
            $extraclass_outside_caption = ' '.trim($params->get('out_caption_class'));
        }
        
        $html .= '<div id="out_captions_'.$id_suffix.'">';
        
        for ($i = 0; $i < $slides_count; $i++) {
            if (isset($caption_list[$i]) && $caption_list[$i] != '') {
                $html .= '<div class="out_caption'.$extraclass_outside_caption.'">'.$caption_list[$i].'</div>';
            } else {
                $html .= '<div class="out_caption"></div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    static protected function getAnimation($type) {
        
        switch ($type) {
            case 'slide': case 'slidev' : return 'animation: "slide"';
        }
        
        return 'animation: "fade"';
    }
    
    static protected function getAnimationLoop($loop) {
        $animation_loop = ($loop) ? 'true' : 'false';
        return 'animationLoop: '.$animation_loop;
    }
    
    static protected function getDirection($direction, $type = 'fade') {
        
        switch ($type) {
            //case 'slide' : if ($direction == 'vertical') { return 'direction: "vertical"'; };
            case 'slidev' : return 'direction: "vertical"';
        }
        
        return 'direction: "horizontal"';
    }
    
    /* time between changes */
    static protected function getSlideshowSpeed($interval) {
        return 'slideshowSpeed: '.$interval;
    }
    
    /* effect speed */
    static protected function getAnimationSpeed($speed) {
        return 'animationSpeed: '.$speed;
    }
    
    static protected function getPauseOnHover($pause) {
        $pauseOnHover = ($pause) ? 'true' : 'false';
        return 'pauseOnHover: '.$pauseOnHover;
    }
    
    static protected function getAutoStart($auto) {
        $autoStart = ($auto) ? 'true' : 'false';
        return 'slideshow: '.$autoStart;
    }
    
    static protected function getRemoveLoading() {
        return ''; //"start: function(slider) { $('body').removeClass('loading'); }";
    }
    
    static protected function getRTL() {
        if (Factory::getDocument()->getDirection() != 'rtl') {
            return '';
        }
        return 'rtl: true';
    }
    
    static protected function getCaption($id_suffix) {
        
        $script = '';
        
        $script .= 'init: function(slider) { ';
        $script .= '$("#trs_'.$id_suffix.' .flexslidercontainer .caption").css("display", "block"); ';
        $script .= '}, ';
        
        return $script;
    }
    
    static protected function getOutCaption($id_suffix) {
        
        $script = '';
        
        $script .= 'init: function(slider) { ';
        $script .= '$("#out_captions_'.$id_suffix.' .out_caption").eq(slider.currentSlide).fadeIn("slow"); ';
        $script .= '}, ';
        
        // 		$script .= 'before: function(slider) { ';
        // 			$script .= '$("#out_captions_'.$id_suffix.' .out_caption").fadeOut("slow"); ';
        // 		$script .= '}, ';
        
        $script .= 'after: function(slider) { ';
        $script .= '$("#out_captions_'.$id_suffix.' .out_caption").hide().eq(slider.currentSlide).fadeIn("slow"); ';
        $script .= '}, ';
        
        return $script;
    }
    
    /**
     * Load EasySlider plugin if needed
     */
    static function load_flexslider($remote = false)
    {
        if (self::$fsLoaded) {
            return;
        }
        
        $wam = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        $minified = (JDEBUG) ? '' : '-min';
        
        $wam->registerAndUseScript('trs.flexslider', 'mod_trulyresponsiveslides/jquery.flexslider' . $minified . '.js', ['relative' => true, 'version' => 'auto'], [], ['jquery']);
        
        if (JDEBUG) {
            $wam->registerAndUseStyle('trs.flexslider', 'mod_trulyresponsiveslides/flexslider.css', ['relative' => true]);
            if (Factory::getDocument()->getDirection() == 'rtl') {
                $wam->registerAndUseStyle('trs.flexslider_rtl', 'mod_trulyresponsiveslides/flexslider-rtl.css', ['relative' => true]);
            }
        } else {
            if (Factory::getDocument()->getDirection() != 'rtl') {
                $wam->registerAndUseStyle('trs.flexslider', 'mod_trulyresponsiveslides/flexslider-min.css', ['relative' => true, 'version' => 'auto']);
            } else {
                $wam->registerAndUseStyle('trs.flexslider', 'mod_trulyresponsiveslides/flexslider-pack-min.css', ['relative' => true, 'version' => 'auto']);
            }
        }
        
        self::$fsLoaded = true;
    }
    
    /**
     * Get the site mode
     * @return string (dev|prod|adv)
     */
    public static function getSiteMode($params)
    {
        return $params->get('site_mode', 'adv');
    }
    
    /**
     * Is the picture cache set to be cleared
     * @return boolean
     */
    public static function IsClearPictureCache($params)
    {
        if (self::getSiteMode($params) == 'dev') {
            return true;
        }
        if (self::getSiteMode($params) == 'prod') {
            return false;
        }
        return $params->get('clear_cache', true);
    }
    
    /**
     * Is the style/script cache set to be cleared
     * @return boolean
     */
    public static function IsClearHeaderCache($params)
    {
        if (self::getSiteMode($params) == 'dev') {
            return true;
        }
        if (self::getSiteMode($params) == 'prod') {
            return false;
        }
        return $params->get('clear_header_files_cache', 'true');
    }
    
    /**
     * Are errors shown ?
     * @return boolean
     */
    public static function isShowErrors($params)
    {
        if (self::getSiteMode($params) == 'dev') {
            return true;
        }
        if (self::getSiteMode($params) == 'prod') {
            return false;
        }
        return $params->get('show_errors', false);
    }
    
}
