<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemSYW extends CMSPlugin
{
	function onAfterInitialise()
	{
		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src')) {
			\JLoader::registerNamespace('SYW\\Library', JPATH_LIBRARIES.'/syw/src', false, false, 'psr4');
		}

		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src/Field')) {
			\JLoader::registerNamespace('SYW\\Library\\Field', JPATH_LIBRARIES.'/syw/src/Field', false, false, 'psr4');
		}

		if (Folder::exists(JPATH_ROOT.'/libraries/syw/src/Vendor')) {
			\JLoader::registerNamespace('SYW\\Library\\Vendor', JPATH_LIBRARIES.'/syw/src/Vendor', false, false, 'psr4');
		}
	}

}