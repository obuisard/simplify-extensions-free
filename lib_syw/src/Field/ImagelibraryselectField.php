<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

class ImagelibraryselectField extends ListField
{
	protected $type = 'Imagelibraryselect';

	protected function getOptions() {

		$options = array();
		
		//$this->__set('default', 'none');
		
		if (!extension_loaded('gd')) {		    
            $options[] = HTMLHelper::_('select.option', 'gd', 'GD', 'value', 'text', true);
		} else {
		    $options[] = HTMLHelper::_('select.option', 'gd', 'GD', 'value', 'text');
		    $this->__set('default', 'gd');
		}
		
		if (!extension_loaded('imagick')) {
		    $options[] = HTMLHelper::_('select.option', 'imagick', 'Imagick', 'value', 'text', true);
		} else {
		    $options[] = HTMLHelper::_('select.option', 'imagick', 'Imagick', 'value', 'text');
		    if (!extension_loaded('gd')) {
		        $this->__set('default', 'imagick');
		    }
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>