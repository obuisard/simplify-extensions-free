<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class RelatedselectField extends ListField
{
	protected $type = 'Relatedselect';

	protected function getOptions() {

		$options = array();

		// test the fields folder first to avoid message warning that the component is missing
		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_contact') && ComponentHelper::isEnabled('com_contact')) {
			$options[] = HTMLHelper::_('select.option', 'contact', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_RELATEDTOCONTACT') . ' (Pro)', 'value', 'text', $disable = true );
		}

		// test the fields folder first to avoid message warning that the component is missing
		if (is_dir(JPATH_ADMINISTRATOR . '/components/com_comprofiler') && ComponentHelper::isEnabled('com_comprofiler')) {
			$options[] = HTMLHelper::_('select.option', 'cb_user_profile', Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_RELATEDTOCBUSERPROFILE') . ' (Pro)', 'value', 'text', $disable = true );
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>