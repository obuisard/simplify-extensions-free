<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Module\TrulyResponsiveSlides\Site\Field;

defined( '_JEXEC' ) or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use SYW\Library\Field\DynamicsingleselectField;

class SliderselectField extends DynamicsingleselectField
{
    public $type = 'Sliderselect';

    protected $items;

    protected function getOptions()
    {
        $options = array();

        $imagefolder = Uri::root(true) . '/media/mod_trulyresponsiveslides/images/sliders/';

        foreach ($this->items as $key => $value) {
        	if (isset($value['badge'])) {
        		$options[] = array($key, $value['label'], '', $imagefolder . $value['image'] . '.png', '', $value['disabled'], $value['badge']);
        	} else {
        		$options[] = array($key, $value['label'], '', $imagefolder . $value['image'] . '.png', '', $value['disabled']);
        	}
        }

        return $options;
    }

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->width = 120;
            $this->height = 100;

            $this->items = array();
            $this->items['basic'] = array('label' => Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_ANIMATIONBASIC'), 'image' => 'sliders_basic', 'disabled' => false);
            $this->items['withthumb'] = array('label' => Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_ANIMATIONWITHTHUMB'), 'image' => 'sliders_autothumbs', 'disabled' => false);
            $this->items['withthumbslider'] = array('label' => Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_ANIMATIONWITHTHUMBSLIDER'), 'image' => 'sliders_thumbcarousel', 'disabled' => true, 'badge' => 'Pro');
            $this->items['withautohidethumbslider'] = array('label' => Text::_('MOD_TRULYRESPONSIVESLIDER_VALUE_ANIMATIONWITHAUTOHIDETHUMBSLIDER'), 'image' => 'sliders_thumbcarousel_closed', 'disabled' => true, 'badge' => 'Pro');
        }

        return $return;
    }
}
?>