<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use SYW\Library\Libraries as SYWLibraries;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Module\TrulyResponsiveSlides\Site\Cache\CSSFileCache;
use SYW\Module\TrulyResponsiveSlides\Site\Cache\JSAnimationFileCache;
use SYW\Module\TrulyResponsiveSlides\Site\Helper\Helper;

	if ($layout != 'articles') {
		$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_WRONGLAYOUT'), 'error');
	} elseif (empty($list)) {
		$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_NOSLIDE'), 'warning');
	} else {

		$thumb_width_percent = '';

		$list_images = array();
		$list_alts = array();
		$list_captions = array();

        $app = Factory::getApplication();
        $wam = $app->getDocument()->getWebAssetManager();

		// Get the global parameters
        $globalparams = ComponentHelper::getParams('com_content');

        $default_bg_picture = $params->get('default_bg', '');
        if ($default_bg_picture) {
            $default_image_object = HTMLHelper::cleanImageURL($default_bg_picture);
            $default_bg_picture = $default_image_object->url;
        }

		$modal_needed = false;

		foreach ($list as $item) {

			$image_fulltext_path = '';
			if (!array_key_exists('image_fulltext', $item->images)) {
				if (empty($default_bg_picture)) {
					// error: full text image missing
					$errors[] = array(Text::sprintf('MOD_TRULYRESPONSIVESLIDER_ERROR_FULLTEXTIMAGEMISSING', $item->title), 'warning');
					continue;
				} else {
					$image_fulltext_path = $default_bg_picture;
					$list_alts[] = '';
				}
			} else {
				$image_fulltext_path = trim($item->images['image_fulltext']);
				$list_alts[] = trim($item->images['image_fulltext_alt']);
				if (empty($image_fulltext_path)) {
					$image_fulltext_path = $default_bg_picture;
				}
			}

			if (empty($image_fulltext_path)) {
				// error: full text image missing
				$errors[] = array(Text::sprintf('MOD_TRULYRESPONSIVESLIDER_ERROR_FULLTEXTIMAGEMISSING', $item->title), 'warning');
				continue;
			}

			if (substr_count($image_fulltext_path, 'http') <= 0 && !File::exists($image_fulltext_path)) {
				// error: file does not exist
				$errors[] = array(Text::sprintf('MOD_TRULYRESPONSIVESLIDER_ERROR_FILEDOESNOTEXIST', $image_fulltext_path, $item->title), 'error');
				continue;
			}

			$list_images[] = $image_fulltext_path;

			// links

			$links = '';
			if ($item->urls) {

				if (!empty($item->urls['urla']) || !empty($item->urls['urlb']) || !empty($item->urls['urlc'])) {

					$links .= '<ul>';

					if (!empty($item->urls['urla'])) {
						$target = $item->urls['targeta'];
						if ($item->urls['targeta'] == '') {
							$target = $globalparams->get('targeta');
						}
						$links .= '<li>';
						$links .= Helper::getHtmlLinkTag($module, $item->urls['urla'], $target, $item->urls['urlatext'], false, $popup_width, $popup_height);
						$links .= '</li>';
						if ($target == 3) {
						    $modal_needed = true;
						}
					}
					if (!empty($item->urls['urlb'])) {
						$target = $item->urls['targetb'];
						if ($item->urls['targetb'] == '') {
							$target = $globalparams->get('targetb');
						}
						$links .= '<li>';
						$links .= Helper::getHtmlLinkTag($module, $item->urls['urlb'], $target, $item->urls['urlbtext'], false, $popup_width, $popup_height);
						$links .= '</li>';
						if ($target == 3) {
						    $modal_needed = true;
						}
					}
					if (!empty($item->urls['urlc'])) {
						$target = $item->urls['targetc'];
						if ($item->urls['targetc'] == '') {
							$target = $globalparams->get('targetc');
						}
						$links .= '<li>';
						$links .= Helper::getHtmlLinkTag($module, $item->urls['urlc'], $target, $item->urls['urlctext'], false, $popup_width, $popup_height);
						$links .= '</li>';
						if ($target == 3) {
						    $modal_needed = true;
						}
					}

					$links .= '</ul>';
				}
			}

            // category and title

            $caption_category = '';
            $caption_title = '';

            $itemparams = new Registry();
            $itemparams->loadString($item->attribs);

            if (($itemparams->get("show_category") == '' && $globalparams->get('show_category')) || $itemparams->get("show_category") == 1) {
                $category_html_tag = $params->get('cat_tag', '3');
                $caption_category = '<h'.$category_html_tag.' class="caption_category">'.$item->category_title.'</h'.$category_html_tag.'>';
            }

            if (($itemparams->get("show_title") == '' && $globalparams->get('show_title')) || $itemparams->get("show_title") == 1) {
                $title_html_tag = $params->get('title_tag', '2');
                $caption_title = '<h'.$title_html_tag.' class="caption_title">'.$item->title.'</h'.$title_html_tag.'>';
            }

            /* caption, position */

			$coordinate = $default_position;
            if (!empty($item->images['image_fulltext_caption'])) {
            	$image_fulltext_caption = strtolower(trim($item->images['image_fulltext_caption']));
            	if (!empty($image_fulltext_caption)) {
            		$coordinate = $image_fulltext_caption;
            	}
            }

			// get the content and format it

            Helper::parseCustomFields($item->introtext, $item);
            Helper::parseCustomFields($item->fulltext, $item);

            $introtext = HTMLHelper::_('content.prepare', trim($item->introtext));
            $fulltext = HTMLHelper::_('content.prepare', trim($item->fulltext));

            // TODO trim the tags to make sure there is actual content?

            $caption_html_content = '';

            if (!empty($fulltext)) {
                if (strpos($coordinate, 'w') !== false || $coordinate == 'n' || $coordinate == 's' || $coordinate == 'c' || $coordinate == 'cc') {
                    $caption_html_content = '<div class="caption_left">';
                    $caption_html_content .= $caption_category.$caption_title;
                    $caption_html_content .= '<div class="caption_content">'.$introtext.'</div>';
                    if (!empty($links)) {
                        $caption_html_content .= '<div class="caption_links">'.$links.'</div>';
                    }
                    $caption_html_content .= '</div>';
                    $caption_html_content .= '<div class="caption_right">';
                    $caption_html_content .= '<div class="caption_content">'.$fulltext.'</div>';
                    $caption_html_content .= '</div>';
                } else {
                    $caption_html_content = '<div class="caption_left">';
                    $caption_html_content .= '<div class="caption_content">'.$introtext.'</div>';
                    $caption_html_content .= '</div>';
                    $caption_html_content .= '<div class="caption_right">';
                    $caption_html_content .= $caption_category.$caption_title;
                    $caption_html_content .= '<div class="caption_content">'.$fulltext.'</div>';
                    if (!empty($links)) {
                        $caption_html_content .= '<div class="caption_links">'.$links.'</div>';
                    }
                    $caption_html_content .= '</div>';
                }
                $coordinate .= ' two-column';
            } else {
            	$caption_html_content = $caption_category.$caption_title;
            	if (!empty($introtext)) {
            		$caption_html_content .= '<div class="caption_content">'.$introtext.'</div>';
            	}
            	if (!empty($links)) {
            		$caption_html_content .= '<div class="caption_links">'.$links.'</div>';
            	}
            }

            if (empty($caption_html_content)) {
            	$item->text = '';
            } else {
            	$item->text = '<div class="innercaption coordinate-'.$coordinate.' complex_caption">'.$caption_html_content.'</div>';

            	// will trigger events from plugins
            	$app->triggerEvent('onContentPrepare', array('com_content.slide', &$item, &$params, 0));
            }

            $list_captions[] = $item->text;
		}

        $result = Helper::createImages($params, $class_suffix, $list_images, '');
        if (!$result) {
        	$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_NOIMAGESCREATED'), 'warning');
        }

        $list_images = Helper::getImageList($params, $class_suffix, $list_images, '');

		if ($animation == 'withthumb') {

			$result = Helper::createThumbnails($params, $class_suffix, $list_images, '');
			if (!$result) {
				$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_NOTHUMBNAILSCREATED'), 'warning');
			}

			// set number of thumbs images per line when not in a carousel -> % of space taken
			if (!empty($max_width)) {
				$thumb_width_percent = 100 / floor(floatval($max_width) / floatval($thumb_width));
			}
		}

		$params->set('thumb_width_percent', $thumb_width_percent);

		// caching the stylesheet

		$cache_css = new CSSFileCache('mod_trulyresponsiveslides', $params);
		if (!empty($style_overrides)) {
			$cache_css->addDeclaration($style_overrides);
		}
		$result = $cache_css->cache('style_'.$module->id.'.css', $clear_header_files_cache);

		if ($result) {
			$wam->registerAndUseStyle('trs.style_' . $module->id, $cache_css->getCachePath() . '/style_' . $module->id . '.css');
		}

		// caching the scripts

		$extra_class = ' ';
		$scriptDeclaration = 'jQuery(document).ready(function($) {';

		switch ($animation) {
			case 'withthumb':
				$extra_class .= 'auto_thumbs';
				$scriptDeclaration .= Helper::getSliderWithThumbJavascript($params, $class_suffix);
				break;
			default:
				$extra_class .= 'basic';
				$scriptDeclaration .=  Helper::getBasicSliderJavascript($params, $class_suffix);
				break;
		}

		$scriptDeclaration .= '});';

		// create javascript file

		$cache_js = new JSAnimationFileCache('mod_trulyresponsiveslides', $params);
		$cache_js->addDeclaration($scriptDeclaration, 'js');

		if ($inline_scripts) {
			$wam->addInlineScript($cache_js->getBuffer(true, true));
		} else {
			$result = $cache_js->cache('animation_'.$module->id.'.js', $clear_header_files_cache);

			if ($result) {
				$wam->registerAndUseScript('trs.animation_' . $module->id, $cache_js->getCachePath() . '/animation_' . $module->id . '.js', [], ['defer' => true]);
			}
		}
	}
