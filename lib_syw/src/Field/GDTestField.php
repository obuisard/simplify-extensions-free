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

class GDTestField extends FormField
{
	public $type = 'Gdtest';

	protected $supportedtypes; // can be gif jpg png webp
	protected $message;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$extensions = get_loaded_extensions();

		$html = '';

		if (!in_array( 'gd', $extensions)) {
			$html .= '<div style="margin: 0" class="alert alert-error">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::_('LIB_SYW_GDTEST_NOTLOADED').'</span>';
			$html .= '</div>';

			return $html;
		} else {
			$html .= '<div style="margin: 0" class="alert alert-success">';
				if ($this->message) {
					$html .= '<span style="display: inline-block; padding-bottom: 10px">'. $this->message .'</span><br />';
				}
				$html .= '<span>'.Text::_('LIB_SYW_GDTEST_LOADED').' ('.GD_VERSION.')'.'</span><br />';

			if (in_array('gif', $this->supportedtypes)) {
				if (imagetypes() & IMG_GIF) {
					$html .= '<span class="badge badge-success">GIF '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge badge-warning">GIF '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('jpg', $this->supportedtypes)) {
				if (imagetypes() & IMG_JPG) {
					$html .= '<span class="badge badge-success">JPG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge badge-warning">JPG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('png', $this->supportedtypes)) {
				if (imagetypes() & IMG_PNG) {
					$html .= '<span class="badge badge-success">PNG '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= '<span class="badge badge-warning">PNG '.lcfirst(Text::_('JDISABLED')).'</span> ';
				}
			}

			if (in_array('webp', $this->supportedtypes)) {
				if (imagetypes() & IMG_WEBP) {
					$html .= ' <span class="badge badge-success">WEBP '.lcfirst(Text::_('JENABLED')).'</span> ';
				} else {
					$html .= ' <span class="badge badge-warning">WEBP '.lcfirst(Text::_('JDISABLED')).'</span> ';
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
			$this->supportedtypes = explode(',', $supportedtypes);
			$this->message = isset($this->element['message']) ? trim(Text::_((string)$this->element['message'])) : '';
		}

		return $return;
	}

}
?>
