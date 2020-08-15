<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Module\TrulyResponsiveSlides\Site\Cache\CSSFileCache;
use SYW\Module\TrulyResponsiveSlides\Site\Cache\JSAnimationFileCache;
use SYW\Module\TrulyResponsiveSlides\Site\Helper;

	if ($layout != 'k2') {
		$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_WRONGLAYOUT'), 'error');
	} elseif (empty($list)) {
		$errors[] = array(Text::_('MOD_TRULYRESPONSIVESLIDER_ERROR_NOSLIDE'), 'warning');
	} else {

		$thumb_width_percent = '';

		$list_images = array();
		$list_alts = array();
		$list_captions = array();

        $app = Factory::getApplication();

        $default_bg_picture = $params->get('default_bg', '');

		foreach ($list as $item) {

			$image_path = trim($item->image);

			if (empty($image_path)) {
				$image_path = $default_bg_picture;
			}

			if (empty($image_path)) {
				// error: image missing
				$errors[] = array(Text::sprintf('MOD_TRULYRESPONSIVESLIDER_ERROR_IMAGEMISSING', $item->title), 'warning');
				continue;
			}

			if (substr_count($image_path, 'http') <= 0 && !File::exists($image_path)) {
				// error: file does not exist
				$errors[] = array(Text::sprintf('MOD_TRULYRESPONSIVESLIDER_ERROR_FILEDOESNOTEXIST', $image_path, $item->title), 'error');
				continue;
			}

			$list_images[] = $image_path;
			$list_alts[] = '';

			// links

			$links = ''; // no links for k2

            /* category and title */

            $caption_category = '';
            $caption_title = '';

            $itemparams = new Registry();
            $itemparams->loadString($item->params);

            // Get the global parameters from category
            $db = Factory::getDBO();
            $category = Table::getInstance('K2Category', 'Table');
            $category->load($item->cat_id);
            $globalparams = new Registry($category->params); // JComponentHelper::getParams('com_k2');

            if (($itemparams->get("itemCategory") == '' && $globalparams->get('itemCategory')) || $itemparams->get("itemCategory") == 1) {
            	$category_html_tag = $params->get('cat_tag', '3');
            	$caption_category = '<h'.$category_html_tag.' class="caption_category">'.$item->category_title.'</h'.$category_html_tag.'>';
            }

            if (($itemparams->get("itemTitle") == '' && $globalparams->get('itemTitle')) || $itemparams->get("itemTitle") == 1) {
            	$title_html_tag = $params->get('title_tag', '2');
            	$caption_title = '<h'.$title_html_tag.' class="caption_title">'.$item->title.'</h'.$title_html_tag.'>';
            }

            /* caption, position */

            $coordinate = strtolower(trim($item->image_caption));
            if (empty($coordinate)) {
                $coordinate = $default_position;
            }

            // get the content and format it
            $introtext = trim($item->introtext);
            $fulltext = trim($item->fulltext);

            // TODO trim the tags to make sure there is actual content?

            $caption_html_content = '';

            if (!empty($fulltext)) {
                if (strpos($coordinate, 'w') !== false || $coordinate == 'n' || $coordinate == 's' || $coordinate == 'c') {
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
			$doc->addStyleSheet(Uri::base(true).'/media/cache/mod_trulyresponsiveslides/style_'.$module->id.'.css');
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
				$scriptDeclaration .= Helper::getBasicSliderJavascript($params, $class_suffix);
				break;
		}

		$scriptDeclaration .= '});';

		// create javascript file

		$cache_js = new JSAnimationFileCache('mod_trulyresponsiveslides', $params);
		$cache_js->addDeclaration($scriptDeclaration, 'js');

		if ($inline_scripts) {
			$doc->addScriptDeclaration($cache_js->getBuffer(true));
		} else {
			$result = $cache_js->cache('animation_'.$module->id.'.js', $clear_header_files_cache);

			if ($result) {
				$doc->addScript(Uri::base(true).'/media/cache/mod_trulyresponsiveslides/animation_'.$module->id.'.js');
			}
		}
	}
?>

<?php if ($show_errors && !empty($errors)) : ?>
	<?php foreach ($errors as $error) : ?>
		<div class="alert <?php echo SYWUtilities::getBootstrapProperty('alert-'.$error[1], $bootstrap_version); ?>">
			<?php echo $error[0]; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ($layout == 'k2' && !empty($list) && empty($errors)) : ?>
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
	</div>
<?php endif; ?>
