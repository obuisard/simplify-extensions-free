<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Factory;

FormHelper::loadFieldClass('subform');

class SYWSubformField extends SubformField
{
	public $type = 'SYWSubform';
	
	static $added_script = false;
	
	static function addScripts() 
	{
		if (!self::$added_script) {
			self::$added_script = true;
			
			// fixes to aknowledge Bootstrap
			
			// TODO Bootstrap 4 
			
			$script = 'jQuery(document).ready(function () { ';
				$script .= 'jQuery(document).on("subform-row-add", function(event, row) { ';
					
					$script .= 'jQuery(row).find("select").chosen(); '; // fix select (class="advancedSelect" does not always work)
					
					$script .= 'jQuery(row).find(".hasTooltip").tooltip();';
					$script .= 'jQuery(row).find(".hasPopover").popover({ container: "body", trigger: "hover focus" });';
					
					$script .= 'jQuery(row).find(".radio.btn-group label").addClass("btn");'; // turn radios into btn-group
					
					// Prevent clicks on disabled fields
					$script .= 'jQuery(row).find("fieldset.btn-group").each(function() {';
						$script .= 'if (jQuery(this).prop("disabled")) {';
						$script .= 'jQuery(this).css("pointer-events", "none").off("click");';
							$script .= 'jQuery(this).find(".btn").addClass("disabled");';
						$script .= '}';
					$script .= '});';
					
					// Add btn-* styling to checked fields according to their values
					$script .= 'jQuery(row).find(".btn-group label:not(.active)").click(function() { ';
						$script .= 'var label = jQuery(this); ';
						$script .= 'var input = jQuery("#" + label.attr("for")); ';
							
						$script .= 'if (!input.prop("checked")) { ';
							$script .= 'label.closest(".btn-group").find("label").removeClass("active btn-success btn-danger btn-primary"); ';
							$script .= 'if (input.val() == "") { ';
								$script .= 'label.addClass("active btn-primary"); ';
							$script .= '} else if (input.val() == 0) { ';
								$script .= 'label.addClass("active btn-danger"); ';
							$script .= '} else { ';
								$script .= 'label.addClass("active btn-success"); ';
							$script .= '} ';
							$script .= 'input.prop("checked", true); ';
							$script .= 'input.trigger("change"); ';
						$script .= '} ';
					$script .= '}); ';
					
					$script .= 'jQuery(row).find(\'.btn-group input[checked="checked"]\').each(function() { ';
						$script .= 'var input = jQuery(this); ';
						$script .= 'if (input.val() == "") { ';
							$script .= 'input.parent().find(\'label[for="\' + input.attr(\'id\') + \'"]\').addClass("active btn-primary"); ';
						$script .= '} else if (input.val() == 0) { ';
							$script .= 'input.parent().find(\'label[for="\' + input.attr(\'id\') + \'"]\').addClass("active btn-danger"); ';
						$script .= '} else { ';
							$script .= 'input.parent().find(\'label[for="\' + input.attr(\'id\') + \'"]\').addClass("active btn-success"); ';
						$script .= '} ';
					$script .= '}); ';
					
				$script .= '}) ';
			$script .= '});';
			
			Factory::getDocument()->addScriptDeclaration($script);
		}
	}

	protected function getInput()
	{
		self::addScripts();			

		return parent::getInput();
	}

}
?>