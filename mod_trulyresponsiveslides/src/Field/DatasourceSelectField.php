<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use SYW\Library\K2 as SYWK2;

class DatasourceSelectField extends ListField
{
	public $type = 'DatasourceSelect';

	protected function getOptions()
	{
		$options = array();

		$options[] = HTMLHelper::_('select.option', 'k2', Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_K2ITEMS'), 'value', 'text', $disable = !SYWK2::exists());

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>