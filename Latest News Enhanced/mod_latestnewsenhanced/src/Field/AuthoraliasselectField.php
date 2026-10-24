<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\LatestNewsEnhanced\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Author alias selection
 */
class AuthoraliasselectField extends ListField
{
    public $type = 'Authoraliasselect';
    
    protected $option;

	protected function getOptions()
	{
		$options = array();

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select('DISTINCT created_by_alias');
		$query->select($db->quoteName('created_by_alias', 'value'));
		$query->select($db->quoteName('created_by_alias', 'text'));
		$query->from($db->quoteName('#__content'));
		$query->where($db->quoteName('created_by_alias') . ' != ' . $db->quote(''));
		$query->order('created_by_alias', 'ASC');

		$db->setQuery($query);

		try {
			$authors = $db->loadObjectList();
		} catch (ExecutionFailureException $e) {
			$authors = array();
		}

		$options = array_merge($options, $authors);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->option = isset($this->element['option']) ? $this->element['option'] : '';
		}

		return $return;
	}
}
?>