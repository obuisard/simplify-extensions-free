<?php
/**
 * @copyright    Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license        GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use Joomla\Component\Tags\Site\Helper\RouteHelper as TagsRouteHelper;
use Joomla\Registry\Registry;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;
use SYW\Module\TrombinoscopeContacts\Site\Helper\Helper;

/**
 * Renders a single contact card.
 *
 * Shared by the normal list (tmpl/default.php, which already has every
 * setting below as a local variable computed once for the whole list by the
 * Dispatcher) and the Ajax search (TrombinoscopeHelper::searchContactsAjax(),
 * which has no Dispatcher and so derives them via Helper::getCardSettings()).
 * No category/alphabet headers and no carousel wrapper here - those are
 * list-level concerns handled by the caller.
 */
extract($displayData);

/**
 * Extracted variables
 * --------------------
 * @var \stdClass            $item
 * @var Registry             $params
 * @var \stdClass            $module
 * @var array                $groups
 * @var int                  $i_header
 * @var string               $carousel_configuration
 * @var string               $format_style
 * @var bool                 $uppercase
 * @var string               $contact_link
 * @var string               $link_access
 * @var string               $generic_link
 * @var string               $link_label
 * @var int                  $bootstrap_version
 * @var string               $name_label
 * @var bool                 $name_tooltip
 * @var bool                 $link_name
 * @var bool                 $show_picture
 * @var bool                 $link_picture
 * @var string               $picture_hover_type
 * @var bool                 $picture_tooltip
 * @var bool                 $keep_picture_space
 * @var string               $photo_align
 * @var bool                 $crop_picture
 * @var int                  $picture_width
 * @var int                  $picture_height
 * @var bool                 $create_highres_images
 * @var bool                 $show_errors
 * @var bool                 $show_vcard
 * @var string               $vcard_icon
 * @var bool                 $show_featured
 * @var string               $featured_icon
 * @var bool                 $style_social_icons
 * @var bool                 $show_category
 * @var bool                 $link_to_category
 * @var bool                 $show_tags
 * @var bool                 $link_to_tags
 * @var array                $requested_links
 * @var array                $requested_infos
 * @var string               $extraclassesfields
 * @var string               $extraclasseslinkfields
 */

$i_header = $i_header ?? 0;
$carousel_configuration = $carousel_configuration ?? 'none'; // Ajax search results never use the carousel

/**
 * Joomla's "System - SEF" plugin normally root-prefixes bare relative image
 * paths (e.g. "images/foo.png" -> "/site-subfolder/images/foo.png") as a
 * full-page output buffer post-process. The Ajax search response bypasses
 * that whole page-rendering pipeline (com_ajax just echoes JSON), so this
 * card has to make its own image paths root-relative, otherwise they resolve
 * incorrectly once injected into the page. Paths already absolute/protocol-
 * relative/root-relative are left untouched.
 */
$makeImagePathAbsolute = function ($path) {
    if (empty($path) || strpos($path, '/') === 0 || preg_match('#^[a-z][a-z0-9+.-]*://#i', $path)) {
        return $path;
    }
    return \Joomla\CMS\Uri\Uri::root(true) . '/' . $path;
};

// Convert parameter fields to objects.
$itemparams = new Registry();
$itemparams->loadString($item->params);

// name format
$formatted_name = Helper::getFormattedName($item->name, $format_style, $uppercase);

// link
$link = '';
$link_attributes = '';
$link_classes = '';
if (in_array($link_access, $groups)) {
    switch ($contact_link) {
        case 'standard' :
            $link = Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language));
            break;
        case 'popup' :
            $link = Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language) . '&tmpl=component');
            $link_attributes = ' onclick="return false;" data-modaltitle="'.htmlspecialchars($formatted_name, ENT_COMPAT, 'UTF-8').'"';
            if ($bootstrap_version > 0) {
                $link_attributes .= ' data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'toggle="modal" data-' . ($bootstrap_version >= 5 ? 'bs-' : '') . 'target="#tcpmodal_'.$module->id.'"';
            }
            $link_classes = 'tcpmodal_'.$module->id;
            break;
        case 'linka' : $link_attributes = ' target="_blank"'; case 'linka_sw' : $link = $itemparams->get('linka'); break;
        case 'linkb' : $link_attributes = ' target="_blank"'; case 'linkb_sw' : $link = $itemparams->get('linkb');    break;
        case 'linkc' : $link_attributes = ' target="_blank"'; case 'linkc_sw' : $link = $itemparams->get('linkc');    break;
        case 'linkd' : $link_attributes = ' target="_blank"'; case 'linkd_sw' : $link = $itemparams->get('linkd');    break;
        case 'linke' : $link_attributes = ' target="_blank"'; case 'linke_sw' : $link = $itemparams->get('linke');    break;
        case 'generic' : $link_attributes = ' target="_blank"'; case 'generic_sw' : $link = $generic_link; break;
        default: $link = '';
    }
}

