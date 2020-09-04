<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use SYW\Library\Fonts as SYWFonts;

$value = $field->value;

if ($value == '') {
	return;
}

$icon_prefix = 'SYW';
$load_icomoon = false;

if (strpos($value, 'icomoon') !== false) {
    $value = str_replace('icomoon-', '', $value);
    $icon_prefix = '';
    $load_icomoon = true;
}

SYWFonts::loadIconFont(!$load_icomoon, $load_icomoon); // LOADS even when not supposed to show 'display' = 0

echo '<i class="'.$icon_prefix.'icon-'.$value.'"></i>';