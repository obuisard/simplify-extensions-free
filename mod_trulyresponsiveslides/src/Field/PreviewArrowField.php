<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/*
 * Preview for Truly Responsive Slides arrows
 */
class PreviewArrowField extends FormField
{
	public $type = 'PreviewArrow';

	protected function getInput()
	{
	    HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);

		// a little over-complicated because having a radius, a bg transparent and no shadow leaves some traces
		$script = 'jQuery(document).ready(function () {';
			$script .= 'var preview_color = jQuery(\'#jform_params_arrow_c\').val(); ';
			$script .= 'if (preview_color != \'\') { ';
				$script .= 'jQuery(\'.preview_arrow i\').css(\'color\', preview_color); ';
			$script .= '} ';
			$script .= 'jQuery(\'#jform_params_arrow_c\').change(function() { ';
				$script .= 'jQuery(\'.preview_arrow i\').css(\'color\', jQuery(\'#jform_params_arrow_c\').val()); ';
			$script .= '}); ';

			$script .= 'var preview_bgcolor = jQuery(\'#jform_params_arrow_bgc\').val(); ';
			$script .= 'if (preview_bgcolor != \'\') {';
				$script .= 'jQuery(\'.preview_arrow\').css(\'background-color\', preview_bgcolor); ';
			$script .= '} ';

			$script .= 'jQuery(\'#a_jform_params_arrow_bgc\').click(function() { ';
				$script .= 'jQuery(\'.preview_arrow\').css(\'background-color\', \'transparent\'); ';
				$script .= 'if (jQuery(\'#jform_params_arrow_shadow\').val() == 0) { ';
					$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', \'0px\'); ';
				$script .= '}; ';
			$script .= '}); ';

			$script .= 'jQuery(\'#visible_jform_params_arrow_bgc\').change(function() { ';
				$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', jQuery(\'#jform_params_arrow_bgr\').val() + \'px\'); ';
				$script .= 'jQuery(\'.preview_arrow\').css(\'background-color\', jQuery(\'#jform_params_arrow_bgc\').val()); ';
			$script .= '}); ';

			$script .= 'if (preview_bgcolor == \'\' && jQuery(\'#jform_params_arrow_shadow\').val() == 0) {';
				$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', \'0px\'); ';
			$script .= '} else { ';
				$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', jQuery(\'#jform_params_arrow_bgr\').val() + \'px\'); ';
			$script .= '} ';
			$script .= 'jQuery(\'#jform_params_arrow_bgr\').change(function() { ';
				$script .= 'if ((jQuery(\'#jform_params_arrow_bgc\').val() == \'\' || jQuery(\'#jform_params_arrow_bgc\').val() == \'transparent\') && jQuery(\'#jform_params_arrow_shadow\').val() == 0) {';
					$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', \'0px\'); ';
				$script .= '} else { ';
					$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', jQuery(\'#jform_params_arrow_bgr\').val() + \'px\'); ';
				$script .= '} ';
			$script .= '}); ';

			$script .= 'jQuery(\'.preview_arrow\').css(\'box-shadow\', \'0 0 \' + jQuery(\'#jform_params_arrow_shadow\').val() + \'px #000\'); ';
			$script .= 'jQuery(\'#jform_params_arrow_shadow\').change(function() { ';
				$script .= 'if ((jQuery(\'#jform_params_arrow_bgc\').val() == \'\' || jQuery(\'#jform_params_arrow_bgc\').val() == \'transparent\') && jQuery(\'#jform_params_arrow_shadow\').val() == 0) {';
					$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', \'0px\'); ';
				$script .= '} else { ';
					$script .= 'jQuery(\'.preview_arrow\').css(\'border-radius\', jQuery(\'#jform_params_arrow_bgr\').val() + \'px\'); ';
				$script .= '} ';
				$script .= 'jQuery(\'.preview_arrow\').css(\'box-shadow\', \'0 0 \' + jQuery(\'#jform_params_arrow_shadow\').val() + \'px #000\'); ';
			$script .= '}); ';

			$script .= 'jQuery(\'.preview_arrow\').hover(function() { jQuery(this).css(\'opacity\', \'.7\'); } , function() { jQuery(this).css(\'opacity\', \'1\'); }); ';
		$script .= '});';

		Factory::getDocument()->addScriptDeclaration($script);

		$html = '';

		$html .= '<div style="width: 84px; padding: 20px; background-color: #fbfbfb; border: 2px dashed #ccc; border-radius: 10px">';

			$html .= '<div id="preview_arrow_left" class="preview_arrow" style="display: inline-block; width: 32px; height: 32px; vertical-align: middle; text-align: center; cursor: pointer">';
				$html .= '<i class="SYWicon-keyboard-arrow-left" style="font-size: 32px; line-height: 32px"></i>';
			$html .= '</div>';

			$html .= '<div id="preview_arrow_right" class="preview_arrow" style="margin-left: 20px; display: inline-block; width: 32px; height: 32px; vertical-align: middle; text-align: center; cursor: pointer">';
				$html .= '<i class="SYWicon-keyboard-arrow-right" style="font-size: 32px; line-height: 32px"></i>';
			$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}
?>