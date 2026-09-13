<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use SYW\Library\Libraries as SYWLibraries;
use SYW\Library\Stylesheets as SYWStylesheets;

$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

$bootstrap_version = isset($displayData['bootstrap_version']) ? intval($displayData['bootstrap_version']) : 5;
$load_bootstrap = isset($displayData['load_bootstrap']) ? $displayData['load_bootstrap'] : true;
if ($load_bootstrap) {
	//SYWStylesheets::loadBootstrapModals(); // the CSS may be missing ? NO, present in SCSS
	HTMLHelper::_('bootstrap.modal');
}

$selector = $displayData['selector'];

$width = isset($displayData['width']) ? $displayData['width'] : '600';
$height = isset($displayData['height']) ? $displayData['height'] : '400';

$title = isset($displayData['title']) ? $displayData['title'] : Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_MODAL_TITLE');

if ($bootstrap_version > 0) {
	SYWLibraries::instantiateBootstrapModal($selector, array('default_title' => $title, 'height' => $height), $bootstrap_version);
	SYWStylesheets::loadBootstrapModalsCss($selector, array('width' => $width), $bootstrap_version);
} else {
	SYWLibraries::instantiatePureModal($selector);
	SYWStylesheets::loadPureModalsCss();
}
?>
<?php if ($bootstrap_version == 0) : ?>
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
<?php if ($bootstrap_version == 2) : ?>
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
<?php if ($bootstrap_version > 2) : ?>
	<div id="<?php echo $selector; ?>" data-backdrop="false" data-keyboard="true" data-remote="true" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="<?php echo $selector; ?>Label" aria-hidden="true">
    	<div class="modal-dialog" role="document">
    		<div class="modal-content">
            	<div class="modal-header">
            		<?php if ($bootstrap_version == 3) : ?>
            			<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?>"><span aria-hidden="true">&times;</span></button>
            			<h4 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h4>
            		<?php endif; ?>
            		<?php if ($bootstrap_version == 4) : ?>
            			<h5 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h5>
            			<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?>"><span aria-hidden="true">&times;</span></button>
            		<?php endif; ?>
            		<?php if ($bootstrap_version == 5) : ?>
            			<h5 id="<?php echo $selector; ?>Label" class="modal-title"><?php echo $title; ?></h5>
            			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?>"></button>
            		<?php endif; ?>
            	</div>
            	<div class="modal-body">
            		<iframe class="iframe" height="<?php echo $height; ?>" style="display: block; width: 100%; border: 0; max-height: none; overflow: auto"></iframe>
            	</div>
            	<div class="modal-footer">
            		<button class="btn btn-secondary" data-<?php echo ($bootstrap_version >= 5 ? 'bs-' : '') ?>dismiss="modal" aria-hidden="true"><?php echo Text::_('MOD_LATESTNEWSENHANCEDEXTENDED_CLOSE'); ?></button>
            	</div>
    		</div>
    	</div>
    </div>
<?php endif; ?>