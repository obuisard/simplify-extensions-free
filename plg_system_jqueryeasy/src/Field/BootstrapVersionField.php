<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Plugin\System\JqueryEasy\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class BootstrapVersionField extends FormField
{
	public $type = 'Bootstrapversion';

	static $versions = array('4.0' => '4.5.0');

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('plg_system_jqueryeasy.sys', JPATH_SITE);

		$version = 'undefined';

		$numbers = explode('.', JVERSION);
		$joomla_release = $numbers[0].'.'.$numbers[1];

		if (isset(self::$versions[$joomla_release])) {
			$version = self::$versions[$joomla_release];
		}

		$html .= '<div class="bootstrapversion alert alert-info" style="margin: 0">';
		$html .= '  <span>'.Text::sprintf('PLG_SYSTEM_JQUERYEASY_FIELD_JOOMLAISPACKAGEDWITH_LABEL', 'Bootstrap '.$version).'</span>';
		$html .= '</div>';

		$url = 'https://api.cdnjs.com/libraries/twitter-bootstrap?fields=version';
		$div = 'bootstrapversion';

		Factory::getDocument()->addScriptDeclaration('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {
					var request = new XMLHttpRequest();
					request.open("GET", "' . $url . '", true);
					request.onload = function() {
					  	if (this.status >= 200 && this.status < 400) {
							var data = JSON.parse(this.response);
							if (data != undefined && data.version != undefined) {

								const json_version = document.createElement("span");
								json_version.classList.add("badge", "bg-info");
								json_version.innerText = data.version;

								const the_version = document.createTextNode("' . Text::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSION_LABEL') . ' ");
								const the_source = document.createTextNode(" (' . Text::_('PLG_SYSTEM_JQUERYEASY_FIELD_LATESTAVAILABLEVERSIONSOURCE_LABEL') . ' Cloudflare)");

								let the_div = document.querySelector(".' . $div . '");
								the_div.appendChild(the_version);
								the_div.appendChild(json_version);
								the_div.appendChild(the_source);
								the_div.insertBefore(document.createElement("br"), the_version);
							}
					  	}
					};
					request.send();
				}
			});
		');

		return $html;
	}

}
?>