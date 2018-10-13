<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('list');

class JFormFieldTemplates extends \JFormFieldList
{
	public $type = 'Templates';
		
	protected function getOptions()
	{
		$options = array();
		
		$client = ApplicationHelper::getClientInfo('site', true);
		
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('s.id, s.title, e.name as name, s.template');
		$query->from('#__template_styles as s');
		$query->where('s.client_id = ' . (int) $client->id);
		$query->order('template');
		$query->order('title');
		$query->join('LEFT', '#__extensions as e on e.element=s.template');
		$query->where('e.enabled=1');
		$query->where($db->quoteName('e.type') . '=' . $db->quote('template'));
	
		$db->setQuery($query);
		
		$templates = array();
		try {
			$templates = $db->loadObjectList();
		} catch (\RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}		
	
		foreach ($templates as $item) {
			$options[] = \JHTML::_('select.option', $item->id, $item->title);
		}	

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
