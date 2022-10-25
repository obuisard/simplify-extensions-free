<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class ExtensionlinkField extends FormField
{
	public $type = 'Extensionlink';

	protected $link_type;
	protected $link;
	protected $syw_description;

	protected function getLabel()
	{
		$html = '';
		
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		
		$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

		switch ($this->link_type) {
			case 'forum': $icon="SYWicon-chat"; $title = 'LIB_SYW_EXTENSIONLINK_FORUM_LABEL'; break;
			case 'forumbeta': $icon="SYWicon-chat"; $title = 'LIB_SYW_EXTENSIONLINK_FORUMBETA_LABEL'; $class = 'btn-inverse'; break;
			case 'demo': $icon="SYWicon-visibility"; $title = 'LIB_SYW_EXTENSIONLINK_DEMO_LABEL'; break;
			case 'review': $icon="SYWicon-thumb-up"; $title = 'LIB_SYW_EXTENSIONLINK_REVIEW_LABEL'; break;
			case 'donate': $icon="SYWicon-paypal"; $title = 'LIB_SYW_EXTENSIONLINK_DONATE_LABEL'; break;
			case 'upgrade': $icon="SYWicon-wallet-membership"; $title = 'LIB_SYW_EXTENSIONLINK_UPGRADE_LABEL'; break;
			case 'buy': $icon="SYWicon-paypal"; $title = 'LIB_SYW_EXTENSIONLINK_BUY_LABEL'; break;
			case 'doc': $icon="SYWicon-local-library"; $title = 'LIB_SYW_EXTENSIONLINK_DOC_LABEL'; break;
			case 'onlinedoc': $icon="SYWicon-local-library"; $title = 'LIB_SYW_EXTENSIONLINK_ONLINEDOC_LABEL'; break;
			case 'quickstart': $icon="SYWicon-timer"; $title = 'LIB_SYW_EXTENSIONLINK_QUICKSTART_LABEL'; break;
			case 'acknowledgement': $icon="SYWicon-thumb-up"; $title = 'LIB_SYW_EXTENSIONLINK_ACKNOWLEDGEMENT_LABEL'; break;
			case 'license': $icon="SYWicon-receipt"; $title = 'LIB_SYW_EXTENSIONLINK_LICENSE_LABEL'; break;
			case 'report': $icon="SYWicon-bug-report"; $title = 'LIB_SYW_EXTENSIONLINK_BUGREPORT_LABEL'; break;
			case 'support': $icon="SYWicon-lifebuoy"; $title = 'LIB_SYW_EXTENSIONLINK_SUPPORT_LABEL'; break;
			case 'translate': $icon="SYWicon-translate"; $title = 'LIB_SYW_EXTENSIONLINK_TRANSLATE_LABEL'; break;
			default: $icon = ''; $title = '';
		}

		if ($this->link) {
			$html .= '<a class="btn btn-dark btn-sm hasTooltip" title="'.Text::_($title).'" href="'.$this->link.'" target="_blank">';
		} else {
			$html .= '<span class="badge bg-secondary hasTooltip" title="'.Text::_($title).'">';
		}
		$html .= '<i class="'.$icon.'" style="font-size: 2em; vertical-align: middle" aria-hidden="true"></i>';
		if ($this->link) {
			$html .= '</a>';
		} else {
			$html .= '</span>';
		}

		return $html;
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '<div class="syw_info" style="padding-top: 5px; overflow: inherit">';

		if ($this->syw_description) {
			if ($this->link) {
				$html .= Text::sprintf($this->syw_description, $this->link);
			} else {
				$html .= Text::_($this->syw_description);
			}
		} else {

			switch ($this->link_type) {
				case 'forum': $desc = 'LIB_SYW_EXTENSIONLINK_FORUM_DESC'; break;
				case 'forumbeta': $desc = 'LIB_SYW_EXTENSIONLINK_FORUMBETA_DESC'; break;
				case 'demo': $desc = 'LIB_SYW_EXTENSIONLINK_DEMO_DESC'; break;
				case 'review': $desc = 'LIB_SYW_EXTENSIONLINK_REVIEW_DESC'; break;
				case 'donate': $desc = 'LIB_SYW_EXTENSIONLINK_DONATE_DESC'; break;
				case 'upgrade': $desc = 'LIB_SYW_EXTENSIONLINK_UPGRADE_DESC'; break;
				case 'buy': $desc = 'LIB_SYW_EXTENSIONLINK_BUY_DESC'; break;
				case 'doc': $desc = 'LIB_SYW_EXTENSIONLINK_DOC_DESC'; break;
				case 'onlinedoc': $desc = 'LIB_SYW_EXTENSIONLINK_ONLINEDOC_DESC'; break;
				case 'quickstart': $desc = 'LIB_SYW_EXTENSIONLINK_QUICKSTART_DESC'; break;
				case 'acknowledgement': $desc = 'LIB_SYW_EXTENSIONLINK_ACKNOWLEDGEMENT_DESC'; break;
				case 'license': $desc = 'LIB_SYW_EXTENSIONLINK_LICENSE_DESC'; break;
				case 'report': $desc = 'LIB_SYW_EXTENSIONLINK_BUGREPORT_DESC'; break;
				case 'support': $desc = 'LIB_SYW_EXTENSIONLINK_SUPPORT_DESC'; break;
				case 'translate': $desc = 'LIB_SYW_EXTENSIONLINK_TRANSLATE_DESC'; break;
				default: $desc = '';
			}

			if ($desc) {
				if ($this->link) {
				    if ($this->link_type == 'translate') {
				        $html .= Text::sprintf($desc, 'https://simplifyyourweb.com/translators');
				    } else {
				        $html .= Text::sprintf($desc, $this->link);
				    }
				} else {
					$html .= Text::_($desc);
				}
			}
		}

		if ($this->link_type == 'review') {
			$html = rtrim($html, '.');
			$html .= ' <a href="'.$this->link.'" target="_blank" style="text-decoration: none; vertical-align: text-bottom">';
			$html .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
			$html .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
			$html .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
			$html .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i>';
			$html .= '<i class="SYWicon-star" style="font-size: 1.1em; color: #f7c41f; vertical-align: middle" aria-hidden="true"></i></a> .';
		}

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->link_type = (string)$this->element['linktype'];
			$this->link = isset($this->element['link']) ? (string)$this->element['link'] : '';
			$this->syw_description = isset($this->element['sywdescription']) ? (string)$this->element['sywdescription'] : '';
		}

		return $return;
	}

}
?>
