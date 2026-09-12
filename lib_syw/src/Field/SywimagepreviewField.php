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
use Joomla\CMS\Uri\Uri;

class SywimagepreviewField extends FormField
{
	public $type = 'Sywimagepreview';

	protected $path;
	protected $relative_path;
	protected $width;
	protected $height;
	protected $show_name;

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$style = '';

		$style .= 'max-width: '.$this->width.'px;';

		if ($this->height) {
			$style .= 'max-height: '.$this->height.'px;';
		}

		$html .= '<div class="image_preview" style="'.$style.' overflow: auto; border: 1px solid #ccc; border-radius: 3px; padding: 10px; text-align: center">';

		if ($this->path) {

			if (!$this->relative_path) {
				$this->path = URI::root().$this->path;
			}

			$parts = explode('/', $this->path);

			$html .= '<img src="'.$this->path.'" alt="'.end($parts).'" style="max-width: 100%">';
			if ($this->show_name) {
				$html .= '<br /><br /><span class="file_name">'.end($parts).'</span>';
			}
		} else {
			// no preview available
			$html .= '<span>'.Text::_('LIB_SYW_IMAGEPREVIEW_NOPREVIEW').'</span>';
		}

		$html .= '</div>';

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->path = isset($this->element['path']) ? trim((string)$this->element['path']) : '';
			$this->relative_path = isset($this->element['relativepath']) ? filter_var($this->element['relativepath'], FILTER_VALIDATE_BOOLEAN) : true;
			$this->width = isset($this->element['width']) ? trim((string)$this->element['width']) : '200';
			$this->height = isset($this->element['height']) ? trim((string)$this->element['height']) : '';
			$this->show_name = isset($this->element['showname']) ? filter_var($this->element['showname'], FILTER_VALIDATE_BOOLEAN) : false;
		}

		return $return;
	}

}
?>