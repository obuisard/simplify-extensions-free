<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die ;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class SywfontweightpickerField extends ListField
{
    public $type = 'Sywfontweightpicker';
    
    /**
     * Method to get a list of tags
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options = [];
        
        $options[] = HTMLHelper::_('select.option', '100', Text::_('LIB_SYW_FONTPICKER_THIN'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '100i', Text::_('LIB_SYW_FONTPICKER_THIN_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '200', Text::_('LIB_SYW_FONTPICKER_EXTRALIGHT'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '200i', Text::_('LIB_SYW_FONTPICKER_EXTRALIGHT_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '300', Text::_('LIB_SYW_FONTPICKER_LIGHT'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '300i', Text::_('LIB_SYW_FONTPICKER_LIGHT_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '400', Text::_('LIB_SYW_FONTPICKER_NORMAL'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '400i', Text::_('LIB_SYW_FONTPICKER_NORMAL_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '500', Text::_('LIB_SYW_FONTPICKER_MEDIUM'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '500i', Text::_('LIB_SYW_FONTPICKER_MEDIUM_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '600', Text::_('LIB_SYW_FONTPICKER_SEMIBOLD'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '600i', Text::_('LIB_SYW_FONTPICKER_SEMIBOLD_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '700', Text::_('LIB_SYW_FONTPICKER_BOLD'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '700i', Text::_('LIB_SYW_FONTPICKER_BOLD_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '800', Text::_('LIB_SYW_FONTPICKER_EXTRABOLD'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '800i', Text::_('LIB_SYW_FONTPICKER_EXTRABOLD_ITALIC'), 'value', 'text');
        
        $options[] = HTMLHelper::_('select.option', '900', Text::_('LIB_SYW_FONTPICKER_BLACK'), 'value', 'text');
        $options[] = HTMLHelper::_('select.option', '900i', Text::_('LIB_SYW_FONTPICKER_BLACK_ITALIC'), 'value', 'text');
    
        return $options;
    }
}
