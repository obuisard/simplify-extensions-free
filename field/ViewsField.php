<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('list');

class ViewsField extends ListField
{
	public $type = 'Views';
	
	protected $extension_option;
	protected $extension_view;
	
	protected function getOptions()
	{
		$options = array();
		
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		
		$additional_tag = '';
		if (Multilanguage::isEnabled()) {
			$additional_tag = ', " (", a.language, ")"';
		}
		
		$query->select('DISTINCT a.id AS value, CONCAT(a.title, " (", a.alias, ")"'.$additional_tag.') AS text, a.alias, a.level, a.menutype, a.type, a.template_style_id, a.checked_out');
		$query->from('#__menu AS a');
		$query->join('LEFT', $db->quoteName('#__menu') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
		
		$extension_views = explode(",", $this->extension_view);
		$views_sql = '';
		foreach ($extension_views as $extension_view) {
		    $views_sql .= 'a.link LIKE '.$db->quote('%option='.$this->extension_option.'&view='.$extension_view.'%').' OR ';
		}
		
		$query->where(substr($views_sql, 0, -4));
		
		$query->where('a.published = 1');
		
		// 		if (JLanguageMultilang::isEnabled()) {
		// 			$lang = JFactory::getLanguage();
		// 			$query->where('a.language = '.$db->quote($lang->getTag()));
		// 		}
		
		$db->setQuery($query);
		
		try {
			$options = $db->loadObjectList();
		} catch (\RuntimeException $e) {
			//return false;
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		
		return $options;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->extension_option = isset($this->element['option']) ? trim($this->element['option']) : '';
			$this->extension_view = isset($this->element['view']) ? $this->element['view'] : ''; 
		}
		
		return $return;
	}
	
}
