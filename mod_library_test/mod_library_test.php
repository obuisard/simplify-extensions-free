<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Library\Version as SYWVersion;

if (!SYWVersion::isCompatible('2.1.0')) {
	echo '<p>incompatible extensions library</p>';
} else {
	echo '<p>compatible extensions library</p>';
}

// mobile test

$isMobile = SYWUtilities::isMobile();
$isTablet = SYWUtilities::isTablet();
$mobileDetectVersion = SYWUtilities::getMobileDetectVersion();

echo '<p>Is mobile: ' . ($isMobile ? 'yes' : 'no') . ' - Is tablet: ' . ($isTablet ? 'yes' : 'no') . ' - Mobile Detect version: <code>' . $mobileDetectVersion . '</code></p>';


// font tests

$font = $params->get('font', '');
$font2 = $params->get('font2', '');
$weights = $params->get('fontweight', '');
$weights2 = $params->get('fontweight2', '');

$web_fonts_array = [];

if (!empty($font)) {
    $web_fonts = SYWFonts::getWebfontsFromFamily($font);
    if (count($web_fonts) > 0) {
        foreach ($web_fonts as $web_font) {
            $web_fonts_array[] = ['name' => $web_font, 'weights' => (!empty($weights) ? $weights : [])];
        }
    }
}

if (!empty($font2)) {
    $web_fonts = SYWFonts::getWebfontsFromFamily($font2);
    if (count($web_fonts) > 0) {
        foreach ($web_fonts as $web_font) {
            $web_fonts_array[] = ['name' => $web_font, 'weights' => (!empty($weights2) ? $weights2 : [])];
        }
    }
}

if (count($web_fonts_array) > 0) {
    SYWFonts::loadWebFonts($web_fonts_array);
    if (!empty($font)) {
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle('h2 { font-family: ' . $font . '; }');
    }
    if (!empty($font2)) {
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle('h3 { font-family: ' . $font2 . '; }');
    }
}

SYWFonts::addFontFace('Conduit ITC Light', '../../media/mod_library_test/fonts/conditcl-webfont', 'normal', 'normal', ['eot', 'svg', 'ttf', 'woff']);

Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle('body { font-family: "Conduit ITC Light", sans-serif; }');

require(ModuleHelper::getLayoutPath('mod_library_test', $params->get('layout', 'default')));
?>
