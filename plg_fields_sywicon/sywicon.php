<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;

/**
 * Fields SYWIcon Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsSYWIcon extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{
	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   Form       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode) {
			return $fieldNode;
		}

		FormHelper::addFieldPrefix('SYW\Library\Field');

		$fieldNode->setAttribute('type', 'sywiconpicker');
		$fieldNode->setAttribute('buttonrole', 'clear');

		if ($field->fieldparams->get('icon_editable', 0)) {
		    $fieldNode->setAttribute('editable', 'true');
		}

		if ($field->fieldparams->get('icon_icomoon', 0)) {
		    $fieldNode->setAttribute('icomoon', 'true');
		}

		return $fieldNode;
	}

}
