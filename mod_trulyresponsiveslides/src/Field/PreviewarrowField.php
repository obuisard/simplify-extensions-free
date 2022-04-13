<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/*
 * Preview for Truly Responsive Slides arrows
 */
class PreviewarrowField extends FormField
{
	public $type = 'Previewarrow';

	protected function getInput()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->useStyle('fontawesome');

		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {

					let preview = document.querySelectorAll(".preview_arrow");

					preview.forEach(function (el) {
						el.style.backgroundColor = document.getElementById("jform_params_arrow_bgc").value;
						el.querySelector("i").style.color = document.getElementById("jform_params_arrow_c").value;
						el.style.borderRadius = document.getElementById("jform_params_arrow_bgr").value + "px";
						el.style.boxShadow = "0 0 " + document.getElementById("jform_params_arrow_shadow").value + "px #000";
					});

					document.getElementById("a_jform_params_arrow_bgc").addEventListener("click", function(event) {
						document.querySelectorAll(".preview_arrow").forEach(function (el) {
							el.style.backgroundColor = "transparent";
						});
					});

					document.getElementById("visible_jform_params_arrow_bgc").addEventListener("change", function(event) {
						document.querySelectorAll(".preview_arrow").forEach(function (el) {
							el.style.backgroundColor = document.getElementById("jform_params_arrow_bgc").value;
						});
					});

					document.getElementById("jform_params_arrow_c").addEventListener("change", function(event) {
						if (event.target.value != "") {
							document.querySelectorAll(".preview_arrow i").forEach(function (el) {
								el.style.color = event.target.value;
							});
						}
					});

					document.getElementById("jform_params_arrow_bgr").addEventListener("change", function(event) {
						if (event.target.value != "") {
							document.querySelectorAll(".preview_arrow").forEach(function (el) {
								el.style.borderRadius = event.target.value + "px";
							});
						}
					});

					document.getElementById("jform_params_arrow_shadow").addEventListener("change", function(event) {
						if (event.target.value != "") {
							document.querySelectorAll(".preview_arrow").forEach(function (el) {
								el.style.boxShadow = "0 0 " + event.target.value + "px #000";
							});
						}
					});
				}
			});
		');

		$wam->addInlineStyle('.preview_arrow { opacity: 1 } .preview_arrow:hover { opacity: 0.7 }');

		$html = '';

		$html .= '<div style="width: 84px; padding: 20px; background-color: #fbfbfb; border: 2px dashed #ccc; -webkit-border-radius: 10px; border-radius: 10px; box-sizing: initial">';

			$html .= '<div id="preview_arrow_left" class="preview_arrow" style="display: inline-block; width: 32px; height: 32px; vertical-align: middle; text-align: center; cursor: pointer">';
				$html .= '<i class="fas fa-angle-left" style="font-size: 32px; line-height: 32px"></i>';
			$html .= '</div>';

			$html .= '<div id="preview_arrow_right" class="preview_arrow" style="margin-left: 20px; display: inline-block; width: 32px; height: 32px; vertical-align: middle; text-align: center; cursor: pointer">';
				$html .= '<i class="fas fa-angle-right" style="font-size: 32px; line-height: 32px"></i>';
			$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}
?>