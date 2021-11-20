<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class ImagesHelper
{
	static $dirpath = '';

	static function getItems($params, $module)
	{
		$directory = JPATH_SITE.'/images'.$params->get('images_folder');

		if (!Folder::exists($directory)) {
			return null;
		}

		$list = array();

		$images = Folder::files($directory);
		foreach($images as $image) {
			$extension = strtolower(File::getExt($image));
			if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'webp' || $extension == 'avif') {
				$list[] = $image;
			}
		}

		//$images = glob(''.JURI::root(true).'/images'.$directory.'/'.'{*.jpg, *.png}', GLOB_BRACE);

		self::$dirpath = $directory.'/';

		$order = $params->get('images_sort', 'string');
		switch ($order) {
			case 'date': usort($list, "ImagesHelper::dateSort"); break; // sort files by date
			default: sort($list, SORT_STRING); break;
		}

		return $list;
	}

	protected static function dateSort($a, $b)
	{
		if (filemtime(self::$dirpath.$a) == filemtime(self::$dirpath.$b)) {
			return 0;
		}
		return (filemtime(self::$dirpath.$a) < filemtime(self::$dirpath.$b)) ? -1 : 1;
	}

}
