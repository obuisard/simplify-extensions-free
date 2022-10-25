<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Version as SYWVersion;

if (!SYWVersion::isCompatible('2.1.0')) {
	echo '<span>incompatible extensions library</span>';
} else {
	echo '<span>compatible extensions library</span>';
}

// font tests

SYWFonts::addFontFace('Conduit ITC Light', '../../media/mod_library_test/fonts/conditcl-webfont', 'normal', 'normal', ['eot', 'svg', 'ttf', 'woff']);

Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle('body { font-family: "Conduit ITC Light", sans-serif; }');

require(ModuleHelper::getLayoutPath('mod_library_test', $params->get('layout', 'default')));
?>
