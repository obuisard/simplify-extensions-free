<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use SYW\Library\Fonts as SYWFonts;
use SYW\Library\Utilities as SYWUtilities;

$value = $field->value;

if ($value == '')
{
	return;
}

if ($field->fieldparams->get('universal', 0))
{
	$value = SYWUtilities::getIconFullName($value); // backward compatibility with old picker

	if (strpos($value, 'SYWicon-') !== false)
	{
		SYWFonts::loadIconFont();
	}
	else if (strpos($value, 'icon-') !== false || strpos($value, 'fa-') !== false)
	{
		SYWFonts::loadIconFont('fontawesome'); // icomoon is supported through the fontawesome asset
	}
}
else
{
	$contains_prefix = false;

	if (strpos($value, 'SYWicon-') !== false)
	{
		SYWFonts::loadIconFont();
		$contains_prefix = true;
	}
	else if (strpos($value, 'icon-') !== false || strpos($value, 'fa-') !== false)
	{
		SYWFonts::loadIconFont('fontawesome'); // icomoon is supported through the fontawesome asset
		$contains_prefix = true;
	}

	// old picker values

	if (!$contains_prefix)
	{
		if (strpos($value, 'icomoon') !== false)
		{
			$value = str_replace('icomoon-', 'icon-', $value);

			SYWFonts::loadIconFont('fontawesome'); // icomoon is supported through the fontawesome asset
		}
		else
		{
			$value = 'SYWicon-' . $value;

			SYWFonts::loadIconFont();
		}
	}
}

echo '<i class="' . $value . '"></i>';
