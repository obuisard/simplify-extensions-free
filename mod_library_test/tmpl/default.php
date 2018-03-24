<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
 
defined('_JEXEC') or die;

use SYW\Library\Version as SYWVersion;

if (!SYWVersion::isCompatible('2.0.0')) {
	echo 'incompatible';
} else {
	echo 'compatible';
}
?>
