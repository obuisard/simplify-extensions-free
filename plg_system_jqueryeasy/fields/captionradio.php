<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

JFormHelper::loadFieldClass('radio');

class JFormFieldCaptionRadio extends JFormFieldRadio
{
	public $type = 'CaptionRadio';
	
	protected $library;
	
	protected function getInput()
	{
		$html = array();
		
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);
		
		if ((version_compare(JVERSION, '3.2', 'ge') && $this->library == 'jQuery') || (version_compare(JVERSION, '3.2', 'lt') && $this->library == 'MooTools')) {
			
			// Initialize some field attributes.
			$class = $this->element['class'] ? ' class="radio ' . (string) $this->element['class'] . '"' : ' class="radio"';
			
			// Start the radio field output.
			$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
			
			// Get the field options.
			$options = $this->getOptions();
			
			// Build the radio field output.
			foreach ($options as $i => $option)
			{
				
				// Initialize some option attributes.
				$checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
				$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
				$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
				$required = !empty($option->required) ? ' required="required" aria-required="true"' : '';
				
				// Initialize some JavaScript option attributes.
				$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
				
				$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
						. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . $required . '/>';
						
						$html[] = '<label for="' . $this->id . $i . '"' . $class . '>'
								. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';
			}
			
			// End the radio field output.
			$html[] = '</fieldset>';
		} else {
			$html[] = '<div style="margin-bottom:0" class="alert alert-warning">';
			if (version_compare(JVERSION, '3.2', 'ge')) {
				$html[] = JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_DISABLECAPTIONJQUERY');
			} else {
				$html[] = JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_DISABLECAPTIONMOOTOOLS');
			}
			$html[] = '</div>';
		}
		
		return implode($html);
	}
	
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		
		if ($return) {
			$this->library = isset($this->element['library']) ? trim($this->element['library']) : 'jQuery';
		}
		
		return $return;
	}
	
	// 	protected function getId($fieldId, $fieldName)
	// 	{
	// 		if ((version_compare(JVERSION, '3.2', 'ge') && $this->element['library'] == 'jQuery') || (version_compare(JVERSION, '3.2', 'lt') && $this->element['library'] == 'MooTools')) {
	// 			return parent::getId('disablecaptions', 'disablecaptions');
	// 		} else {
	// 			return parent::getId('disablecaptions_'.strtolower($this->element['library']), 'disablecaptions_'.strtolower($this->element['library']));
	// 		}
	// 	}
	
	// 	protected function getName($fieldName)
	// 	{
	// 		if ((version_compare(JVERSION, '3.2', 'ge') && $this->element['library'] == 'jQuery') || (version_compare(JVERSION, '3.2', 'lt') && $this->element['library'] == 'MooTools')) {
	// 			return parent::getName('disablecaptions');
	// 		} else {
	// 			return parent::getName('disablecaptions_'.strtolower($this->element['library']));
	// 		}
	// 	}
	
	protected function getFieldName($fieldName)
	{
		if ((version_compare(JVERSION, '3.2', 'ge') && $this->element['library'] == 'jQuery') || (version_compare(JVERSION, '3.2', 'lt') && $this->element['library'] == 'MooTools')) {
			return $fieldName; //'disablecaptions';
		} else {
			return 'disablecaptions_'.strtolower($this->element['library']);
		}
	}
	}
	?>