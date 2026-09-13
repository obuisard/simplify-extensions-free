<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Form\Field\ListField;

class ListfreeField extends ListField
{
	public $type = 'Listfree';

	protected function getOptions()
	{
		$options = parent::getOptions();

		foreach ($options as $option) {
			if ($option->disable) {
				$option->text .= ' (Pro)';
			}
		}

		return $options;
	}
}
?>