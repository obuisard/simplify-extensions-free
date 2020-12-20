<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use SYW\Library\Stylesheets as SYWStylesheets;
use SYW\Library\Libraries as SYWLibraries;

$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

$bootstrapVersion = isset($displayData['bootstrap_version']) ? intval($displayData['bootstrap_version']) : 2;
$loadBootstrap = isset($displayData['load_bootstrap']) ? $displayData['load_bootstrap'] : true;
if ($loadBootstrap) {
    SYWStylesheets::loadBootstrapModals();
    HTMLHelper::_('bootstrap.framework');
}

$selector = $displayData['selector'];

$width = isset($displayData['width']) ? $displayData['width'] : '600';
$height = isset($displayData['height']) ? $displayData['height'] : '400';

$title = isset($displayData['title']) ? $displayData['title'] : Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_MODAL_TITLE');

if ($bootstrapVersion > 0) {
	$script = "jQuery(document).ready(function($) { ";

		$script .= "$('.".$selector."').on('click', function () { ";
			$script .= "var dataTitle = $(this).attr('data-modaltitle'); ";
			$script .= "if (typeof (dataTitle) !== 'undefined' && dataTitle !== null) { $('#".$selector."').find('.modal-title').text(dataTitle); } ";
			$script .= "var dataURL = $(this).attr('href'); ";
			$script .= "$('#".$selector."').find('.iframe').attr('src', dataURL); ";
		$script .= "}); ";

		$script .= "$('#".$selector."').on('show.bs.modal', function() { ";
			$script .= "$('body').addClass('modal-open'); ";
			$script .= "var event = document.createEvent('Event'); event.initEvent('modalopen', true, true); document.dispatchEvent(event); ";
		$script .= "}).on('shown.bs.modal', function() { ";
			$script .= "var modal_body = $(this).find('.modal-body'); modal_body.css({'max-height': ".$height."}); ";
			$script .= "var padding = parseInt(modal_body.css('padding-top')) + parseInt(modal_body.css('padding-bottom')); modal_body.find('.iframe').css({'height': (".$height." - padding)}); ";
		$script .= "}).on('hide.bs.modal', function () { ";
			$script .= "$(this).find('.modal-title').text('" . $title . "'); ";
			$script .= "var modal_body = $(this).find('.modal-body'); modal_body.css({'max-height': 'initial'}); modal_body.find('.iframe').attr('src', 'about:blank'); $('body').removeClass('modal-open'); ";
			$script .= "var event = document.createEvent('Event'); event.initEvent('modalclose', true, true); document.dispatchEvent(event); ";
		$script .= "});";

	$script .= "}); ";

	//Factory::getDocument()->addScriptDeclaration($script);
	$wam->addInlineScript($script);

	$style = '';
	if ($bootstrapVersion > 2) {
    	$style = '@media (min-width: 768px) { #'.$selector.' .modal-dialog { width: 80%; max-width: '.$width.'px; } } ';
	} else {
    	$style = '@media (min-width: 768px) { #'.$selector.' { max-width: 80%; left: 50%; margin-left: auto; -webkit-transform: translate(-50%); -ms-transform: translate(-50%); transform: translate(-50%); width: '.$width.'px; } } ';
	}

	//Factory::getDocument()->addStyleDeclaration($style);
	$wam->addInlineStyle($style);
} else {
	SYWLibraries::instantiatePureModal($selector);
	SYWStylesheets::loadPureModalsCss();
}
?>
<?php if ($bootstrapVersion == 0) : ?>
	<div id="<?php echo $selector; ?>" class="puremodal" role="dialog" aria-hidden="true">
		<div class="puremodal-content" aria-modal="true" aria-labelledby="<?php echo $selector; ?>Label">
			<div class="puremodal-header">
				<h3 id="<?php echo $selector; ?>Label" class="puremodal-title"><?php echo $title; ?></h3>
			</div>
			<div class="puremodal-body">
				<iframe class="iframe" height="<?php echo $height; ?>"></iframe>
			</div>
			<div class="puremodal-footer">
            	<button class="puremodal-close" aria-hidden="true"><?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?></button>
            </div>
		</div>
	</div>
<?php endif; ?>
<?php if ($bootstrapVersion == 2) : ?>
	<div id="<?php echo $selector; ?>" data-backdrop="false" data-keyboard="true" data-remote="true" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $selector; ?>Label" aria-hidden="true">
    	<div class="modal-header">
    		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    		<h3 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h3>
    	</div>
    	<div class="modal-body">
    		<iframe class="iframe" height="<?php echo $height; ?>" style="display: block; width: 100%; border: 0; max-height: none; overflow: auto"></iframe>
    	</div>
    	<div class="modal-footer">
    		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?></button>
    	</div>
    </div>
<?php endif; ?>
<?php if ($bootstrapVersion > 2) : ?>
	<div id="<?php echo $selector; ?>" data-backdrop="false" data-keyboard="true" data-remote="true" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $selector; ?>Label" aria-hidden="true">
    	<div class="modal-dialog" role="document">
    		<div class="modal-content">
            	<div class="modal-header">
            		<?php if ($bootstrapVersion == 3) : ?>
            			<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?>"><span aria-hidden="true">&times;</span></button>
            			<h4 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h4>
            		<?php endif; ?>
            		<?php if ($bootstrapVersion == 4) : ?>
            			<h5 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h5>
            			<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?>"><span aria-hidden="true">&times;</span></button>
            		<?php endif; ?>
            	</div>
            	<div class="modal-body">
            		<iframe class="iframe" height="<?php echo $height; ?>" style="display: block; width: 100%; border: 0; max-height: none; overflow: auto"></iframe>
            	</div>
            	<div class="modal-footer">
            		<button class="btn btn-secondary" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?></button>
            	</div>
    		</div>
    	</div>
    </div>
<?php endif; ?>