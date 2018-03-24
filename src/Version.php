<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

/**
 * Version information class for the SYW Library
 */
class Version
{
	/** @var  string  Product name. */
	static $PRODUCT = 'SimplifyYourWeb Extensions Library';

	/** @var  string  Release version. */
	static $RELEASE = '2.0.0';

	/** @var  string  Release date. */
	static $RELDATE = '21-Jan-2018';

	/** @var  string  Copyright Notice. */
	static $COPYRIGHT = 'Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.';

	/** @var  string  Link text. */
	static $URL = '<a href="http://www.simplifyyourweb.com">SimplifyYourWeb.com</a>.';

	/**
	 * Compares two a "PHP standardized" version number against the current library version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 * @return  bool    True if the version is compatible.
	 * @see     http://www.php.net/version_compare
	 */
	static function isCompatible($minimum)
	{
		return version_compare(self::$RELEASE, $minimum, 'ge');
	}

	/**
	 * Gets a "PHP standardized" version string for the current library.
	 *
	 * @return  string  Version string.
	 */
	static function getVersion()
	{
		return self::$RELEASE;
	}
	
}
