<?php
/**
 * Layout for rendering the article title in Latest News Enhanced
 * Variables expected in $displayData:
 * - item: the article item object
 * - show_title: bool
 * - link_title: bool
 * - title_html_tag: string (e.g. 2 for h2)
 * - title_class: string
 * - link: string (URL)
 * - link_edit: string (URL)
 * - checked_out: int
 * - user_id: int
 * - link_tooltip: bool
 * - follow: string
 * - popup_width: int
 * - popup_height: int
 * - LNEHelper: helper class
 * - Text: Joomla Text class
 * - Factory: Joomla Factory class
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use SYW\Module\LatestNewsEnhanced\Site\Helper;

$item = $displayData['item'];
$show_title = $displayData['show_title'];
$link_title = $displayData['link_title'];
$title_html_tag = $displayData['title_html_tag'];
$title_class = $displayData['title_class'];
$link = $displayData['link'];
$link_edit = $displayData['link_edit'] ?? null;
$checked_out = $displayData['checked_out'] ?? 0;
$user_id = $displayData['user_id'] ?? 0;
$link_tooltip = $displayData['link_tooltip'] ?? false;
$follow = $displayData['follow'] ?? '';
$popup_width = $displayData['popup_width'] ?? 0;
$popup_height = $displayData['popup_height'] ?? 0;

?>
<?php if ($show_title) : ?>
    <h<?php echo $title_html_tag; ?> class="newstitle<?php echo $title_class; ?>">
    <?php if ($link_title && $link) : ?>
        <?php echo Helper::getHtmlATag($displayData['module'], $item, $follow, $link_tooltip, $popup_width, $popup_height); ?>
            <span><?php echo $item->title; ?></span>
        </a>
    <?php else : ?>
        <span><?php echo $item->title; ?></span>
    <?php endif; ?>
    <?php if ($link_edit) : ?>
        <?php if ($checked_out > 0 && $checked_out != $user_id) : ?>
            <?php $checkoutUser = Factory::getUser($checked_out); ?>
            <span class="checked_out hasTooltip" title="<?php echo Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_CHECKED_OUT_BY', $checkoutUser->name); ?>"><i class="SYWicon-lock"></i></span>
        <?php else : ?>
            <a href="<?php echo $link_edit; ?>" class="edit hasTooltip" title="<?php echo Text::_('JGLOBAL_EDIT'); ?>"><i class="SYWicon-create"></i></a>
        <?php endif; ?>
    <?php endif; ?>
    </h<?php echo $title_html_tag; ?>>
<?php else : ?>
    <?php if ($link_edit) : ?>
        <h<?php echo $title_html_tag; ?> class="newstitle<?php echo $title_class; ?>">
        <?php if ($checked_out > 0 && $checked_out != $user_id) : ?>
            <?php $checkoutUser = Factory::getUser($checked_out); ?>
            <span class="checked_out hasTooltip" title="<?php echo Text::sprintf('MOD_LATESTNEWSENHANCEDEXTENDED_CHECKED_OUT_BY', $checkoutUser->name); ?>"><i class="SYWicon-lock"></i></span>
        <?php else : ?>
            <a href="<?php echo $link_edit; ?>" class="edit hasTooltip" title="<?php echo Text::_('JGLOBAL_EDIT'); ?>"><i class="SYWicon-create"></i></a>
        <?php endif; ?>
        </h<?php echo $title_html_tag; ?>>
    <?php endif; ?>
<?php endif; ?>
