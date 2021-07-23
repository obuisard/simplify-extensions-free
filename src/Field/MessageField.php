<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class MessageField extends FormField
{
	public $type = 'Message';

	protected $message_type;
	protected $message;
	protected $badge_type;
	protected $badge;

	protected function getLabel()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		if ($this->message_type == 'example' || $this->message_type == 'fieldneutral' || $this->message_type == 'fieldwarning' || $this->message_type == 'fielderror' || $this->message_type == 'fieldinfo') {
			if ($this->badge) {
				return '<span class="badge bg-' . $this->badge_type . '">' . $this->badge . '</span><br />' . parent::getLabel();
			} else {
				return parent::getLabel();
			}
		}

		return $html;
	}

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$message_label = '';
		if ($this->element['label']) {
			$message_label = $this->translateLabel ? Text::_(trim($this->element['label'])) : trim($this->element['label']);
		}

		if ($this->message_type == 'example') {

			if ($this->message) {
				$html .= '<span class="muted" style="font-size: 0.8em;">' . Text::_($this->message) . '</span>';
			}

		} else {
			$style = '';
			$style_label = '';
			switch ($this->message_type) {
				case 'warning': case 'fieldwarning': $style = 'warning'; $style_label = 'warning'; break;
				case 'error': case 'fielderror': $style = 'danger'; $style_label = 'danger'; break;
				case 'info': case 'fieldinfo': $style = 'info'; $style_label = 'info'; break;
				case 'neutral': case 'fieldneutral': $style = 'light'; $style_label = 'light'; break;
				case 'dark': case 'fielddark': $style = 'dark'; $style_label = 'dark'; break;
				case 'primary': case 'fieldprimary': $style = 'primary'; $style_label = 'primary'; break;
				case 'secondary': case 'fieldsecondary': $style = 'secondary'; $style_label = 'secondary'; break;
				default: $style = 'success'; $style_label = 'success'; /* message, success */
			}

			$class = '';
			if ($style) {
				$class = ' alert-' . $style;
			}

			$html .= '<div style="margin: 0" class="alert' . $class . '">';
			if ($message_label && $this->message_type != 'fieldneutral' && $this->message_type != 'fieldwarning' && $this->message_type != 'fielderror' && $this->message_type != 'fieldinfo') {

			    if ($message_label == 'Pro') {
			        $style_label = 'danger';
			    }

			    if ($style_label) {
			        $style_label = ' bg-'.$style_label;
			    }

			    $html .= '<span class="badge' . $style_label . '">' . $message_label . '</span>&nbsp;';
			}

			if ($this->message) {
			    $style_attribute = '';
			    if ((isset($message_label) && $message_label == 'Pro') || $this->badge == 'Pro') {
			        $style_attribute = ' style="font-style: italic"';
			    }
			    $html .= '<span' . $style_attribute . '>' . Text::_($this->message) . '</span>';
			}

			$html .= '</div>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->message_type = isset($this->element['style']) ? trim((string)$this->element['style']) : 'info';
			$this->message = isset($this->element['text']) ? trim((string)$this->element['text']) : '';
			$this->badge_type = isset($this->element['badgetype']) ? trim((string)$this->element['badgetype']) : 'danger';
			if ($this->badge_type == 'light') {
			    $this->badge_type .= ' text-dark';
			}
			
			$this->badge = isset($this->element['badge']) ? trim((string)$this->element['badge']) : '';
			$this->badge = Text::_($this->badge);
		}

		return $return;
	}

}
?>