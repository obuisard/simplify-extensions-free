<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use SYW\Library\Image as SYWImage;
use SYW\Library\Libraries as SYWLibraries;

/**
 * Class Helper
 */
class Helper
{
	protected static $image_extension_types = array('png', 'jpg', 'gif', 'jpeg', 'webp', 'avif', 'svg');

	/**
	 * Look for images in content
	 * Using regular expressions
	 *
	 * @param string $introtext
	 * @param string $fulltext
	 * @param int    $threshold the minimum width or height the image must have to be eligible
	 *
	 * @return object|null the first image attributes if one is found, null otherwise
	 */
	static function getImageFromContent($introtext, $fulltext = '', $threshold = 0)
	{
	    Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));
	    
	    if ($introtext == '' && $fulltext == '') {
	        return null;
	    }
	    
	    preg_match_all('#<img[^>]*>#iU', $introtext . ' ' . $fulltext, $img_result); // finds all images
	    
	    if (empty($img_result[0])) {
	        return null;
	    }
	    
	    $result = null;
	    
	    libxml_use_internal_errors(true); // Suppress errors but still handle exceptions
	    
	    foreach ($img_result[0] as $element) {

	        try {
	            // Remove empty attributes (when we have 'title' instead of title="" for instance, which fails in XML)
	            
	            $dom = new \DOMDocument();
	            $dom->loadHTML($element, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
	            
	            $xpath = new \DOMXPath($dom);
	            $elements = $xpath->query('//img'); // Select all img tags
	            
	            foreach ($elements as $element) {
	                $attributesToRemove = [];
	                foreach ($element->attributes as $attribute) {
	                    if ($attribute->value === '') {
	                        $attributesToRemove[] = $attribute->name;
	                    }
	                }
	                foreach ($attributesToRemove as $attrName) {
	                    $element->removeAttribute($attrName);
	                }
	            }
	            
	            // Make sure img is well formed to be recognized as xml, ensure the <img> tag ends with />
	            $imgFromDOM = preg_replace('/<img([^>]+?)(?<!\/)>/', '<img$1 />', $dom->saveXML(null, LIBXML_NOXMLDECL));
	            
	            $img = new \SimpleXMLElement($imgFromDOM);
	            
	            if (count($img->attributes()) > 0) { // if it has attributes
	                $result = new \stdClass();
	                $hasSrc = false;
	                $isBigEnough = true;
	                foreach ($img->attributes() as $attribute_name => $attribute_value) {
	                    if ($attribute_value) {
	                        $result->{$attribute_name} = $attribute_value;
	                        if ($attribute_name == 'src') {
	                            $hasSrc = true;
	                        }
	                        
	                        // Discard x-small images
	                        if (($attribute_name == 'width' || $attribute_name == 'height') && (int)$attribute_value <= $threshold) {
	                            $isBigEnough = false;
	                        }
	                    }
	                }
	                if ($hasSrc && $isBigEnough) {
	                    break;
	                }
	                
	                $result = null;
	            }
	        } catch (\Exception $e) {
	            Log::add('LatestNewsEnhanced Helper:getImageSrcFromContent() - img is not well formed', Log::ERROR, 'syw');
	            continue;
	        }
	    }
	    
	    return $result;
	}

	/**
	 * Look for images in content
	 * Using DOMDocument (DO NOT USE BECAUSE OF HIGH MEMORY CONSUMPTION ON LARGE ARTICLES)
	 *
	 * @param string $introtext
	 * @param string $fulltext
	 * @param int $threshold the minimum width or height the image must have to be eligible
	 *
	 * @return object|null the first image attributes if one is found, null otherwise
	 */
	static function getImageFromContentThruDom($introtext, $fulltext = '', $threshold = 0)
	{	    
	    if ($introtext == '' && $fulltext == '') {
	        return null;
	    }
	    
	    $dom = new \DOMDocument();
	    $dom->loadHTML($introtext . ' ' . $fulltext);
	    $anchors = $dom->getElementsByTagName('img');
	    
	    if ($anchors->length == 0) {
	        return null;
	    }
	    
	    $result = null;
	    
	    // Make sure we do not handle invalid HTML: ignore img without the src attribute or if src is empty
	    
	    foreach ($anchors as $element) {
	        if ($element->hasAttributes()) {
	            $result = new \stdClass();
	            $hasSrc = false;
	            $isBigEnough = true;
	            foreach ($element->attributes as $attribute) {
	                if ($attribute->value) {
	                    $result->{$attribute->name} = $attribute->value;
	                    if ($attribute->name == 'src') {
	                        $hasSrc = true;
	                    }
	                    
	                    // Discard x-small images
	                    if (($attribute->name == 'width' || $attribute->name == 'height') && (int)$attribute->value <= $threshold) {
	                        $isBigEnough = false;
	                    }
	                }
	            }
	            if ($hasSrc && $isBigEnough) {
	                break;
	            }
	            
	            $result = null;
	        }
	    }
	    
	    return $result;
	}
	
	/**
	 * Look for images in the item image parameters
	 * 
	 * @param string $images
	 * @param string $section
	 * 
	 * @return object|null
	 */
	static function getImageFromItem($images, $section = 'intro')
	{
	    $registry = new Registry();
	    $registry->loadString($images);
	    $images_array = $registry->toArray();
	    
	    if (empty($images_array)) {
	        return null;
	    }
	    
	    if (!isset($images_array['image_' . $section]) || $images_array['image_' . $section] == '') {
	        return null;
	    }
	    
	    $result = new \stdClass();
	    
	    $result->src = $images_array['image_' . $section];
	    
	    if (isset($images_array['image_' . $section . '_alt']) && trim($images_array['image_' . $section . '_alt']) != '') {
	        if (isset($images_array['image_' . $section . '_alt_empty']) && $images_array['image_' . $section . '_alt_empty'] == '1') {
	            return $result;
	        }
	        
	        $result->alt = $images_array['image_' . $section . '_alt'];
	    }

	    return $result;
	}
	
	/**
	 * Look for images in the category parameters
	 * 
	 * @param string $params
	 * 
     * @return object|null
	 */
	static function getImageFromCategory($params)
	{
	    $category_params = json_decode($params);
	    
	    if (!isset($category_params->image) || $category_params->image == '') {
	        return null;
	    }
	    
	    $result = new \stdClass();
	    
	    $result->src = $category_params->image;
	    
	    if (isset($category_params->image_alt) && trim($category_params->image_alt) != '') {
	        if (isset($category_params->image_alt_empty) && $category_params->image_alt_empty == '1') {
	            return $result;
	        }
	        
	        $result->alt = $category_params->image_alt;
	    }	    
	    
	    return $result;
	}
	
	/**
	 * Look for images in the media field
	 * 
	 * @param string $value
	 * 
     * @return object|null
	 */
	static function getImageFromMediaField($value)
	{
	    $image_custom_field_value = json_decode($value, true);
	    
	    $result = new \stdClass();
	    
	    if ($image_custom_field_value !== null) { // new json string from accessible media field
	        if (!isset($image_custom_field_value['imagefile']) || $image_custom_field_value['imagefile'] == '') {
	            return null;
	        }
	        
	        $result->src = $image_custom_field_value['imagefile'];
	        
	        if (isset($image_custom_field_value['alt_text']) && trim($image_custom_field_value['alt_text']) != '') {
	            if (isset($image_custom_field_value['alt_empty']) && $image_custom_field_value['alt_empty'] == '1') {
	                return $result;
	            }
	            
	            $result->alt = $image_custom_field_value['alt_text'];
	        }	        
	        
	    } else { // old values before using accessible media field
	        $result->src = $value;
	    }
	    
	    return $result;
	}

	/**
	* Create the thumbnail(s), if possible
	*
	* @param string $module_id
	* @param string $item_id
	* @param string $imagesrc
	* @param string $tmp_path
	* @param integer $head_width
	* @param integer $head_height
	* @param boolean $crop_picture
	* @param array $image_quality_array
	* @param string $filter
	* @param boolean $create_high_resolution
	* @param boolean $allow_remote
	* @param string $thumbnail_mime_type
	*
	* @return array the original image path if errors before thumbnail creation
	*  or no thumbnail path if errors during thumbnail creation
	*  or thumbnail path if no error
	*/
	static function getImageFromSrc($module_id, $item_id, $imagesrc, $tmp_path, $head_width, $head_height, $crop_picture, $image_quality_array, $filter, $create_high_resolution = false, $allow_remote = true, $thumbnail_mime_type = '')
	{
	    $result = [];

		if ($head_width == 0 || $head_height == 0) {
			// keep original image

			return [
			    'url' => $imagesrc,
			    'error' => Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INFO_USINGORIGINALIMAGE'), // necessary to specify thumbnail creation failed
			];
		}

		if (!extension_loaded('gd') && !extension_loaded('imagick')) {
			// missing image library

		    return [
		        'url' => $imagesrc,
		        'error' => Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_WARNING_NOIMAGELIBRARYLOADED'),
		    ];
		}

		$original_imagesrc = $imagesrc;

		// there may be extra info in the path
		// example: http://www.tada.com/image.jpg?x=3
		// thubmnails cannot be created if ? in the path

		$url_array = explode("?", $imagesrc);
		$imagesrc = $url_array[0];

		$imageext = strtolower(File::getExt($imagesrc));
		$original_imageext = $imageext;

		if (!in_array($imageext, self::$image_extension_types)) {

			// case where image is a URL with no extension (generated image)
			// example: http://argos.scene7.com/is/image/Argos/7491801_R_Z001A_UC1266013?$TMB$&wid=312&hei=312
			// thubmnails cannot be created from generated images external paths
			// or image has another file type like .tiff

		    return [
		        'url' => $original_imagesrc,
		        'error' => Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_UNSUPPORTEDFILETYPE', $original_imagesrc),
		    ];
		}
		
		// Special case with SVG: no creation of thumbnails
		if ($imageext === 'svg') {
		    return [
		        'url' => $original_imagesrc, 
		        'error' => '',
		    ];
		}

		// URL works only if 'allow url fopen' is 'on', which is a security concern
		// retricts images to the ones found on the site, external URLs are not allowed (for security purposes)
		if (substr_count($imagesrc, 'http') <= 0) { // if the image is internal
			if (substr($imagesrc, 0, 1) == '/') {
				// take the slash off
				$imagesrc = ltrim($imagesrc, '/');
			}
		} else {
			$base = Uri::base(); // Uri::base() is http://www.mysite.com/subpath/
			$imagesrc = str_ireplace($base, '', $imagesrc);
		}

		// we end up with all $imagesrc paths as 'images/...'
		// if not, the URL was from an external site

		if (substr_count($imagesrc, 'http') > 0) {
			// we have an external URL
			if (/*!ini_get('allow_url_fopen') || */!$allow_remote) {
				return [
				    'url' => $original_imagesrc,
				    'error' => Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_EXTERNALURLNOTALLOWED', $imagesrc),
				];
			}
		}

		switch ($thumbnail_mime_type) {
		    case 'image/jpg': $imageext = 'jpg'; break;
		    case 'image/png': $imageext = 'png'; break;
		    case 'image/webp': $imageext = 'webp'; break;
		    case 'image/avif': $imageext = 'avif';
		}

		$filename = $tmp_path . '/thumb_' . $module_id . '_' . $item_id . '.' . $imageext;

		// create the thumbnail

		$image = new SYWImage($imagesrc);

		if (is_null($image->getImagePath())) {
		    $result['error'] = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_IMAGEFILEDOESNOTEXIST', $imagesrc);
		} else if (is_null($image->getImageMimeType())) {
		    $result['error'] = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_UNABLETOGETIMAGEPROPERTIES', $imagesrc);
		} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
		    $result['error'] = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_UNSUPPORTEDFILETYPE', $imagesrc);
		} else {

		    $quality = self::getImageQualityFromExt($imageext, $image_quality_array);

			// negative values force the creation of the thumbnails with size of original image
			// great to create high-res of original image and/or to use quality parameters to create an image with smaller file size
			if ($head_width < 0 || $head_height < 0) {
				$head_width = $image->getImageWidth();
				$head_height = $image->getImageHeight();
			}

			if ($image->toThumbnail($filename, $thumbnail_mime_type, $head_width, $head_height, $crop_picture, $quality, $filter, $create_high_resolution)) {

			    $result['thumb_width'] = $image->getThumbnailWidth();
			    $result['thumb_height'] = $image->getThumbnailHeight();
			    
    			if ($image->getImageMimeType() === 'image/webp' || $thumbnail_mime_type === 'image/webp' || $image->getImageMimeType() === 'image/avif' || $thumbnail_mime_type === 'image/avif') { // create fallback

    			    $fallback_extension = 'png';
    			    $fallback_mime_type = 'image/png';

    			    // create fallback with original image mime type when the original is not webp or avif
    			    if ($image->getImageMimeType() !== 'image/webp' && $image->getImageMimeType() !== 'image/avif') {
    			        $fallback_extension = $original_imageext;
    			        $fallback_mime_type = $image->getImageMimeType();
    			    }

    			    $quality = self::getImageQualityFromExt($fallback_extension, $image_quality_array);

    			    if (!$image->toThumbnail($tmp_path . '/thumb_' . $module_id . '_' . $item_id . '.' . $fallback_extension, $fallback_mime_type, $head_width, $head_height, $crop_picture, $quality, $filter, $create_high_resolution)) {
    			        $result['error'] = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_THUMBNAILCREATIONFAILED', $imagesrc);
    				}
    			}
			} else {
			    $result['error'] = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_ERROR_THUMBNAILCREATIONFAILED', $imagesrc);
			}
		}

		$image->destroy();

		if (empty($result['error'])) {
			$result['url'] = $filename;
		}

		return $result;
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

	/**
	 * Delete all thumbnails for a module instance
	 *
	 * @param string $module_id
	 * @param string $tmp_path
	 *
	 * @return false if the glob function failed, true otherwise
	 */
	static function clearThumbnails($module_id, $tmp_path)
	{
		Log::addLogger(array('text_file' => 'syw.errors.php'), Log::ALL, array('syw'));

		if (function_exists('glob')) {
			$filenames = glob(JPATH_ROOT.'/'.$tmp_path.'/thumb_'.$module_id.'_*.*');
			if ($filenames == false) {
				Log::add('LatestNewsEnhanced Helper:clearThumbnails() - Error on glob - No permission on files/folder or old system', Log::ERROR, 'syw');
				return false;
			}

			foreach ($filenames as $filename) {
				File::delete($filename); // returns false if deleting failed - won't log to avoid making the log file huge
			}

			return true;
		}
			
		Log::add('LatestNewsEnhanced Helper:clearThumbnails() - glob function does not exist', Log::ERROR, 'syw');

		return false;
	}

	/**
	 * Check if thumbnail already exists for an item
	 * When including high resolution thumbnails, both images need to exist
	 * Since there is no way to know what extension has been previously used, it needs to iterate through the valid extension types
	 *
	 * @param string $module_id
	 * @param string $item_id
	 * @param string $tmp_path
	 * @param boolean $include_highres
	 *
	 * @return string|boolean the thumbnail filename if found, false otherwise
	 */
	static function thumbnailExists($module_id, $item_id, $tmp_path, $include_highres = false)
	{
		$existing_thumbnail_path = null;
		foreach (self::$image_extension_types as $thumbnail_extension_type) {
			$thumbnail_path = $tmp_path.'/thumb_'.$module_id.'_'.$item_id.'.'.$thumbnail_extension_type;
			if (is_file(JPATH_ROOT.'/'.$thumbnail_path)) {
				$existing_thumbnail_path = $thumbnail_path; // uses the first file found, but could be several with different extensions
			}
		}

		// glob may not work with cURL on some php versions (like 5.4.14)
// 		$result = glob("'.$tmp_path.'/thumb_'.$module_id.'_'.$item_id.'.{jpg,jpeg,png,gif}", GLOB_BRACE);
// 		if ($result == false || empty($result)) {
// 			return false;
// 		} else {
// 			$existing_thumbnail_path = $result[0]; // uses the first file found, but could be several with different extensions
// 			// use filemtime() to get the most recent file? worth the trouble?
// 		}

		if (!empty($existing_thumbnail_path)) {
			if ($include_highres) {
				$thumbnail_path_highres = str_replace('.', '@2x.', $existing_thumbnail_path);
				if (is_file(JPATH_ROOT.'/'.$thumbnail_path_highres)) {
					return $existing_thumbnail_path;
				}
			} else {
				return $existing_thumbnail_path;
			}
		}

		return false;
	}

	/**
	* Create the first part of the <a> tag
	*/
	static function getHtmlATag($module, $item, $follow = true, $tooltip = true, $popup_width = '600', $popup_height = '500', $css_classes = '', $anchors = '', $add_aria_label = true)
	{
		$module_params = json_decode($module->params);

		$bootstrap_version = isset($module_params->bootstrap_version) ? $module_params->bootstrap_version : 5;
		if ($bootstrap_version === 'joomla') {
			$bootstrap_version = 5;
		} else {
			$bootstrap_version = intval($bootstrap_version);
		}

		return self::getATag($item, $follow, $tooltip, $popup_width, $popup_height, $css_classes, $anchors, $module->id, $add_aria_label, $bootstrap_version);
	}

	/*
	 * for B/C
	 */
	static function getATag($item, $follow = true, $tooltip = true, $popup_width = '600', $popup_height = '500', $css_classes = '', $anchors = '', $module_id = 0, $add_aria_label = true, $bootstrap_version = 5)
	{
		$attribute_title = '';
		$attribute_class = '';
		if ($item->linktarget == 3) {
		    $attribute_class = 'lnemodal_'.$module_id;
		}

		if ($tooltip) {
			$attribute_title = ' title="'.htmlspecialchars($item->linktitle, ENT_COMPAT, 'UTF-8').'"';
			$attribute_class .= empty($attribute_class) ? 'hasTooltip' : ' hasTooltip';
		}

		if (!empty($css_classes)) {
		    $attribute_class .= empty($attribute_class) ? $css_classes : ' '.$css_classes;
		}

		if (!empty($attribute_class)) {
			$attribute_class = ' class="'.$attribute_class.'"';
		}

		$nofollow = '';
		if (!$follow) {
			$nofollow = ' rel="nofollow"';
		}

		$attribute_aria_label = '';
		if ($add_aria_label) {
			$readmore_text = Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_READMOREABOUT_LABEL', htmlspecialchars($item->linktitle, ENT_COMPAT, 'UTF-8')); // default
			$attribute_aria_label = ' aria-label="'.$readmore_text.'"';
		}

		switch ($item->linktarget) {
			case 1:	// open in a new window
				return '<a href="'.$item->link.$anchors.'" target="_blank"'.$attribute_class.$attribute_title.$attribute_aria_label.$nofollow.'>';
				break;
			case 2:	// open in a popup window
				$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='.$popup_width.',height='.$popup_height;
				return '<a href="'.$item->link.$anchors.'"'.$attribute_class.$attribute_title.$attribute_aria_label.' onclick="window.open(this.href, \'targetWindow\', \''.$attribs.'\'); return false;">';
				break;
			case 3:	// open in a modal window
				$extra_url = '';
				if ($item->isinternal) {
					if (strpos($item->link, "?") !== false) {
						$extra_url .= '&';
					} else {
						$extra_url .= '?';
					}
					$extra_url .= 'tmpl=component&print=1';
				}

				$link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($item->linktitle, ENT_COMPAT, 'UTF-8').'"';
				if ($bootstrap_version > 0) {
					$link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#lnemodal_'.$module_id.'"';
				}

				return '<a href="'.$item->link.$extra_url.$anchors.'"'.$attribute_class.$attribute_title.$attribute_aria_label.$link_attributes.'>';
				break;
			default: // open in parent window
				return '<a href="'.$item->link.$anchors.'"'.$attribute_class.$attribute_title.$attribute_aria_label.$nofollow.'>';
				break;
		}
	}

	static function date_to_counter($date, $date_in_future = false)
	{
		$date_origin = new Date($date);
		$now = new Date(); // now

		$difference = $date_origin->diff($now); // DateInterval object PHP 5.3 [y] => 0 [m] => 0 [d] => 26 [h] => 23 [i] => 11 [s] => 32 [invert] => 0 [days] => 26

		return array('years' => $difference->y, 'months' => $difference->m, 'days' => $difference->d, 'hours' => $difference->h, 'mins' => $difference->i, 'secs' => $difference->s);
	}

	static function isInfoTypeRequired($info_type, $params)
	{
	    if (in_array($info_type, self::getDetailsInfoTypes($params))) {
	        return true;
	    }

	    return false;
	}

	/**
	 *
	 * @param object $params
	 * @param string $prefix
	 * @param string $subform
	 * @return array
	 */
	static function getDetailsInfoTypes($params, $prefix = '', $subform = 'information_blocks')
	{
	    $info_types = array();

	    // get data from subform items

	    if ($prefix.$subform) {
	        $information_blocs = $params->get($prefix.$subform); // array of objects
	        if (!empty($information_blocs) && is_object($information_blocs)) {
	            foreach ($information_blocs as $information_bloc) {
	                if ($information_bloc->info != 'none') {
	                    $info_types[] = $information_bloc->info;
	                }
	            }
	        }
	    }

	    return $info_types;
	}

	/**
	 * Get detail parameters
	 *
	 * @param object $params
	 * @param string $prefix a prefix for the fields names
	 * @return array
	 */
	static function getDetails($params, $prefix = '', $subform = 'information_blocks')
	{
	    $infos = array();
	    
	    $user = Factory::getUser();
	    $groups	= $user->getAuthorisedViewLevels();

		// get data from subform items

		if ($prefix.$subform) {
			$information_blocs = $params->get($prefix.$subform); // array of objects
			if (!empty($information_blocs) && is_object($information_blocs)) {
				foreach ($information_blocs as $information_bloc) {
				    if ($information_bloc->info != 'none' && in_array($information_bloc->access, $groups)) {

						$details = array();
						$details['info'] = $information_bloc->info;
						$details['prepend'] = $information_bloc->prepend;
						$details['show_icons'] = $information_bloc->show_icons == 1 ? true : false;
						$details['icon'] = $information_bloc->icon;
						$details['extra_classes'] = isset($information_bloc->extra_classes) ? trim($information_bloc->extra_classes) : '';

						$infos[] = $details;

						if ($information_bloc->new_line == 1) {
							$infos[] = array('info' => 'newline', 'prepend' => '', 'show_icons' => false, 'icon' => '', 'extra_classes' => '');
						}
					}
				}
			}
		}

		return $infos;
	}

	/**
	 * Get icon and label pre-data, if any
	 */
	static function getPreData($label, $show_icon, $default_icon, $icon = '')
	{
		$html = "";

		if ($show_icon && Factory::getDocument()->getDirection() !== 'rtl') {
			$icon = empty($icon) ? $default_icon : $icon;
			$html .= '<i class="detail_icon ' . $icon . '"></i>';
		}

		if (!empty($label)) {
		    $html .= '<span class="detail_label">' . $label . '</span>';
		}

		if ($show_icon && Factory::getDocument()->getDirection() === 'rtl') {
		    $icon = empty($icon) ? $default_icon : $icon;
		    $html .= '<i class="detail_icon ' . $icon . '"></i>';
		}

		return $html;
	}

	/**
	 * Get block information
	 *
	 * @param array $infos
	 * @param object $params
	 * @param object $item
	 * @param object $item_params
	 * @return string
	 */
	static function getInfoBlock($infos, $params, $item, $item_params = null)
	{
		$info_block = '';

		if (empty($infos)) {
			return $info_block;
		}

		$show_date = $params->get('show_d', 'date'); // kept for backward compatibility
		$date_format = $params->get('d_format', 'd F Y');
		$time_format = $params->get('t_format', 'H:i');
		$postdate = $params->get('post_d', 'published');

		$separator = htmlspecialchars($params->get('separator', ''));
		$separator = empty($separator) ? ' ' : $separator;

		$info_block .= '<dt>'.Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INFORMATION_LABEL').'</dt>';

		$info_block .= '<dd class="newsextra">';
		$has_info_from_previous_detail = false;

		foreach ($infos as $key => $value) {

			switch ($value['info']) {
				case 'newline':
					$info_block .= '</dd><dd class="newsextra">';
					$has_info_from_previous_detail = false;
				break;

				case 'readmore':

					if (isset($item->link) && !empty($item->link) && $item->cropped) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_readmore' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-more', $value['icon']);
						}

						$info_block .= '<span class="detail_data">';

						$readmore_text = Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_VALUE_READMORE'); // default

						$link_label_item = trim($params->get('link', ''));

						if (strpos($item->linktitle, rtrim($item->title, '.')) === false) {
							$link_label_item = $item->linktitle; // use the label from links a, b or c, if they exist
						}
						if (!empty($link_label_item)) {
							$readmore_text = $link_label_item;
						}

						$follow = $params->get('follow', true);
						$popup_width = $params->get('popup_x', 600);
						$popup_height = $params->get('popup_y', 500);

						$info_block .= self::getATag($item, $follow, true, $popup_width, $popup_height, '', '', 0, true).$readmore_text.'</a>';

						$info_block .= '</span>';

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-more', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'hits':

					//if ($item_params->get('show_hits')) {
					if (isset($item->hits)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_hits' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-eye', $value['icon']);
						}

						$info_block .= '<span class="detail_data">';

						$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_HITS', $item->hits);

						$info_block .= '</span>';

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-eye', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'rating':

					//if ($item_params->get('show_vote')) {
					if (isset($item->vote)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_rating' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						$icon_default = 'star-outline';
						if (!empty($item->vote)) {
							if ($item->vote == 5) {
								$icon_default = 'SYWicon-star';
							} else {
								$icon_default = 'SYWicon-star-half';
							}
						}

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], $icon_default, $value['icon']);
						}

						$info_block .= '<span class="detail_data">';

						if (!empty($item->vote)) {
							if ($params->get('show_rating') == 'text') {
								$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_RATING', $item->vote).' ';
								$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_FROMUSERS', $item->vote_count);
								//$info_block .= $item->vote.'/5 '.Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_FROMUSERS', $item->vote_count);
							} else { // use stars

								$whole = intval($item->vote);

								$stars = '';
								for ($i = 0; $i < $whole; $i++) {
									$stars .= '<i class="SYWicon-star"></i>';
								}

								if ($whole < 5) {

									// get fraction

									$fraction = $item->vote - $whole;
									if ($fraction > .4) {
										$stars .= '<i class="SYWicon-star-half"></i>';
									} else {
										$stars .= '<i class="SYWicon-star-outline"></i>';
									}

									for ($i = $whole + 1; $i < 5; $i++) {
										$stars .= '<i class="SYWicon-star-outline"></i>';
									}
								}

								$info_block .= $stars;
							}
						} else {
							$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_NORATING');
						}

						$info_block .= '</span>';

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], $icon_default, $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'author':

					//if ($item_params->get('show_author')) {
					if (isset($item->author)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_author' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'user', $value['icon']);
						}

						$info_block .= '<span class="detail_data">';

						$info_block .= $item->author;

						$info_block .= '</span>';

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-user', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'keywords':
					if (isset($item->metakey) && !empty($item->metakey)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_keywords' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-tag', $value['icon']);
						}

						$info_block .= '<span class="detail_data">';

						$info_block .= $item->metakey;

						$info_block .= '</span>';

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-tag', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'category':
				case 'linkedcategory':

					//if ($item_params->get('show_category')) {
					if (isset($item->category_title)) {

						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$info_block .= '<span class="detail detail_category' . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if ($value['info'] == 'category') {
							$icon_default = 'SYWicon-folder';
						} else {
							$icon_default = 'SYWicon-folder-open';
						}

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], $icon_default, $value['icon']);
						}

						// if ($item_params->get('link_category')
						if ($value['info'] == 'category') {
							$info_block .= '<span class="detail_data">'.$item->category_title.'</span>';
						} else {
							if (isset($item->catlink)) {
								$info_block .= '<a class="detail_data" href="'.$item->catlink.'">'.$item->category_title.'</a>';
							} else {
								$info_block .= '<span class="detail_data">'.$item->category_title.'</span>';
							}
						}

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], $icon_default, $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'date':
				case 'ago':
				case 'agomhd':
				case 'agohm':
					if (isset($item->date)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$additional_class = '';
						if (empty($item->date)) {
							$additional_class = ' nodate';
						}

						$info_block .= '<span class="detail detail_date' . $additional_class . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-calendar', $value['icon']);
						}

						if ($item->date) {

							$info_block .= '<span class="detail_data">';

							if ($show_date != 'date') { // for backward compatibility until re-saved
								$value['info'] = $show_date;
							}

							if ($value['info'] == 'date') {
								$info_block .= HTMLHelper::_('date', $item->date, $date_format);
							} else if ($value['info'] == 'ago' && isset($item->nbr_days)) {
								if ($item->nbr_years > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INYEARSMONTHSDAYSONLY', $item->nbr_years, $item->nbr_months, $item->nbr_days);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_YEARSMONTHSDAYSAGO', $item->nbr_years, $item->nbr_months, $item->nbr_days);
									}
								} else if ($item->nbr_months > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INMONTHSDAYSONLY', $item->nbr_months, $item->nbr_days);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_MONTHSDAYSAGO', $item->nbr_months, $item->nbr_days);
									}
								} else if ($item->nbr_days == 0) {
									$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_TODAY');
								} else if ($item->nbr_days == 1) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_TOMORROW');
									} else {
										$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_YESTERDAY');
									}
								} else {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INDAYSONLY', $item->nbr_days);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_DAYSAGO', $item->nbr_days);
									}
								}
							} else if ($value['info'] == 'agomhd' && isset($item->nbr_days)) {
								if ($item->nbr_years > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INYEARSMONTHSDAYSONLY', $item->nbr_years, $item->nbr_months, $item->nbr_days);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_YEARSMONTHSDAYSAGO', $item->nbr_years, $item->nbr_months, $item->nbr_days);
									}
								} else if ($item->nbr_months > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INMONTHSDAYSONLY', $item->nbr_months, $item->nbr_days);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_MONTHSDAYSAGO', $item->nbr_months, $item->nbr_days);
									}
								} else if ($item->nbr_days > 0) {
									if ($item->nbr_days == 1) {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INADAY');
										} else {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_DAYAGO');
										}
									} else {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INDAYSONLY', $item->nbr_days);
										} else {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_DAYSAGO', $item->nbr_days);
										}
	 								}
								} else if ($item->nbr_hours > 0) {
									if ($item->nbr_hours == 1) {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INANHOUR');
										} else {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_HOURAGO');
										}
									} else {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INHOURS', $item->nbr_hours);
										} else {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_HOURSAGO', $item->nbr_hours);
										}
									}
								} else {
									if ($item->nbr_minutes == 1) {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INAMINUTE');
										} else {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_MINUTEAGO');
										}
									} else {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INMINUTES', $item->nbr_minutes);
										} else {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_MINUTESAGO', $item->nbr_minutes);
										}
									}
								}
							} else if (isset($item->nbr_days)) {
								if ($item->nbr_years > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INYEARSMONTHSDAYSHOURSMINUTES', $item->nbr_years, $item->nbr_months, $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_YEARSMONTHSDAYSHOURSMINUTESAGO', $item->nbr_years, $item->nbr_months, $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									}
								} elseif ($item->nbr_months > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INMONTHSDAYSHOURSMINUTES', $item->nbr_months, $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_MONTHSDAYSHOURSMINUTESAGO', $item->nbr_months, $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									}
								} else if ($item->nbr_days > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INDAYSHOURSMINUTES', $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_DAYSHOURSMINUTESAGO', $item->nbr_days, $item->nbr_hours, $item->nbr_minutes);
									}
								} else if ($item->nbr_hours > 0) {
									if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INHOURSMINUTES', $item->nbr_hours, $item->nbr_minutes);
									} else {
										$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_HOURSMINUTESAGO', $item->nbr_hours, $item->nbr_minutes);
									}
								} else {
									if ($item->nbr_minutes == 1) {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_INAMINUTE');
										} else {
											$info_block .= Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_MINUTEAGO');
										}
									} else {
										if ($postdate == 'finished' || $postdate == 'fin_pen' || $postdate == 'pending') {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_INMINUTES', $item->nbr_minutes);
										} else {
											$info_block .= Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_MINUTESAGO', $item->nbr_minutes);
										}
									}
								}
							} else {
								$info_block .= HTMLHelper::_('date', $item->date, $date_format);
							}

							$info_block .= '</span>';
						} else {
							$info_block .= '<span class="detail_data">-</span>';
						}

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-calendar', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;

				case 'time':
					if (isset($item->date)) {
						if ($has_info_from_previous_detail) {
							$info_block .= '<span class="delimiter">'.$separator.'</span>';
						}

						$additional_class = '';
						if (empty($item->date)) {
							$additional_class = ' nodate';
						}

						$info_block .= '<span class="detail detail_time' . $additional_class . ($value['extra_classes'] ? ' ' . $value['extra_classes'] : '') . '">';

						if (Factory::getDocument()->getDirection() != 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-clock', $value['icon']);
						}

						if ($item->date) {
							$info_block .= '<span class="detail_data">' . HTMLHelper::_('date', $item->date, $time_format) . '</span>';
						} else {
							$info_block .= '<span class="detail_data">-</span>';
						}

						if (Factory::getDocument()->getDirection() == 'rtl') {
							$info_block .= self::getPreData($value['prepend'], $value['show_icons'], 'SYWicon-clock', $value['icon']);
						}

						$info_block .= '</span>';

						$has_info_from_previous_detail = true;
					}
				break;
			}
		}

		$info_block .= '</dd>';

		// remove potential 'newsextra' block when no data is available
		$info_block = str_replace('<dd class="newsextra"></dd>', '', $info_block);

		if (strpos($info_block, 'dd') === false) {
			return ''; // accessibility rule: if no dd then no dt is allowed
		}

		return $info_block;
	}

	/**
	* Load plugin if needed by animation
	*/
	static function loadAnimationLibrary($animation, $remote = false)
	{
		SYWLibraries::loadPurePagination($remote);
	}

	/**
	 * Load common stylesheet to all module instances
	 */
	static function loadCommonStylesheet()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->registerAndUseStyle('lne.common_styles', 'mod_latestnewsenhanced/common_styles.min.css', ['relative' => true, 'version' => 'auto']);
	}

	/**
	 * Load user stylesheet to all module instances
	 * if the file has 'substitute' in the name, it will replace all module styles
	 */
	static function loadUserStylesheet($styles_substitute = false)
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$prefix = 'common_user';
		if ($styles_substitute) {
			$prefix = 'substitute';
		}

		if (File::exists(JPATH_ROOT . '/media/mod_latestnewsenhanced/css/' . $prefix . '_styles-min.css')) {
		    if (JDEBUG && File::exists(JPATH_ROOT . '/media/mod_latestnewsenhanced/css/' . $prefix . '_styles.css')) {
		        $wam->registerAndUseStyle('lne.' . $prefix . '_styles', 'mod_latestnewsenhanced/' . $prefix . '_styles.css', ['relative' => true, 'version' => 'auto']);
		    } else {
		        $wam->registerAndUseStyle('lne.' . $prefix . '_styles', 'mod_latestnewsenhanced/' . $prefix . '_styles-min.css', ['relative' => true, 'version' => 'auto']);
		    }
		} else {
			$wam->registerAndUseStyle('lne.' . $prefix . '_styles', 'mod_latestnewsenhanced/' . $prefix . '_styles.min.css', ['relative' => true, 'version' => 'auto']);
		}
	}

	/**
	 * Get the site mode
	 * @return string (dev|prod|adv)
	 */
	public static function getSiteMode($params)
	{
		return $params->get('site_mode', 'dev');
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
		return $params->get('clear_css_cache', 'true');
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

	/**
	 * Are white spaces removed ?
	 * @return boolean
	 */
	public static function isRemoveWhitespaces($params)
	{
		if (self::getSiteMode($params) == 'dev') {
			return false;
		}
		if (self::getSiteMode($params) == 'prod') {
			return true;
		}
		return $params->get('remove_whitespaces', false);
	}

}
?>