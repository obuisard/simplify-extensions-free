<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JQueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class ThemesField extends FormField
{
	public $type = 'Themes';

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';

		HTMLHelper::stylesheet('plg_system_jqueryeasy/themes.css', array('relative' => true, 'version' => 'auto'));

		$type = strtolower($this->type);

		ob_start();
		require_once dirname(__FILE__) . '/' . $type . '/tmpl/default.php';
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

}
?>