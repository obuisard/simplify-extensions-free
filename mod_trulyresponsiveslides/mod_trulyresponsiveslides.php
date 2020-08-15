<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Fonts as SYWFonts;
use SYW\Library\K2 as SYWK2;
use SYW\Library\Utilities as SYWUtilities;
use SYW\Module\TrulyResponsiveSlides\Site\Helper\ArticlesHelper;
use SYW\Module\TrulyResponsiveSlides\Site\Helper\Helper;
use SYW\Module\TrulyResponsiveSlides\Site\Helper\ImagesHelper;
use SYW\Module\TrulyResponsiveSlides\Site\Helper\K2ItemsHelper;

$isMobile = SYWUtilities::isMobile();

$show_on_mobile = $params->get('show_on_mobile', 1);
if (($isMobile && $show_on_mobile == 0) || (!$isMobile && $show_on_mobile == 2)) {
	return;
}

$list = null;

$layout = $params->get('layout');
switch ($layout) {
	case 'k2':
		if (SYWK2::exists()) {
			$list = K2ItemsHelper::getItems($params, $module);
		}
		break;
	case 'articles':
		$list = ArticlesHelper::getItems($params, $module);
		break;
	default:
		$list = ImagesHelper::getItems($params, $module);
		break;
}

$doc = Factory::getDocument();

$urlPath = Uri::base().'modules/mod_trulyresponsiveslides/';

$class_suffix = $module->id;
$params->set('suffix', $class_suffix);

$bootstrap_version = $params->get('bootstrap_version', 'joomla');
$load_bootstrap = false;
if ($bootstrap_version === 'joomla') {
    $bootstrap_version = version_compare(JVERSION, '4.0.0', 'lt') ? 2 : 4;
    $load_bootstrap = true;
} else {
	$bootstrap_version = intval($bootstrap_version);
}

$params->set('bootstrap_version', $bootstrap_version); // for use in js and css cached files

$load_remotely = $params->get('remote_libraries', 0);

// common parameters

$max_width = $params->get('max_w', '');

$img_width = $params->get('img_w', 900);
$img_height = $params->get('img_h', 600);

$thumb_width = $params->get('thumb_w', 80);
$thumb_height = $params->get('thumb_h', 60);

$default_position = $params->get('default_position', 's');

$animation = $params->get('animation', 'basic');

$popup_width = $params->get('popup_x', '600');
$popup_height = $params->get('popup_y', '480');

// style overrides

$style_overrides = trim($params->get('style_overrides', ''));
if (!empty($style_overrides)) {
	$style_overrides = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $style_overrides); // minify the CSS code
}

// advanced parameters

$show_errors = $params->get('show_errors', 0);
if ($params->get('site_mode', 'adv') == 'dev') {
	$show_errors = 1;
} else if ($params->get('site_mode', 'adv') == 'prod') {
	$show_errors = 0;
}

$clear_header_files_cache = $params->get('clear_header_files_cache', 1);
if ($params->get('site_mode', 'adv') == 'dev') {
	$clear_header_files_cache = 1;
} else if ($params->get('site_mode', 'adv') == 'prod') {
	$clear_header_files_cache = 0;
}

$inline_scripts = $params->get('inline_scripts', 0);

// loading of libraries

HTMLHelper::_('jquery.framework');
SYWFonts::loadIconFont();

Helper::load_flexslider($load_remotely);

require(ModuleHelper::getLayoutPath('mod_trulyresponsiveslides', $layout));
?>