?>

<?php if ($show_errors && !empty($errors)) : ?>
	<?php foreach ($errors as $error) : ?>
		<div class="<?php echo SYWUtilities::getBootstrapProperty('alert alert-'.$error[1], $bootstrap_version); ?>">
			<?php echo $error[0]; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ($layout == 'articles' && !empty($list) && empty($errors)) : ?>
	<div id="trs_<?php echo $class_suffix; ?>"<?php if ($isMobile) : ?> class="mobile"<?php endif; ?>>
		<?php if (trim($params->get('pretext', ''))) : ?>
			<div class="pretext">
				<?php if ($params->get('allow_plugins_prepost', 0)) : ?>
					<?php echo HTMLHelper::_('content.prepare', $params->get('pretext')); ?>
				<?php else : ?>
					<?php echo $params->get('pretext'); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="flexslidercontainer<?php echo $extra_class; ?>">
			<?php if ($animation == 'withthumb') : ?>
				<?php echo Helper::getSliderWithThumbHtml($params, $list_images, $list_alts, $list_captions, null, Uri::root(true), $class_suffix); ?>
			<?php else : ?>
				<?php echo Helper::getBasicSliderHtml($params, $list_images, $list_alts, $list_captions, null, Uri::root(true), $class_suffix); ?>
		<?php endif; ?>
		</div>
		<?php if ($params->get('out_captions', 0)) : ?>
			<?php echo Helper::getOutCaptionsHtml($params, count($list_images), $list_captions, null, $class_suffix); ?>
		<?php endif; ?>
		<?php if (trim($params->get('posttext', ''))) : ?>
			<div class="posttext">
				<?php if ($params->get('allow_plugins_prepost', 0)) : ?>
					<?php echo HTMLHelper::_('content.prepare', $params->get('posttext')); ?>
				<?php else : ?>
					<?php echo $params->get('posttext'); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php
			if ($modal_needed) {
				if ($bootstrap_version == 0) {
					SYWLibraries::loadPureModal($load_remotely);
				}

                $layout = new FileLayout('trsmodal', JPATH_ROOT.'/modules/mod_trulyresponsiveslides/layouts'); // no possible overrides

                $data = array('selector' => 'trsmodal_'.$module->id, 'width' => $popup_width, 'height' => $popup_height);
            	$data['bootstrap_version'] = $bootstrap_version;
            	$data['load_bootstrap'] = $load_bootstrap;

            	echo $layout->render($data);
            }
        ?>
	</div>
<?php endif; ?>
