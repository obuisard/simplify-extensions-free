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
use SYW\Library\Version as SYWVersion;

/**
 *
 * @author Olivier Buisard
 *
 * field parameters
 *
 * accept: allowed extensions - defaults to .gif,.jpg,.png
 * size
 * maxlength
 * class
 * disabled
 * onchange
 * showpreview: show the file preview - defaults to false
 * width: the preview max width - defaults to 200
 * height: the preview max width
 * showname: show the file name - defaults to false
 *
 */
class SywimagefilepreviewField extends FormField
{
    public $type = 'Sywimagefilepreview';

    protected $width;
    protected $height;
    protected $show_name;
    protected $show_preview;
    protected $clear;
    protected $versionfromext;

    protected function getInput()
    {
    	$html = '';

        $lang = Factory::getLanguage();
        $lang->load('lib_syw.sys', JPATH_SITE);

        $wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($this->show_preview) {
			$wam->addInlineScript('
				document.addEventListener("readystatechange", function(event) {
					if (event.target.readyState == "complete") {
						document.getElementById("' . $this->id . '_file").addEventListener("change", on' . $this->id . 'FileSelected, false);
						function on' . $this->id . 'FileSelected (input) {
							if (input.target.files[0]) {
								file = input.target.files[0];
								file_path = (window.URL || window.webkitURL).createObjectURL(file);
								let reader = new FileReader();
								reader.readAsDataURL(file);
								reader.onloadend = function(evt) {
									if (evt.target.readyState == FileReader.DONE) {
										let image_preview = document.querySelector("#' . $this->id . '_preview img");
										if (image_preview != null) {
											image_preview.setAttribute("src", file_path);
											document.getElementById("' . $this->id . '_preview").querySelector(".image_preview").style.display = "block";
											document.getElementById("' . $this->id . '_preview").querySelector(".no_preview").style.display = "none";
											let filename = document.querySelector("#' . $this->id . '_preview .file_name");
											if (filename != null) {
												filename.textContent = file.name;
											}
										}
									}
								}
							}
						}
					}
				});
			');
		} else {
			$wam->addInlineScript('
				document.addEventListener("readystatechange", function(event) {
					if (event.target.readyState == "complete") {
						document.getElementById("' . $this->id . '_file").addEventListener("change", on' . $this->id . 'FileSelected, false);
						function on' . $this->id . 'FileSelected (input) {
							if (input.target.files[0]) {
								file = input.target.files[0];
								file_path = (window.URL || window.webkitURL).createObjectURL(file);
								let reader = new FileReader();
								reader.readAsDataURL(file);
								reader.onloadend = function(evt) {
									if (evt.target.readyState == FileReader.DONE) {
										document.getElementById("' . $this->id . '_filename").value = file.name;
									}
								}
							}
						}
					}
				});
			');
		}

        // Initialize some field attributes.
        $accept = $this->element['accept'] ? ' accept="' . (string) $this->element['accept'] . '"' : ' accept=".gif,.jpg,.png"';
        $size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        $html .= '<input type="file" name="' . $this->getName($this->fieldname . '_file') . '" id="' . $this->id . '_file"' . $accept . $disabled . $class . $size . $maxLength . $onchange . ' />';
        $html .= '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .'" />';

        $path = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');

        if ($this->show_preview) {

            $style = '';

            $style .= 'max-width: '.$this->width.'px;';

            if (!empty($this->height)) {
                $style .= 'max-height: '.$this->height.'px;';
            }

            $html .= '<div id="'.$this->id.'_preview" style="'.$style.' overflow: auto; border: 1px solid #ccc; border-radius: 3px; padding: 10px; margin-top: 5px; text-align: center">';

			$html .= '<div class="image_preview" style="display: ' . (empty($path) ? 'none' : 'block') . '">';

			$parts = explode('/', $path);
			
			$file_path = Uri::root().$path;
			if ($this->versionfromext) {
			    $file_path .= '?' . SYWVersion::getMediaVersion($this->versionfromext);
			}

			$html .= '<img src="' . $file_path . '" alt="' . end($parts) . '" style="max-width: 100%">';

//                 $extensions_needing_fallbacks = array('webp', 'avif');
//                 $image_extension = JFile::getExt($path);
//                 if (in_array($image_extension, $extensions_needing_fallbacks)) {
//                 	$html .= '<br /><br /><span style="font-size: .8em">'.JText::_('LIB_SYW_IMAGEPREVIEW_PREVIEWMAYNOTBEAVAILABLE').'</span>';
//                 }

			if ($this->show_name) {
				$html .= '<br /><br /><span class="file_name">'.end($parts).'</span>';
			}

			// clear button
			if ($this->clear) {

				$onclick = 'document.getElementById(\'' . $this->id . '_preview\').querySelector(\'.image_preview\').style.display = \'none\';';
				$onclick .= 'document.getElementById(\'' . $this->id . '\').value = \'\';';
				$onclick .= 'document.getElementById(\'' . $this->id . '_file\').value = \'\';';
				$onclick .= 'document.getElementById(\'' . $this->id . '_preview\').querySelector(\'.no_preview\').style.display = \'block\';';
				$onclick .= 'return false;';

				$html .= '<br /><br /><button onclick="' . $onclick . '" class="btn btn-sm btn-danger">' . Text::_('JACTION_DELETE') . '</button>';
			}

			$html .= '</div>';

			$html .= '<div class="no_preview" style="display: ' . (empty($path) ? 'block' : 'none') . '">';
				$html .= '<span>'.Text::_('LIB_SYW_IMAGEPREVIEW_NOPREVIEW').'</span>';
			$html .= '</div>';

			$html .= '</div>';
		} else {
			$html .= '<br /><br />';

			$parts = explode('/', $path);

			if ($this->clear) {
				$html .= '<div class="input-group">';
			}

			$html .= '<input id="'.$this->id.'_filename" class="form-control" type="text" disabled="disabled" value="'.end($parts).'" />';

			if ($this->clear) {

				$onclick = 'document.getElementById(\'' . $this->id . '_filename\').value = \'\';';
				$onclick .= 'document.getElementById(\'' . $this->id . '\').value = \'\';';
				$onclick .= 'document.getElementById(\'' . $this->id . '_file\').value = \'\';';
				$onclick .= 'return false;';

				$html .= '<button onclick="' . $onclick . '" class="btn btn-sm btn-danger">' . Text::_('JACTION_DELETE') . '</button>';
				
				$html .= '</div>';
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
            $this->clear = isset($this->element['clear']) ? filter_var($this->element['clear'], FILTER_VALIDATE_BOOLEAN) : true;
            $this->versionfromext = isset($this->element['versionfromext']) ? trim((string)$this->element['versionfromext']) : '';
        }

        return $return;
    }

}
