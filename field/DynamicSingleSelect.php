<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class DynamicSingleSelect extends \JFormFieldList
{
	public $type = 'DynamicSingleSelect';
	
	protected $use_global;
	protected $noelement;
	protected $width;
	protected $maxwidth;
	protected $height;
	protected $selectedcolor;
	protected $disabledtitle;

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */
	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);
		
		\JHtml::_('bootstrap.tooltip');

		// build the script

		Factory::getDocument()->addScriptDeclaration("
			jQuery(document).ready(function () {
				jQuery('#".$this->id."_elements .element.enabled').each(function() {
					if (jQuery(this).attr('data-option') == '".$this->value."') {
						jQuery(this).css('border', '7px dashed ".$this->selectedcolor."');
					}
				});
				jQuery('#".$this->id."_elements .element.enabled').click(function() {
					jQuery('#".$this->id."_id').val(jQuery(this).attr('data-option'));
					jQuery('#".$this->id."_id').change();
					jQuery('#".$this->id."_elements .element').css('border', '7px solid #fff');
					jQuery(this).css('border', '7px dashed ".$this->selectedcolor."');
				});
			});
		");

		// add the styles

		Factory::getDocument()->addStyleDeclaration("
			#".$this->id."_elements { display: -webkit-box; display: -ms-flexbox; display: -webkit-flex; display: flex; overflow-x: auto; }
			#".$this->id."_elements .element { display: inline-block; position: relative; vertical-align: top; relative; margin: 0 5px 5px 5px; padding: 15px;".(!empty($this->maxwidth) ? " max-width: ".$this->maxwidth."px;" : "")." background-color: #f4f4f4; border: 7px solid #fff; text-align: center; cursor: pointer; }
			#".$this->id."_elements .element.global { background-color: #2a6496; color: #fff }
			#".$this->id."_elements .element:first-child { margin-left: 0 }
			#".$this->id."_elements .element.disabled { opacity: 0.65; filter: alpha(opacity=65); cursor: default; }
			#".$this->id."_elements .images-container { display: inline-block; position: relative; width: ".$this->width."px; height: ".$this->height."px; margin-bottom: 5px; }
			#".$this->id."_elements .element img { display: block; position: absolute; left: 50%; transform: translateX(-50%); -webkit-transition: opacity .4s ease; transition: opacity .4s ease; max-width: ".$this->width."px; max-height: ".$this->height."px; }
			#".$this->id."_elements .element img.original { opacity: 1; filter: alpha(opacity=100); }
			#".$this->id."_elements .element img.hover { opacity: 0; filter: alpha(opacity=0); z-index: 2; }
			#".$this->id."_elements .element:hover img.hover { opacity: 1; filter: alpha(opacity=100); }
			#".$this->id."_elements .element:hover img.original { opacity: 0; filter: alpha(opacity=0); }
		");

		$options = array();

		if ($this->noelement) {
			$options[] = array('', \JText::_('JNONE'), '');
		}

		$options = array_merge($options, $this->getOptions());

		$value = $this->default;
		if (!empty($this->value)) {
			$value = $this->value;
		}

		$html = '<ul id="'.$this->id.'_elements" class="elements thumbnails">';

		foreach ($options as $option) {
			
			$class_global = '';
			$class_disabled = '';
			$class_hastooltip = '';
			$title_attribute = '';
			
			if (isset($option[5]) && ($option[5] == 'disabled' || $option[5] == true)) {
				$class_disabled = ' disabled';
				if (!empty($this->disabledtitle)) {
					$title_attribute = ' title="'.\JText::_($this->disabledtitle).'"';
					$class_hastooltip = ' hasTooltip';
				}
			} else {
				$class_disabled = ' enabled';
				$title_attribute = ' title="'.\JText::_('JSELECT').'"';
				$class_hastooltip = ' hasTooltip';
			}
			
			if ($this->use_global && $option[0] == '') {
				$class_global = ' global';
			}
			
			$html .= '<li class="element thumbnail'.$class_global.$class_hastooltip.$class_disabled.'" data-option="'.$option[0].'"'.$title_attribute.'>';
				$html .= '<div class="images-container">';
				if (isset($option[3]) && !empty($option[3])) {
	
					$originalclass = '';
					if (isset($option[4]) && !empty($option[4])) {
						$originalclass = ' class="original"';
						$html .= '<img class="hover" alt="'.$option[1].'" src="'.$option[4].'" />';
					}
	
					$html .= '<img'.$originalclass.' alt="'.$option[1].'" src="'.$option[3].'" />';
				}
				$html .= '</div>';
	
				$html .= '<h3>'.$option[1].'</h3>';
				if (!empty($option[2])) {
					$html .= '<p style="font-size: .8em">'.$option[2].'</p>';
				}
			$html .= '</li>';
		}

		$html .= '</ul>';
		$html .= '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.$value.'" />';

		return $html;
	}

	protected function getOptions()
	{
	    $xml_options = parent::getOptions();
	    $options = array();
	    
	    foreach ($xml_options as $option) {
	        $options[] = array($option->value, $option->text, '', '', '', $option->disable);
	    }
	    
	    // TODO problem 'none' has no value, like global value

//		$options[] = array('option1', 'Option 1', 'Description 1', 'option1/option1.png', 'option1/option1_hover.png');
//		$options[] = array('option2', 'Option 2', 'Description 2', 'option2/option2.png', 'option2/option2_hover.png');
//		$options[] = array('option3', 'Option 3', 'Description 3', 'option3/option3.png', 'option3/option3_hover.png', 'disabled');

		return $options;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->use_global = ($this->element['global'] == "true") ? true : false;
			$this->noelement = isset($this->element['noelement']) ? filter_var($this->element['noelement'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->width = 100;
			$this->maxwidth = '';
			$this->height = 100;
			$this->selectedcolor = '#6f6f6f';//isset($this->element['selectedcolor']) ? $this->element['selectedcolor'] : '#6f6f6f';
			$this->disabledtitle = isset($this->element['disabledtitle']) ? $this->element['disabledtitle'] : '';
		}

		return $return;
	}
}
?>