// Category link
$link_category = '';
if ($link_to_category) {
    $link_category = Route::_(ContactRouteHelper::getCategoryRoute($item->catid, $item->language));
}

$extraclasses = " personid-".$item->id." catid-".$item->catid;

if (isset($item->tags)) {
    foreach ($item->tags as $tag) {
        $extraclasses .= " tag-".$tag->id;
    }
}

$extraclasses .= ($i_header % 2) ? " even" : " odd";

if ($item->featured && $show_featured) {
    $extraclasses .= " featured";
}

if ($style_social_icons) {
    $extraclasses .= " social";
}

if (!$show_picture) {
    $extraclasses .= " text_only";
} else {
    $extraclasses .= " ";
    if (!$keep_picture_space && empty($item->image)) {
        $extraclasses .= "text_only ghost_";
    }
    switch ($photo_align) {
        case 'l': $extraclasses .= "picture_left"; break;
        case 'r': $extraclasses .= "picture_right"; break;
        case 't': $extraclasses .= "picture_top"; break;
        case 'lr': $extraclasses .= ($i_header % 2) ? "picture_right" : "picture_left"; break;
        case 'rl': $extraclasses .= ($i_header % 2) ? "picture_left" : "picture_right"; break;
        default : $extraclasses .= "picture_left";
    }
}
?>
<div class="person<?php echo $extraclasses ?>">
    <?php if ($carousel_configuration != 'none') : ?><div class="shell_animate"><?php endif; ?>
    <div class="shell">
        <div class="outerperson">

            <?php if ($show_errors && !empty($item->error)) : ?>
                <div class="alert <?php echo SYWUtilities::getBootstrapProperty('alert-error', $bootstrap_version); ?>">
                    <ul>
                    <?php foreach ($item->error as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($requested_links) : ?>
                <?php $links_output = ''; ?>
                <?php foreach ($requested_links as $index => $requested_link) : ?>
                    <?php $links_output .= Helper::renderLink($params, $item, $index, $requested_link, $extraclasseslinkfields); ?>
                <?php endforeach; ?>
                <?php if ($links_output) : ?>
                    <div class="iconlinks">
                        <ul><?php echo $links_output; ?></ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($show_vcard) : ?>
                <div class="vcard">
                    <a href="<?php echo Route::_(ContactRouteHelper::getContactRoute($item->slug, $item->catid, $item->language) . '&format=vcf'); ?>" class="hasTooltip<?php echo $extraclasseslinkfields ? ' ' . $extraclasseslinkfields : ''; ?>" aria-label="<?php echo Text::_('MOD_TROMBINOSCOPE_VCARD');?>" title="<?php echo Text::_('MOD_TROMBINOSCOPE_DOWNLOAD_VCARD');?>">
                        <i class="icon <?php echo $vcard_icon; ?>" aria-hidden="true"></i><span><?php echo Text::_('MOD_TROMBINOSCOPE_VCARD'); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <div class="innerperson">
                <div class="personpicture">

                    <?php if (isset($item->individual_bg) && $item->individual_bg) : ?>
                        <div class="individualbg">
                            <div class="innerindividualbg">
                                <?php echo SYWUtilities::getImageElement($makeImagePathAbsolute($item->individual_bg), $item->individual_bg_alt, null, ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_trombinoscope_' . $module->id)); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($picture_hover_type != 'none' && $link_picture && $link) : ?>
                        <div class="picture_animate <?php echo $picture_hover_type; ?>">
                    <?php endif; ?>

                    <div class="picture">

                        <div class="picture_veil">
                            <?php if ($item->featured && $show_featured) : ?>
                                <div class="feature">
                                    <i class="icon <?php echo $featured_icon; ?>" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($link_picture && $link) : ?>
                            <a href="<?php echo $link; ?>"<?php echo $link_attributes; ?><?php echo Helper::getTitleAttribute($formatted_name, $picture_tooltip) ?><?php echo Helper::getClassAttribute($picture_tooltip, $link_classes); ?>>
                        <?php endif; ?>
                        <?php if ($item->image) : ?>
                            <?php echo SYWUtilities::getImageElement($makeImagePathAbsolute($item->image), $formatted_name, $crop_picture ? array('width' => $picture_width, 'height' => $picture_height) : array(), ($carousel_configuration != 'none') ? false : true, $create_highres_images, null, true, SYWVersion::getMediaVersion('mod_trombinoscope_' . $module->id)); ?>
                        <?php else : ?>
                            <span class="nopicture">&nbsp;</span>
                        <?php endif; ?>
                        <?php if ($link_picture && $link) : ?>
                            </a>
                        <?php endif; ?>

                    </div>

                    <?php if ($picture_hover_type != 'none' && $link_picture && $link) : ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="personinfo">
                    <div class="text_veil">
                        <?php if ($item->featured && $show_featured) : ?>
                            <div class="feature">
                                <i class="icon <?php echo $featured_icon; ?>" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($show_category) : ?>
                        <div class="category">
                            <?php if ($link_category) : ?>
                                <a href="<?php echo $link_category; ?>" class="hasTooltip" title="<?php echo Text::_('JCATEGORY'); ?>">
                                        <span><?php echo htmlspecialchars($item->category, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                            <?php else : ?>
                                <span><?php echo htmlspecialchars($item->category, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_tags) : ?>
                        <?php if (isset($item->tags)) : ?>
                            <div class="tags">
                                <?php $tag_layout = new FileLayout('tcptag', JPATH_ROOT.'/modules/mod_trombinoscope/layouts'); // no overrides possible ?>
                                <?php foreach ($item->tags as $tag) :  ?>
                                    <?php
                                        $tag_data = array('tag' => $tag);
                                        if ($link_to_tags) {
                                            $tag_data['link'] = Route::_(TagsRouteHelper::getTagRoute($tag->id . ':' . $tag->alias));
                                        }
                                        $tag_data['bootstrap_version'] = $bootstrap_version;

                                        echo $tag_layout->render($tag_data);
                                    ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                        $title_attribute = Helper::getTitleAttribute($name_label, $name_tooltip);
                        if ($link_name && $link) {
                            $name_value = '<a href="'.$link.'"'.$link_attributes.$title_attribute.Helper::getClassAttribute($name_tooltip, $link_classes.' fieldvalue').' aria-label="'.$name_label.'"><span>'.htmlspecialchars($formatted_name, ENT_QUOTES, 'UTF-8').'</span></a>';
                        } else {
                            $name_value = '<span class="fieldvalue'.Helper::getTooltipClass($name_tooltip).'" aria-label="'.$name_label.'"'.$title_attribute.'>'.htmlspecialchars($formatted_name, ENT_QUOTES, 'UTF-8').'</span>';
                        }
                    ?>
                    <?php echo Helper::renderName($params, $name_value, $extraclassesfields); ?>

                    <?php if ($requested_infos) : ?>
                        <?php foreach ($requested_infos as $index => $requested_info) : ?>
                            <?php echo Helper::renderInfo($params, $item, $index, $requested_info, $extraclassesfields); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($link_label && $link) : ?>
                        <div class="personlinks">
                            <div class="personlink go">
                                <a href="<?php echo $link; ?>"<?php echo $link_attributes; ?><?php echo Helper::getClassAttribute(false, $link_classes); ?>>
                                    <?php if (Factory::getDocument()->getDirection() == 'rtl') : ?>
                                        <i class="icon SYWicon-arrow-left" aria-hidden="true"></i><span><?php echo $link_label; ?></span>
                                    <?php else : ?>
                                        <i class="icon SYWicon-arrow-right" aria-hidden="true"></i><span><?php echo $link_label; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($carousel_configuration != 'none') : ?></div><?php endif; ?>
</div>
