<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicsingleselectField;
use SYW\Library\K2 as SYWK2;

class DatasourceselectField extends DynamicsingleselectField
{
	public $type = 'Datasourceselect';

	protected function getOptions()
	{
	    $options = parent::getOptions();
	    
	    $options[] = array('k2', Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_K2ITEMS'), '', '', '', !SYWK2::exists());
	    
	    $imagefolder = '/media/mod_trulyresponsiveslides/images/datasources';
	    
	    foreach ($options as &$option) {
	        
	        $option[3] = Uri::root(true).$imagefolder.'/'.$option[0].'.png';
	    }
	    
	    return $options;
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
	    $return = parent::setup($element, $value, $group);
	    
	    if ($return) {
	        $this->width = 100;
	        $this->height = 100;
	    }
	    
	    return $return;
	}
}
?>