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

class Helper
{
	static $fsLoaded = false;

	static function getHtmlLinkTag($module, $url, $target, $text, $showTooltips = true, $popup_width = '600', $popup_height = '480')
	{
		$module_params = json_decode($module->params);

		$bootstrap_version = isset($module_params->bootstrap_version) ? $module_params->bootstrap_version : 'joomla';
		if ($bootstrap_version === 'joomla') {
			$bootstrap_version = version_compare(JVERSION, '4.0.0', 'lt') ? 2 : 4;
		} else {
			$bootstrap_version = intval($bootstrap_version);
		}

		return self::createHtmlLink($url, $target, $text, $module->id, $showTooltips, $popup_width, $popup_height, $bootstrap_version);
	}

	// for B/C
	static function createHtmlLink($url, $target, $text, $id = '', $showTooltips = true, $popup_width = '600', $popup_height = '480', $bootstrap_version = 2)
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




				// missing $bootstrap_version





				if ($bootstrap_version > 0) {
					$link_attributes .= ' data-toggle="modal" data-target="#trsmodal_'.$id.'"';
				}
				return '<a href="'.$url.'" class="trsmodal_'.$id.$extraClass.'"' . $link_attributes . '>'.$linkText.'</a>';
				break;
			default: // open in parent window
				return '<a'.$classAttribute.' href="'.$url.'"'.$titleAttribute.' rel="nofollow">'.$linkText.'</a>';
				break;
		}

		return '';
	}

	static function getImageList(&$params, $module_suffix, $image_list, $images_path) {

		$subdirectory = 'thumbnails/trs';
		if ($params->get('thumb_path', 'images') == 'cache') {
			$subdirectory = 'mod_trulyresponsiveslides';
		}
		$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'images'), $subdirectory);

		$list_images_array = array();

		foreach ($image_list as $image_item) {

			$imagename = File::getName($image_item);
			$imgfilename = $tmp_path.'/img_'.$module_suffix.'_'.$imagename;
			if (is_file(JPATH_ROOT.'/'.$imgfilename)) {
				$list_images_array[] = $imgfilename; // get re-created image that fits the slider
			} else {
				$list_images_array[] = $images_path.$image_item; // keep original
			}
		}

		return $list_images_array;
	}

	static function createImages(&$params, $module_suffix, $image_list, $images_path, $breakpoints = array())
	{
		$img_width = $params->get('img_w', 1024);
		$img_height = $params->get('img_h', 640);

		$clear_cache = $params->get('clear_cache', 0);
		if ($params->get('site_mode', 'adv') == 'dev') {
			$clear_cache = 1;
		} else if ($params->get('site_mode', 'adv') == 'prod') {
			$clear_cache = 0;
		}

		$subdirectory = 'thumbnails/trs';
		if ($params->get('thumb_path', 'images') == 'cache') {
			$subdirectory = 'mod_trulyresponsiveslides';
		}
		$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'images'), $subdirectory);

		$extensions = get_loaded_extensions();
		if (!in_array('gd', $extensions)) {
			return false;
		} else {
			foreach($image_list as $image_item) {
				$imageext = File::getExt($image_item);
				$imagename = File::getName($image_item);
				$imagenamenoext = File::stripExt($imagename);
				$imgfilename = $tmp_path.'/img_'.$module_suffix.'_'.$imagenamenoext;
				if (is_file(JPATH_ROOT.'/'.$imgfilename.'.'.$imageext) && !$clear_cache) { // image already exists
					// do nothing
				} else { // re-create the image if original image has a different size than the slider

					$image = new SYWImage($images_path.$image_item);

					if (is_null($image->getImagePath())) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_IMAGEFILEDOESNOTEXIST', $original_image_src);
						return false;
					} else if (is_null($image->getImageMimeType())) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_UNABLETOGETIMAGEPROPERTIES', $original_image_src);
						return false;
					} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_UNSUPPORTEDFILETYPE', $original_image_src);
						return false;
					} else {
						if ($image->getImageWidth() == $img_width && $image->getImageHeight() == $img_height && empty($breakpoints)) { // do not re-create the image to avoid loss of quality if sizes are the same
							if (is_file(JPATH_ROOT.'/'.$imgfilename.'.'.$imageext)) {
								File::delete(JPATH_ROOT.'/'.$imgfilename.'.'.$imageext); // remove potential thumbnail otherwise it will be used instead of the original
							}
						} else {
							switch ($imageext){
								case 'jpg': case 'jpeg': $quality = 100; break; // 0 to 100
								case 'png': $quality = 0; break; // compression: 0 to 9
								default : $quality = -1; break;
							}

							$image->createThumbnail($img_width, $img_height, true, $quality, null, $imgfilename.'.'.$imageext);
						}
					}

					$image->destroy();
				}
			}
		}

		return true;
	}

	static function createThumbnails(&$params, $module_suffix, $image_list, $images_path)
	{
		$crop_picture = $params->get('crop_pic', 0);
		$thumb_width = $params->get('thumb_w', 80);
		$thumb_height = $params->get('thumb_h', 60);

		$clear_cache = $params->get('clear_cache', 0);
		if ($params->get('site_mode', 'adv') == 'dev') {
			$clear_cache = 1;
		} else if ($params->get('site_mode', 'adv') == 'prod') {
			$clear_cache = 0;
		}

		$subdirectory = 'thumbnails/trs';
		if ($params->get('thumb_path', 'images') == 'cache') {
			$subdirectory = 'mod_trulyresponsiveslides';
		}
		$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'images'), $subdirectory);

		$quality_jpg = $params->get('quality_jpg', 100);
		$quality_png = $params->get('quality_png', 0);

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

		$extensions = get_loaded_extensions();
		if (!in_array('gd', $extensions)) {
			return false;
		} else {
			foreach($image_list as $image_item) {
				$imageext = File::getExt($image_item);
				$imagename = File::getName($image_item);
				$thumbfilename = $tmp_path.'/thumb_'.$module_suffix.'_'.$imagename;
				if (is_file(JPATH_ROOT.'/'.$thumbfilename) && !$clear_cache) { // thumbnail already exists
					// do nothing
				} else { // create the thumbnail

					$image = new SYWImage($images_path.$image_item);

					if (is_null($image->getImagePath())) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_IMAGEFILEDOESNOTEXIST', $original_image_src);
						return false;
					} else if (is_null($image->getImageMimeType())) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_UNABLETOGETIMAGEPROPERTIES', $original_image_src);
						return false;
					} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
						//$result[1] = Text::sprintf('MOD_LATESTNEWSENHANCED_ERROR_UNSUPPORTEDFILETYPE', $original_image_src);
						return false;
					} else {

						switch ($imageext){
							case 'jpg': case 'jpeg': $quality = $quality_jpg; break; // 0 to 100
							case 'png': $quality = $quality_png; break; // compression: 0 to 9
							default : $quality = -1; break;
						}

						$image->createThumbnail($thumb_width, $thumb_height, $crop_picture, $quality, null, $thumbfilename);
					}

					$image->destroy();
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
				$alt = $alts[$i];
			}

			$html .= '<li><img src="'.$image_directory.'/'.$item.'" alt="'.$alt.'" />'.$caption.'</li>';

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
			$html .= self::getDirection($params->get('direction', 'horizontal')).', ';
			$html .= self::getSlideshowSpeed($params->get('interval', 3000)).', ';
			$html .= self::getAnimationSpeed($params->get('speed', 1000)).', ';
			$html .= self::getPauseOnHover($params->get('pauseonslide', 1)).', ';
			$html .= self::getAutoStart($params->get('autostart', 1)).', ';
			$html .= 'prevText: "'.Text::_('JPREV').'", ';
			$html .= 'nextText: "'.Text::_('JNEXT').'", ';

			$html .= self::getRTL() ? self::getRTL().', ' : '';

			$html .= self::getRemoveLoading().', ';

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
		$subdirectory = 'thumbnails/trs';
		if ($params->get('thumb_path', 'images') == 'cache') {
			$subdirectory = 'mod_trulyresponsiveslides';
		}
		$tmp_path = SYWCache::getTmpPath($params->get('thumb_path', 'images'), $subdirectory);

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
				$alt = $alts[$i];
			}

			$html .= '<li data-thumb="'.$tmp_directory.'/thumb_'.$id_suffix.'_'.File::getName($item).'"><img src="'.$image_directory.'/'.$item.'" alt="'.$alt.'" />'.$caption.'</li>';

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
			$html .= self::getDirection($params->get('direction', 'horizontal')).', ';
			$html .= self::getSlideshowSpeed($params->get('interval', 3000)).', ';
			$html .= self::getAnimationSpeed($params->get('speed', 1000)).', ';
			$html .= self::getPauseOnHover($params->get('pauseonslide', 1)).', ';
			$html .= self::getAutoStart($params->get('autostart', 1)).', ';
			$html .= 'controlNav: "thumbnails", ';
			$html .= 'prevText: "'.Text::_('JPREV').'", ';
			$html .= 'nextText: "'.Text::_('JNEXT').'", ';

			$html .= self::getRTL() ? self::getRTL().', ' : '';

			$html .= self::getRemoveLoading().', ';

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
		return 'animation: "'.$type.'"';
	}

	static protected function getAnimationLoop($loop) {
		$animation_loop = ($loop) ? 'true' : 'false';
		return 'animationLoop: '.$animation_loop;
	}

	static protected function getDirection($direction) {
		return 'direction: "'.$direction.'"';
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
		return "start: function(slider) { $('body').removeClass('loading'); }";
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

		$minified = (JDEBUG) ? '' : '-min';

		$doc = Factory::getDocument();

		$doc->addScriptVersion(Uri::root(true).'/media/mod_trulyresponsiveslides/js/jquery.flexslider' . $minified . '.js');

		if (JDEBUG) {
			$doc->addStyleSheet(Uri::root(true).'/media/mod_trulyresponsiveslides/css/flexslider.css');
			if (Factory::getDocument()->getDirection() == 'rtl') {
				$doc->addStyleSheet(Uri::root(true).'/media/mod_trulyresponsiveslides/css/flexslider-rtl.css');
			}
		} else {
			if (Factory::getDocument()->getDirection() != 'rtl') {
				$doc->addStyleSheetVersion(Uri::root(true).'/media/mod_trulyresponsiveslides/css/flexslider-min.css');
			} else {
				$doc->addStyleSheetVersion(Uri::root(true).'/media/mod_trulyresponsiveslides/css/flexslider-pack-min.css'); // regular stylesheet + RTL
			}
		}

		self::$fsLoaded = true;
	}

}
