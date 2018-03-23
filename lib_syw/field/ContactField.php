<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * Supports a modal contact picker
 */
class ContactField extends FormField
{
	protected $type = 'Contact';

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		\JHtml::_('bootstrap.tooltip');
	
		$db = Factory::getDBO();

		// Build the script
		
		$script = array();
		
		$script[] = '	function jSelectContact_'.$this->id.'(id, name, object) {';
		$script[] = '		jQuery("#'.$this->id.'_id").val(id);';
		$script[] = '		jQuery("#'.$this->id.'_name").val(name);';
		$script[] = '		jQuery("#'.$this->id.'_clear").removeClass("hidden");';
		$script[] = '		jQuery("#modalContact'.$this->id.'").modal("hide");';
		if ($this->required) {
			$script[] = '		document.formvalidator.validate(jQuery("#'.$this->id.'_id"));';
			$script[] = '		document.formvalidator.validate(jQuery("#'.$this->id.'_name"));';
		}
		$script[] = '	}';
		
		static $scriptClear;		
		if (!$scriptClear) {
			$scriptClear = true;
		
			$script[] = '	function jClearContact(id) {';
			$script[] = '		jQuery("#" + id + "_id").val("");';
			$script[] = '		jQuery("#" + id + "_name").val("'.htmlspecialchars(\JText::_('LIB_SYW_CONTACT_SELECTCONTACT', true), ENT_COMPAT, 'UTF-8').'");';
			$script[] = '		jQuery("#" + id + "_clear").addClass("hidden");';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup display
		
		$link = 'index.php?option=com_contact&amp;view=contacts&amp;layout=modal&amp;tmpl=component&amp;function=jSelectContact_'.$this->id;
		$link .= '&amp;'.Session::getFormToken().'=1';
		
		$title = '';
		if ((int) $this->value > 0) {
			$query = $db->getQuery(true);
				
			$query->select($db->quoteName('name'));
			$query->from($db->quoteName('#__contact_details'));
			$query->where($db->quoteName('id').' = '.(int) $this->value);
				
			$db->setQuery($query);
		
			try {
				$title = $db->loadResult();
			} catch (\RuntimeException $e) {
				Factory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}
		}		
		
		if (empty($title)) {
			$title = \JText::_('LIB_SYW_CONTACT_SELECTCONTACT');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		
		if (0 == (int) $this->value) {
			$value = '';
		} else {
			$value = (int) $this->value;
		}
		
		$html = '<span class="input-append">';
		$html .= '    <input type="text" class="input-large" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" />';
		$html .= '    <a href="#modalContact'.$this->id.'" class="btn hasTooltip" role="button" data-toggle="modal" title="'.\JText::_('LIB_SYW_CONTACT_SELECTCONTACT').'"><i class="icon-address"></i> '.\JText::_('JSELECT').'</a>';
		$html .= '    <a id="'.$this->id.'_clear" href="#" class="btn hasTooltip'.($value ? '' : ' hidden').'" title="'.\JText::_('JCLEAR').'" onclick="return jClearContact(\''.$this->id.'\')"><i class="icon-remove"></i></a>';
		$html .= '</span>';			

		$class = '';
		if ($this->required) {
			$class = 'required ';
		}

		$html .= '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" class="'.$class.'modal-value" value="'.$value.'" />';
		
		$modal_params = array();
		$modal_params['url'] = $link;
		$modal_params['title'] = \JText::_('LIB_SYW_CONTACT_SELECTCONTACT');
		$modal_params['width'] = '800px';
		$modal_params['height'] = '300px';
		$modal_params['footer'] = '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'.\JText::_("JLIB_HTML_BEHAVIOR_CLOSE").'</button>';
		
		$html .= \JHTML::_('bootstrap.renderModal', 'modalContact'.$this->id, $modal_params);

		return $html;
	}
}
