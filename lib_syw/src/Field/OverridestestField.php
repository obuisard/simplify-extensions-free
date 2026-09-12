<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Filesystem\Folder;

class OverridestestField extends FormField
{
	public $type = 'Overridestest';

	protected $extension;
	protected $view;
	protected $additional_extensions;
	protected $parent_extension;
	protected $include_layouts;

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$html = '';

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		//$defaultemplate = JFactory::getApplication('site')->getTemplate(); // does not work because we can only get one instance of JApplication and it already is admin

		$db = Factory::getDBO();

		$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";

		$db->setQuery($query);

		try {
			$defaultemplate = $db->loadResult();
		} catch (ExecutionFailureException $e) {
			return $html;
		}

		$overrides_path = JPATH_ROOT.'/templates/'.$defaultemplate.'/html/';

		$overrides_do_exist = false;

		$html .= '<div class="alert alert-info" style="margin: 0">';

		// overrides extension

		$html .= '<span>';

		if (!is_null($this->extension) && is_dir($overrides_path.$this->extension.$this->view)) {
			$files = Folder::files($overrides_path.$this->extension.$this->view, '.php');
			if (!empty($files)) {
				$html .= Text::sprintf('LIB_SYW_FILESOVERRIDEN', $this->extension.$this->view);
				foreach ($files as $file) {
					$html .= ' <code>'.$file.'</code>';
				}
			} else {
				$html .= Text::sprintf('LIB_SYW_EMPTYFOLDER', '/templates/'.$defaultemplate.'/html/'.$this->extension.$this->view);
			}

			$overrides_do_exist = true;
		} else {
			$html .= Text::sprintf('LIB_SYW_NOOVERRIDES', $this->extension.$this->view);
		}

		$html .= '</span><br />';

		// override additional extensions
		// if plugins, test it they are enabled

		if (!is_null($this->additional_extensions)) {
			$extensions = explode(',', $this->additional_extensions);
			foreach ($extensions as $extension) {
				$check_overrides = false;
				$entension_parts = explode('_', $extension);
				if ($entension_parts[0] == 'plg') {
					if (PluginHelper::isEnabled($entension_parts[1], $entension_parts[2])) {
						$check_overrides = true;
					}
				} else {
					$check_overrides = true;
				}
				if ($check_overrides) {
					if (is_dir($overrides_path.$extension)) {

						$html .= '<span>';

						$files = Folder::files($overrides_path.$extension, '.php');
						if (!empty($files)) {
							$html .= Text::sprintf('LIB_SYW_FILESOVERRIDEN', $extension);
							foreach ($files as $file) {
								$html .= ' <code>'.$file.'</code>';
							}
						} else {
							$html .= Text::sprintf('LIB_SYW_EMPTYFOLDER', '/templates/'.$defaultemplate.'/html/'.$extension);
						}

						$html .= '</span><br />';

						$overrides_do_exist = true;
					}
				}
			}
		}

		// layouts (check if layouts from parent extension)

		if ($this->include_layouts) {

			$html .= '<span>';

			if (!is_null($this->extension) && is_dir($overrides_path.'layouts/'.$this->extension)) {
			    $files = Folder::files($overrides_path.'layouts/'.$this->extension, '.php');
			    $folders = Folder::folders($overrides_path.'layouts/'.$this->extension);
			    if (!empty($files) || !empty($folders)) {
					$html .= Text::sprintf('LIB_SYW_LAYOUTSOVERRIDEN', $this->extension);
					foreach ($files as $file) {
						$html .= ' <code>'.$file.'</code>';
					}
					foreach ($folders as $folder) {
					    $subfiles = Folder::files($overrides_path.'layouts/'.$this->extension.'/'.$folder, '.php');
					    if (!empty($subfiles)) {
					        $html .= '<br /><code>'.$folder.'/</code><br />';
					        foreach ($subfiles as $file) {
					            $html .= ' <code>'.$file.'</code>';
					        }
					    } else {
					        $html .= '<br />'.Text::sprintf('LIB_SYW_EMPTYFOLDER', '/templates/'.$defaultemplate.'/html/layouts/'.$this->extension.'/'.$folder);
					    }
					}
				} else {
					$html .= Text::sprintf('LIB_SYW_EMPTYFOLDER', '/templates/'.$defaultemplate.'/html/layouts/'.$this->extension);
				}

				$overrides_do_exist = true;
			} else if (!is_null($this->parent_extension) && is_dir($overrides_path.'layouts/'.$this->parent_extension)) {
			    $files = Folder::files($overrides_path.'layouts/'.$this->parent_extension, '.php');
			    $folders = Folder::folders($overrides_path.'layouts/'.$this->parent_extension);
			    if (!empty($files) || !empty($folders)) {
// 					$html .= JText::sprintf('LIB_SYW_LAYOUTSOVERRIDEN', $this->parent_extension);
// 					foreach ($files as $file) {
// 						$html .= '<br /><code>'.$file.'</code>';
// 					}
					$html .= Text::sprintf('LIB_SYW_LAYOUTSOVERRIDENINPARENT', $this->parent_extension, $this->extension);
				} else {
					$html .= Text::sprintf('LIB_SYW_EMPTYFOLDER', '/templates/'.$defaultemplate.'/html/layouts/'.$this->parent_extension);
				}

				$overrides_do_exist = true;
			} else {
				$html .= Text::sprintf('LIB_SYW_NOLAYOUTOVERRIDES', $this->extension);
			}

			$html .= '</span>';
		}

		$html .= '</div>';

		if (!$overrides_do_exist) {
			return '';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->extension = isset($this->element['extension']) ? (string)$this->element['extension'] : null;
			$this->view = isset($this->element['view']) ? '/' . ((string)$this->element['view']) : '';
			$this->additional_extensions = isset($this->element['additional_extensions']) ? (string)$this->element['additional_extensions'] : null;
			$this->parent_extension = isset($this->element['parent_extension']) ? (string)$this->element['parent_extension'] : null;
			$this->include_layouts = isset($this->element['include_layouts']) ? filter_var($this->element['include_layouts'], FILTER_VALIDATE_BOOLEAN) : true;
		}

		return $return;
	}

}
?>
