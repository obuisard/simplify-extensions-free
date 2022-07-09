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
use Joomla\CMS\Uri\Uri;

/* deprecated - use imagefilepreview */
class SywimagefileField extends FormField
{
	public $type = 'Sywimagefile';

	protected $width;
	protected $height;
	protected $show_name;
	protected $show_preview;

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		// Initialize some field attributes.
		$accept = $this->element['accept'] ? ' accept="' . (string) $this->element['accept'] . '"' : ' accept=".gif,.jpg,.png"';
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? 'class="form-control-file ' . (string) $this->element['class'] . '"' : 'class="form-control-file"';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$html .= '<input type="file" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .'"' . $accept . $disabled . $class . $size . $maxLength . $onchange . ' />';

		$path = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

		if ($this->show_preview) {

			$style = '';

			$style .= 'max-width: '.$this->width.'px;';

			if (!empty($this->height)) {
				$style .= 'max-height: '.$this->height.'px;';
			}

			$html .= '<div class="image_preview" style="'.$style.' overflow: auto; border: 1px solid #ccc; border-radius: 3px; padding: 10px; margin-top: 5px; text-align: center">';

			if (!empty($path)) {
				$parts = explode('/', $path);
				$html .= '<img src="'.URI::root().$path.'" alt="'.end($parts).'" style="max-width: 100%">';

// 				$extensions_needing_fallbacks = array('webp', 'avif');
// 				$image_extension = JFile::getExt($path);
// 				if (in_array($image_extension, $extensions_needing_fallbacks)) {
// 					$html .= '<br /><br /><span style="font-size: .8em">'.JText::_('LIB_SYW_IMAGEPREVIEW_PREVIEWMAYNOTBEAVAILABLE').'</span>';
// 				}

				if ($this->show_name) {
					$html .= '<br /><br /><span class="file_name">'.end($parts).'</span>';
				}
			} else {
				// no preview available
				$html .= '<span>'.Text::_('LIB_SYW_IMAGEPREVIEW_NOPREVIEW').'</span>';
			}

			$html .= '</div>';
		} else {
			if ($this->show_name) {
				$parts = explode('/', $path);
				$html .= '<br /><br /><input class="image_file form-control" type="text" disabled="disabled" value="'.end($parts).'" />';
			}
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->width = isset($this->element['width']) ? trim((string)$this->element['width']) : '200';
			$this->height = isset($this->element['height']) ? trim((string)$this->element['height']) : '';
			$this->show_name = isset($this->element['showname']) ? filter_var($this->element['showname'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->show_preview = isset($this->element['showpreview']) ? filter_var($this->element['showpreview'], FILTER_VALIDATE_BOOLEAN) : false;
		}

		return $return;
	}

}
