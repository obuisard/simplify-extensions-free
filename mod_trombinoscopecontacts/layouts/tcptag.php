<?php
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

$bootstrapVersion = isset($displayData['bootstrap_version']) ? intval($displayData['bootstrap_version']) : 2;

$tag = $displayData['tag'];
$link = isset($displayData['link']) ? $displayData['link'] : '';
$onclick = isset($displayData['onclick']) ? $displayData['onclick'] : '';

$tagParams = new Registry($tag->params);
$default_classes = 'label label-info';
if ($bootstrapVersion >= 4) {
	$default_classes = 'badge badge-info';
}
$tag_class = $tagParams->get('tag_link_class', $default_classes);

if (Factory::getLanguage()->hasKey($tag->title)) {
	$tag->title = Text::_($tag->title);
}
?>
<span class="tag tag-<?php echo $tag->id; ?> <?php echo $tag_class; ?>">
	<?php if ($link) : ?>
		<a href="<?php echo $link; ?>"><span><?php echo $this->escape($tag->title); ?></span></a>
	<?php elseif ($onclick) : ?>
		<a href="" onclick="<?php echo $onclick; ?>"><span><?php echo $this->escape($tag->title); ?></span></a>
	<?php else : ?>
		<span><?php echo $this->escape($tag->title); ?></span>
	<?php endif; ?>
</span>