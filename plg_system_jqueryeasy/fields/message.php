<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldMessage extends JFormField
{
	public $type = 'Message';

	protected function getLabel()
	{
		$html = '';

		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		if ($this->message_type == 'example') {
			$html .= '<label style="visibility: hidden; margin: 0">'.JText::_('PLG_SYSTEM_JQUERYEASY_EXAMPLE_EXAMPLE_LABEL').'</label>';
		} else if ($this->message_type == 'fieldwarning' || $this->message_type == 'fielderror' || $this->message_type == 'fieldinfo') {
			return parent::getLabel();
		} else {
			$html .= '<div style="clear: both;"></div>';
		}

		return $html;
	}

	protected function getInput()
	{
		$html = '';

		$lang = JFactory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$message_label = '';
		if ($this->element['label']) {
			$message_label = $this->translateLabel ? JText::_(trim($this->element['label'])) : trim($this->element['label']);
		}
			
		if ($this->message_type == 'example') {
				
			if ($message_label) {
				$html .= '<span class="label">'.$message_label.'</span>&nbsp;';
			} else {
				$html .= '<span class="label">'.JText::_('PLG_SYSTEM_JQUERYEASY_EXAMPLE_EXAMPLE_LABEL').'</span>&nbsp;';
			}
			$html .= '<span class="muted" style="font-size: 0.8em;">';
				
			if ($this->message) {
				$html .= JText::_($this->message);
			}
			$html .= '</span>';
				
		} else {
			$style = '';
			switch ($this->message_type) {
				case 'warning': case 'fieldwarning': $style = 'warning'; break;
				case 'error': case 'fielderror': $style = 'error'; break;
				case 'info': case 'fieldinfo': $style = 'info'; break;
				default: $style = 'success'; /* message, success */
			}
				
			$html .= '<div style="margin-bottom:0" class="alert alert-'.$style.'">';
			if ($message_label && $this->message_type != 'fieldwarning' && $this->message_type != 'fielderror' && $this->message_type != 'fieldinfo') {
				$html .= '<span class="label label-'.$style.'">'.$message_label.'</span>&nbsp;';
			}
				
			$html .= '<span>';
			if ($this->message) {
				$html .= JText::_($this->message);
			}
			$html .= '</span>';
			$html .= '</div>';
		}

		return $html;
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->message_type = isset($this->element['style']) ? trim($this->element['style']) : 'info';
			$this->message = isset($this->element['text']) ? trim($this->element['text']) : '';
		}

		return $return;
	}

}
?>