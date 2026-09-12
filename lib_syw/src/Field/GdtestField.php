<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class GdtestField extends FormField
{
	public $type = 'Gdtest';

	protected $supported_types; // can be gif jpg png webp avif
	protected $message;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		if (!extension_loaded('gd')) {
			$html .= '<div style="margin: 0" class="alert alert-warning">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::sprintf('LIB_SYW_PHPEXTENSION_NOTINSTALLED', 'gd').'</span>';
			$html .= '</div>';

			return $html;
		} else {
			$html .= '<div style="margin: 0" class="alert alert-success">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::sprintf('LIB_SYW_PHPEXTENSION_INSTALLED', 'gd').' ('.GD_VERSION.')'.'</span><br />';

			if (in_array('gif', $this->supported_types)) {
				if (imagetypes() & IMG_GIF) {
					$html .= '<span class="badge bg-success">GIF '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">GIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('jpg', $this->supported_types)) {
				if (imagetypes() & IMG_JPG) {
					$html .= '<span class="badge bg-success">JPG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">JPG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('png', $this->supported_types)) {
				if (imagetypes() & IMG_PNG) {
					$html .= '<span class="badge bg-success">PNG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge bg-warning">PNG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('webp', $this->supported_types)) {
				if (imagetypes() & IMG_WEBP) {
					$html .= ' <span class="badge bg-success">WEBP '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= ' <span class="badge bg-warning">WEBP '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}
			
			if (in_array('avif', $this->supported_types)) {
			    if (function_exists('imageavif')) {
			        $html .= ' <span class="badge bg-success">AVIF '.lcfirst(Text::_('JENABLED')).'</span> ';
			    } else {
			        $html .= ' <span class="badge bg-warning">AVIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
			    }
			}

			$html .= '</div>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$supportedtypes = isset($this->element['supportedtypes']) ? strtolower(str_replace(' ', '', (string)$this->element['supportedtypes'])) : 'gif,jpg,png';
			$this->supported_types = explode(',', $supportedtypes);
			$this->message = isset($this->element['message']) ? trim(Text::_((string)$this->element['message'])) : '';
		}

		return $return;
	}

}
?>
