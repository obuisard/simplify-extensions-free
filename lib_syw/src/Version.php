<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Helper\LibraryHelper;

/**
 * Version information class for the SYW Library
 */
class Version
{
	/** @var  string  Product name. */
    const PRODUCT = 'SimplifyYourWeb Extensions Library';

	/** @var  string  Release version. */
    const RELEASE = '2.6.3';

	/** @var  string  Release date. */
    const RELDATE = '29-Apr-2024';

	/** @var  string  Copyright Notice. */
    const COPYRIGHT = 'Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.';

	/** @var  string  Link text. */
    const URL = '<a href="https://simplifyyourweb.com">SimplifyYourWeb.com</a>.';

    /**
     * the media version for each extension used
     * @var array
     */
    static $extensions_version = null;

	/**
	 * Compares two a "PHP standardized" version number against the current library version.
	 *
	 * @param   string  $minimum  The minimum version of the library which is compatible.
	 * @return  bool    True if the version is compatible.
	 * @see     http://www.php.net/version_compare
	 */
	public static function isCompatible($minimum)
	{
		return version_compare(self::RELEASE, $minimum, 'ge');
	}

	/**
	 * Gets a "PHP standardized" version string for the current library.
	 *
	 * @return  string  Version string.
	 */
	public static function getVersion()
	{
		return self::RELEASE;
	}

	/**
	 * Generate a media version string for assets
	 *
	 * @param string the extension to create the version for
	 * @return  string
	 */
    protected static function generateMediaVersion(string $extension)
	{
	    return md5($extension . (new Date)->toSql());
	}

	/**
	 * Gets a media version which is used to append to extension's media files.
	 *
	 * @param string the extension to create the version for
	 * @return  string  The media version
	 */
	public static function getMediaVersion(string $extension)
	{
	    if (self::$extensions_version === null) {
	        self::$extensions_version = json_decode(LibraryHelper::getParams('syw')->get('mediaversions', ''), true);
	    }

	    if (!isset(self::$extensions_version[$extension]) || JDEBUG) {
            self::setMediaVersion($extension, self::generateMediaVersion($extension));
	    }

        return self::$extensions_version[$extension];
	}

	/**
	 * Function to refresh the media version
	 *
	 * @param string the extension to create the version for
	 */
	public static function refreshMediaVersion(string $extension)
	{
	    self::setMediaVersion($extension, self::generateMediaVersion($extension));
	}

	/**
	 * Sets the media version which is used to append to extension's media files.
	 *
	 * @param string the extension to create the version for
	 * @param string $mediaVersion The media version.

	 */
    protected static function setMediaVersion(string $extension, string $mediaVersion)
	{
	    $params = LibraryHelper::getParams('syw');

		$extensions_version = json_decode($params->get('mediaversions', ''), true);
		$extensions_version[$extension] = $mediaVersion;

		$params->set('mediaversions', json_encode($extensions_version));

		LibraryHelper::saveParams('syw', $params);

		self::$extensions_version = $extensions_version;
	}

}
