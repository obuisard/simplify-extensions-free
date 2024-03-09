<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use SYW\Library\Plugin as SYWPLugin;

class Stylesheets
{
	static $puremodalscssLoaded = false;
	static $bootstrapmodalscssLoaded = array();
	static $accessibleVisibilityLoaded = false;

	static $transitionGrowLoaded = false;
	static $transitionShrinkLoaded = false;
	static $transitionPulseLoaded = false;
	static $transitionPulseGrowLoaded = false;
	static $transitionPulseShrinkLoaded = false;
	static $transitionPushLoaded = false;
	static $transitionPopLoaded = false;
	static $transitionBounceInLoaded = false;
	static $transitionBounceOutLoaded = false;
	static $transitionRotateLoaded = false;
	static $transitionGrowRotateLoaded = false;
	static $transitionWobbleVerticalLoaded = false;
	static $transitionWobbleHorizontalLoaded = false;
	static $transitionBuzzLoaded = false;
	static $transitionBuzzOutLoaded = false;
	static $transitionFadeLoaded = false;
	static $transitionBackPulseLoaded = false;
	static $transitionSweepToRightLoaded = false;
	static $transitionSweepToLeftLoaded = false;
	static $transitionSweepToBottomLoaded = false;
	static $transitionSweepToTopLoaded = false;
	static $transitionBounceToRightLoaded = false;
	static $transitionBounceToLeftLoaded = false;
	static $transitionBounceToBottomLoaded = false;
	static $transitionBounceToTopLoaded = false;
	static $transitionRadialOutLoaded = false;
	static $transitionRadialInLoaded = false;
	static $transitionRectangleInLoaded = false;
	static $transitionRectangleOutLoaded = false;
	static $transitionShutterInHorizontalLoaded = false;
	static $transitionShutterOutHorizontalLoaded = false;
	static $transitionShutterInVerticalLoaded = false;
	static $transitionShutterOutVerticalLoaded = false;
	
	/**
	 * The web asset manager
	 */
	protected static $wam;
	
	/**
	 * Get the web asset manager
	 * @return object
	 */
	protected static function getWebAssetManager()
	{
	    if (self::$wam == null) {
	        self::$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
	    }
	    
	    return self::$wam;
	}
	
	/**
	 * Load pureTreeMenu stylesheet
	 * IE11+ compatible
	 */
	public static function loadPureTreeMenu($style = 'rawmenu')
	{
	    self::getWebAssetManager()->registerAndUseStyle('syw.puretreemenu.' . $style, 'syw/puretreemenu/ptm-' . $style . '.min.css', ['relative' => true, 'version' => 'auto']);
	}

	/**
	 * Load the animate stylesheet
	 * v3.7.2
	 * https://github.com/daneden/animate.css
	 */
	public static function loadAnimate($remote = false)
	{	    
	    $attributes = array();
	    if (Factory::getApplication()->isClient('site') && SYWPLugin::getLazyStylesheet() > 0) {
	        $attributes['rel'] = 'lazy-stylesheet';
	    }

		if ($remote) {
		    self::getWebAssetManager()->registerAndUseStyle('syw.animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css', [], $attributes);
		} else {
		    self::getWebAssetManager()->registerAndUseStyle('syw.animate', 'syw/animate.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
		}
	}

	/*
	 * Get the transition method to call
	 * for instance the transition name 'back-pulse' or 'hvr-back-pulse' will return 'loadTransitionBackPulse'
	 */
	public static function getTransitionMethod($transition)
	{
		$transition = str_replace('hvr-', '', $transition);
		return 'loadTransition'.str_replace(' ', '', ucwords(str_replace('-', ' ', $transition)));
	}

	/**
	 * Load the Grow 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionGrow()
	{
		if (self::$transitionGrowLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-grow {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-grow:hover, .hvr-grow:focus, .hvr-grow:active {
  -webkit-transform: scale(1.1);
  transform: scale(1.1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionGrowLoaded = true;
	}

	/**
	 * Load the Shrink 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionShrink()
	{
		if (self::$transitionShrinkLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-shrink {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-shrink:hover, .hvr-shrink:focus, .hvr-shrink:active {
  -webkit-transform: scale(0.9);
  transform: scale(0.9);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionShrinkLoaded = true;
	}

	/**
	 * Load the Pulse 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionPulse()
	{
		if (self::$transitionPulseLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-pulse {
  25% {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }

  75% {
    -webkit-transform: scale(0.9);
    transform: scale(0.9);
  }
}

@keyframes hvr-pulse {
  25% {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }

  75% {
    -webkit-transform: scale(0.9);
    transform: scale(0.9);
  }
}

.hvr-pulse {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-pulse:hover, .hvr-pulse:focus, .hvr-pulse:active {
  -webkit-animation-name: hvr-pulse;
  animation-name: hvr-pulse;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionPulseLoaded = true;
	}

	/**
	 * Load the Pulse Grow 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionPulseGrow()
	{
		if (self::$transitionPulseGrowLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-pulse-grow {
  to {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
}

@keyframes hvr-pulse-grow {
  to {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
}

.hvr-pulse-grow {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-pulse-grow:hover, .hvr-pulse-grow:focus, .hvr-pulse-grow:active {
  -webkit-animation-name: hvr-pulse-grow;
  animation-name: hvr-pulse-grow;
  -webkit-animation-duration: 0.3s;
  animation-duration: 0.3s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  -webkit-animation-direction: alternate;
  animation-direction: alternate;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionPulseGrowLoaded = true;
	}

	/**
	 * Load the Pulse Shrink 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionPulseShrink()
	{
		if (self::$transitionPulseShrinkLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-pulse-shrink {
  to {
    -webkit-transform: scale(0.9);
    transform: scale(0.9);
  }
}

@keyframes hvr-pulse-shrink {
  to {
    -webkit-transform: scale(0.9);
    transform: scale(0.9);
  }
}

.hvr-pulse-shrink {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-pulse-shrink:hover, .hvr-pulse-shrink:focus, .hvr-pulse-shrink:active {
  -webkit-animation-name: hvr-pulse-shrink;
  animation-name: hvr-pulse-shrink;
  -webkit-animation-duration: 0.3s;
  animation-duration: 0.3s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  -webkit-animation-direction: alternate;
  animation-direction: alternate;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionPulseShrinkLoaded = true;
	}

	/**
	 * Load the Push 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionPush()
	{
		if (self::$transitionPushLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-push {
  50% {
    -webkit-transform: scale(0.8);
    transform: scale(0.8);
  }

  100% {
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

@keyframes hvr-push {
  50% {
    -webkit-transform: scale(0.8);
    transform: scale(0.8);
  }

  100% {
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

.hvr-push {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-push:hover, .hvr-push:focus, .hvr-push:active {
  -webkit-animation-name: hvr-push;
  animation-name: hvr-push;
  -webkit-animation-duration: 0.3s;
  animation-duration: 0.3s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: 1;
  animation-iteration-count: 1;
}	
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionPushLoaded = true;
	}

	/**
	 * Load the Pulse 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionPop()
	{
		if (self::$transitionPopLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-pop {
  50% {
    -webkit-transform: scale(1.2);
    transform: scale(1.2);
  }
}

@keyframes hvr-pop {
  50% {
    -webkit-transform: scale(1.2);
    transform: scale(1.2);
  }
}

.hvr-pop {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-pop:hover, .hvr-pop:focus, .hvr-pop:active {
  -webkit-animation-name: hvr-pop;
  animation-name: hvr-pop;
  -webkit-animation-duration: 0.3s;
  animation-duration: 0.3s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: 1;
  animation-iteration-count: 1;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionPopLoaded = true;
	}

	/**
	 * Load the Bounce In 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceIn()
	{
		if (self::$transitionBounceInLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-in {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-in:hover, .hvr-bounce-in:focus, .hvr-bounce-in:active {
  -webkit-transform: scale(1.2);
  transform: scale(1.2);
  -webkit-transition-timing-function: cubic-bezier(0.47, 2.02, 0.31, -0.36);
  transition-timing-function: cubic-bezier(0.47, 2.02, 0.31, -0.36);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceInLoaded = true;
	}

	/**
	 * Load the Bounce Out 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceOut()
	{
		if (self::$transitionBounceOutLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-out {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-out:hover, .hvr-bounce-out:focus, .hvr-bounce-out:active {
  -webkit-transform: scale(0.8);
  transform: scale(0.8);
  -webkit-transition-timing-function: cubic-bezier(0.47, 2.02, 0.31, -0.36);
  transition-timing-function: cubic-bezier(0.47, 2.02, 0.31, -0.36);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceOutLoaded = true;
	}

	/**
	 * Load the Rotate 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionRotate()
	{
		if (self::$transitionRotateLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-rotate {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-rotate:hover, .hvr-rotate:focus, .hvr-rotate:active {
  -webkit-transform: rotate(4deg);
  transform: rotate(4deg);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionRotateLoaded = true;
	}

	/**
	 * Load the Grow Rotate 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionGrowRotate()
	{
		if (self::$transitionGrowRotateLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-grow-rotate {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: transform;
  transition-property: transform;
}
.hvr-grow-rotate:hover, .hvr-grow-rotate:focus, .hvr-grow-rotate:active {
  -webkit-transform: scale(1.1) rotate(4deg);
  transform: scale(1.1) rotate(4deg);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionGrowRotateLoaded = true;
	}

	/**
	 * Load the Wobble Vertical 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionWobbleVertical()
	{
		if (self::$transitionWobbleVerticalLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-wobble-vertical {
  16.65% {
    -webkit-transform: translateY(8px);
    transform: translateY(8px);
  }

  33.3% {
    -webkit-transform: translateY(-6px);
    transform: translateY(-6px);
  }

  49.95% {
    -webkit-transform: translateY(4px);
    transform: translateY(4px);
  }

  66.6% {
    -webkit-transform: translateY(-2px);
    transform: translateY(-2px);
  }

  83.25% {
    -webkit-transform: translateY(1px);
    transform: translateY(1px);
  }

  100% {
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

@keyframes hvr-wobble-vertical {
  16.65% {
    -webkit-transform: translateY(8px);
    transform: translateY(8px);
  }

  33.3% {
    -webkit-transform: translateY(-6px);
    transform: translateY(-6px);
  }

  49.95% {
    -webkit-transform: translateY(4px);
    transform: translateY(4px);
  }

  66.6% {
    -webkit-transform: translateY(-2px);
    transform: translateY(-2px);
  }

  83.25% {
    -webkit-transform: translateY(1px);
    transform: translateY(1px);
  }

  100% {
    -webkit-transform: translateY(0);
    transform: translateY(0);
  }
}

.hvr-wobble-vertical {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-wobble-vertical:hover, .hvr-wobble-vertical:focus, .hvr-wobble-vertical:active {
  -webkit-animation-name: hvr-wobble-vertical;
  animation-name: hvr-wobble-vertical;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-timing-function: ease-in-out;
  animation-timing-function: ease-in-out;
  -webkit-animation-iteration-count: 1;
  animation-iteration-count: 1;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionWobbleVerticalLoaded = true;
	}

	/**
	 * Load the Wobble Horizontal 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionWobbleHorizontal()
	{
		if (self::$transitionWobbleHorizontalLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-wobble-horizontal {
  16.65% {
    -webkit-transform: translateX(8px);
    transform: translateX(8px);
  }

  33.3% {
    -webkit-transform: translateX(-6px);
    transform: translateX(-6px);
  }

  49.95% {
    -webkit-transform: translateX(4px);
    transform: translateX(4px);
  }

  66.6% {
    -webkit-transform: translateX(-2px);
    transform: translateX(-2px);
  }

  83.25% {
    -webkit-transform: translateX(1px);
    transform: translateX(1px);
  }

  100% {
    -webkit-transform: translateX(0);
    transform: translateX(0);
  }
}

@keyframes hvr-wobble-horizontal {
  16.65% {
    -webkit-transform: translateX(8px);
    transform: translateX(8px);
  }

  33.3% {
    -webkit-transform: translateX(-6px);
    transform: translateX(-6px);
  }

  49.95% {
    -webkit-transform: translateX(4px);
    transform: translateX(4px);
  }

  66.6% {
    -webkit-transform: translateX(-2px);
    transform: translateX(-2px);
  }

  83.25% {
    -webkit-transform: translateX(1px);
    transform: translateX(1px);
  }

  100% {
    -webkit-transform: translateX(0);
    transform: translateX(0);
  }
}

.hvr-wobble-horizontal {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-wobble-horizontal:hover, .hvr-wobble-horizontal:focus, .hvr-wobble-horizontal:active {
  -webkit-animation-name: hvr-wobble-horizontal;
  animation-name: hvr-wobble-horizontal;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-timing-function: ease-in-out;
  animation-timing-function: ease-in-out;
  -webkit-animation-iteration-count: 1;
  animation-iteration-count: 1;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionWobbleHorizontalLoaded = true;
	}

	/**
	 * Load the Buzz 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBuzz()
	{
		if (self::$transitionBuzzLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-buzz {
  50% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  100% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }
}

@keyframes hvr-buzz {
  50% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  100% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }
}

.hvr-buzz {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-buzz:hover, .hvr-buzz:focus, .hvr-buzz:active {
  -webkit-animation-name: hvr-buzz;
  animation-name: hvr-buzz;
  -webkit-animation-duration: 0.15s;
  animation-duration: 0.15s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBuzzLoaded = true;
	}

	/**
	 * Load the Buzz Out 2D transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBuzzOut()
	{
		if (self::$transitionBuzzOutLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-buzz-out {
  10% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  20% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }

  30% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  40% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }

  50% {
    -webkit-transform: translateX(2px) rotate(1deg);
    transform: translateX(2px) rotate(1deg);
  }

  60% {
    -webkit-transform: translateX(-2px) rotate(-1deg);
    transform: translateX(-2px) rotate(-1deg);
  }

  70% {
    -webkit-transform: translateX(2px) rotate(1deg);
    transform: translateX(2px) rotate(1deg);
  }

  80% {
    -webkit-transform: translateX(-2px) rotate(-1deg);
    transform: translateX(-2px) rotate(-1deg);
  }

  90% {
    -webkit-transform: translateX(1px) rotate(0);
    transform: translateX(1px) rotate(0);
  }

  100% {
    -webkit-transform: translateX(-1px) rotate(0);
    transform: translateX(-1px) rotate(0);
  }
}

@keyframes hvr-buzz-out {
  10% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  20% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }

  30% {
    -webkit-transform: translateX(3px) rotate(2deg);
    transform: translateX(3px) rotate(2deg);
  }

  40% {
    -webkit-transform: translateX(-3px) rotate(-2deg);
    transform: translateX(-3px) rotate(-2deg);
  }

  50% {
    -webkit-transform: translateX(2px) rotate(1deg);
    transform: translateX(2px) rotate(1deg);
  }

  60% {
    -webkit-transform: translateX(-2px) rotate(-1deg);
    transform: translateX(-2px) rotate(-1deg);
  }

  70% {
    -webkit-transform: translateX(2px) rotate(1deg);
    transform: translateX(2px) rotate(1deg);
  }

  80% {
    -webkit-transform: translateX(-2px) rotate(-1deg);
    transform: translateX(-2px) rotate(-1deg);
  }

  90% {
    -webkit-transform: translateX(1px) rotate(0);
    transform: translateX(1px) rotate(0);
  }

  100% {
    -webkit-transform: translateX(-1px) rotate(0);
    transform: translateX(-1px) rotate(0);
  }
}

.hvr-buzz-out {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
.hvr-buzz-out:hover, .hvr-buzz-out:focus, .hvr-buzz-out:active {
  -webkit-animation-name: hvr-buzz-out;
  animation-name: hvr-buzz-out;
  -webkit-animation-duration: 0.75s;
  animation-duration: 0.75s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: 1;
  animation-iteration-count: 1;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBuzzOutLoaded = true;
	}

	/**
	 * Load the Fade background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionFade()
	{
		if (self::$transitionFadeLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-fade {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  overflow: hidden;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-property: color, background-color;
  transition-property: color, background-color;
}
.hvr-fade:hover, .hvr-fade:focus, .hvr-fade:active {
  background-color: #2098D1;
  color: white;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionFadeLoaded = true;
	}

	/**
	 * Load the Back Pulse background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBackPulse()
	{
		if (self::$transitionBackPulseLoaded) {
			return;
		}

		$inline_css = <<< CSS
@-webkit-keyframes hvr-back-pulse {
  50% {
    background-color: rgba(32, 152, 209, 0.75);
  }
}

@keyframes hvr-back-pulse {
  50% {
    background-color: rgba(32, 152, 209, 0.75);
  }
}

.hvr-back-pulse {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  overflow: hidden;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
  -webkit-transition-property: color, background-color;
  transition-property: color, background-color;
}
.hvr-back-pulse:hover, .hvr-back-pulse:focus, .hvr-back-pulse:active {
  -webkit-animation-name: hvr-back-pulse;
  animation-name: hvr-back-pulse;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-delay: 0.5s;
  animation-delay: 0.5s;
  -webkit-animation-timing-function: linear;
  animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
  background-color: #2098D1;
  color: white;
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBackPulseLoaded = true;
	}

	/**
	 * Load the Sweep To Right background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionSweepToRight()
	{
		if (self::$transitionSweepToRightLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-sweep-to-right {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-sweep-to-right:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
  -webkit-transform-origin: 0 50%;
  transform-origin: 0 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-sweep-to-right:hover, .hvr-sweep-to-right:focus, .hvr-sweep-to-right:active {
  color: white;
}
.hvr-sweep-to-right:hover:before, .hvr-sweep-to-right:focus:before, .hvr-sweep-to-right:active:before {
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionSweepToRightLoaded = true;
	}

	/**
	 * Load the Sweep To Left background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionSweepToLeft()
	{
		if (self::$transitionSweepToLeftLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-sweep-to-left {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-sweep-to-left:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
  -webkit-transform-origin: 100% 50%;
  transform-origin: 100% 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-sweep-to-left:hover, .hvr-sweep-to-left:focus, .hvr-sweep-to-left:active {
  color: white;
}
.hvr-sweep-to-left:hover:before, .hvr-sweep-to-left:focus:before, .hvr-sweep-to-left:active:before {
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionSweepToLeftLoaded = true;
	}

	/**
	 * Load the Sweep To Bottom background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionSweepToBottom()
	{
		if (self::$transitionSweepToBottomLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-sweep-to-bottom {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-sweep-to-bottom:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
  -webkit-transform-origin: 50% 0;
  transform-origin: 50% 0;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-sweep-to-bottom:hover, .hvr-sweep-to-bottom:focus, .hvr-sweep-to-bottom:active {
  color: white;
}
.hvr-sweep-to-bottom:hover:before, .hvr-sweep-to-bottom:focus:before, .hvr-sweep-to-bottom:active:before {
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionSweepToBottomLoaded = true;
	}

	/**
	 * Load the Sweep To Top background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionSweepToTop()
	{
		if (self::$transitionSweepToTopLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-sweep-to-top {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-sweep-to-top:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
  -webkit-transform-origin: 50% 100%;
  transform-origin: 50% 100%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-sweep-to-top:hover, .hvr-sweep-to-top:focus, .hvr-sweep-to-top:active {
  color: white;
}
.hvr-sweep-to-top:hover:before, .hvr-sweep-to-top:focus:before, .hvr-sweep-to-top:active:before {
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionSweepToTopLoaded = true;
	}

	/**
	 * Load the Bounce To Right background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceToRight()
	{
		if (self::$transitionBounceToRightLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-to-right {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-to-right:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
  -webkit-transform-origin: 0 50%;
  transform-origin: 0 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-bounce-to-right:hover, .hvr-bounce-to-right:focus, .hvr-bounce-to-right:active {
  color: white;
}
.hvr-bounce-to-right:hover:before, .hvr-bounce-to-right:focus:before, .hvr-bounce-to-right:active:before {
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
  -webkit-transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
  transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceToRightLoaded = true;
	}

	/**
	 * Load the Bounce To Left background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceToLeft()
	{
		if (self::$transitionBounceToLeftLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-to-left {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-to-left:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
  -webkit-transform-origin: 100% 50%;
  transform-origin: 100% 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-bounce-to-left:hover, .hvr-bounce-to-left:focus, .hvr-bounce-to-left:active {
  color: white;
}
.hvr-bounce-to-left:hover:before, .hvr-bounce-to-left:focus:before, .hvr-bounce-to-left:active:before {
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
  -webkit-transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
  transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceToLeftLoaded = true;
	}

	/**
	 * Load the Bounce To Bottom background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceToBottom()
	{
		if (self::$transitionBounceToBottomLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-to-bottom {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-to-bottom:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
  -webkit-transform-origin: 50% 0;
  transform-origin: 50% 0;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-bounce-to-bottom:hover, .hvr-bounce-to-bottom:focus, .hvr-bounce-to-bottom:active {
  color: white;
}
.hvr-bounce-to-bottom:hover:before, .hvr-bounce-to-bottom:focus:before, .hvr-bounce-to-bottom:active:before {
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
  -webkit-transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
  transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceToBottomLoaded = true;
	}

	/**
	 * Load the Bounce To Top background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionBounceToTop()
	{
		if (self::$transitionBounceToTopLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-bounce-to-top {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
}
.hvr-bounce-to-top:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
  -webkit-transform-origin: 50% 100%;
  transform-origin: 50% 100%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.5s;
  transition-duration: 0.5s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-bounce-to-top:hover, .hvr-bounce-to-top:focus, .hvr-bounce-to-top:active {
  color: white;
}
.hvr-bounce-to-top:hover:before, .hvr-bounce-to-top:focus:before, .hvr-bounce-to-top:active:before {
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
  -webkit-transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
  transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionBounceToTopLoaded = true;
	}

	/**
	 * Load the Radial Out background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionRadialOut()
	{
		if (self::$transitionRadialOutLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-radial-out {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  overflow: hidden;
  background: #e1e1e1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-radial-out:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  border-radius: 100%;
  -webkit-transform: scale(0);
  transform: scale(0);
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-radial-out:hover, .hvr-radial-out:focus, .hvr-radial-out:active {
  color: white;
}
.hvr-radial-out:hover:before, .hvr-radial-out:focus:before, .hvr-radial-out:active:before {
  -webkit-transform: scale(2);
  transform: scale(2);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionRadialOutLoaded = true;
	}

	/**
	 * Load the Radial In background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionRadialIn()
	{
		if (self::$transitionRadialInLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-radial-in {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  overflow: hidden;
  background: #2098D1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-radial-in:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #e1e1e1;
  border-radius: 100%;
  -webkit-transform: scale(2);
  transform: scale(2);
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-radial-in:hover, .hvr-radial-in:focus, .hvr-radial-in:active {
  color: white;
}
.hvr-radial-in:hover:before, .hvr-radial-in:focus:before, .hvr-radial-in:active:before {
  -webkit-transform: scale(0);
  transform: scale(0);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionRadialInLoaded = true;
	}

	/**
	 * Load the Rectangle In background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionRectangleIn()
	{
		if (self::$transitionRectangleInLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-rectangle-in {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #2098D1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-rectangle-in:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #e1e1e1;
  -webkit-transform: scale(1);
  transform: scale(1);
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-rectangle-in:hover, .hvr-rectangle-in:focus, .hvr-rectangle-in:active {
  color: white;
}
.hvr-rectangle-in:hover:before, .hvr-rectangle-in:focus:before, .hvr-rectangle-in:active:before {
  -webkit-transform: scale(0);
  transform: scale(0);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionRectangleInLoaded = true;
	}

	/**
	 * Load the Rectangle Out background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionRectangleOut()
	{
		if (self::$transitionRectangleOutLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-rectangle-out {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #e1e1e1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-rectangle-out:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #2098D1;
  -webkit-transform: scale(0);
  transform: scale(0);
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-rectangle-out:hover, .hvr-rectangle-out:focus, .hvr-rectangle-out:active {
  color: white;
}
.hvr-rectangle-out:hover:before, .hvr-rectangle-out:focus:before, .hvr-rectangle-out:active:before {
  -webkit-transform: scale(1);
  transform: scale(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionRectangleOutLoaded = true;
	}

	/**
	 * Load the Shutter In Horizontal background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionShutterInHorizontal()
	{
		if (self::$transitionShutterInHorizontalLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-shutter-in-horizontal {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #2098D1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-shutter-in-horizontal:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #e1e1e1;
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
  -webkit-transform-origin: 50%;
  transform-origin: 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-shutter-in-horizontal:hover, .hvr-shutter-in-horizontal:focus, .hvr-shutter-in-horizontal:active {
  color: white;
}
.hvr-shutter-in-horizontal:hover:before, .hvr-shutter-in-horizontal:focus:before, .hvr-shutter-in-horizontal:active:before {
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionShutterInHorizontalLoaded = true;
	}

	/**
	 * Load the Shutter Out Horizontal background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionShutterOutHorizontal()
	{
		if (self::$transitionShutterOutHorizontalLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-shutter-out-horizontal {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #e1e1e1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-shutter-out-horizontal:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #2098D1;
  -webkit-transform: scaleX(0);
  transform: scaleX(0);
  -webkit-transform-origin: 50%;
  transform-origin: 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-shutter-out-horizontal:hover, .hvr-shutter-out-horizontal:focus, .hvr-shutter-out-horizontal:active {
  color: white;
}
.hvr-shutter-out-horizontal:hover:before, .hvr-shutter-out-horizontal:focus:before, .hvr-shutter-out-horizontal:active:before {
  -webkit-transform: scaleX(1);
  transform: scaleX(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionShutterOutHorizontalLoaded = true;
	}

	/**
	 * Load the Shutter In Vertical background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionShutterInVertical()
	{
		if (self::$transitionShutterInVerticalLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-shutter-in-vertical {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #2098D1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-shutter-in-vertical:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #e1e1e1;
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
  -webkit-transform-origin: 50%;
  transform-origin: 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-shutter-in-vertical:hover, .hvr-shutter-in-vertical:focus, .hvr-shutter-in-vertical:active {
  color: white;
}
.hvr-shutter-in-vertical:hover:before, .hvr-shutter-in-vertical:focus:before, .hvr-shutter-in-vertical:active:before {
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionShutterInVerticalLoaded = true;
	}

	/**
	 * Load the Shutter Out Vertical background transition declaratively
	 * Based on Hover.css v2.3.1
	 * http://ianlunn.github.io/Hover
	 */
	static function loadTransitionShutterOutVertical()
	{
		if (self::$transitionShutterOutVerticalLoaded) {
			return;
		}

		$inline_css = <<< CSS
.hvr-shutter-out-vertical {
  -webkit-transform: perspective(1px) translateZ(0);
  transform: perspective(1px) translateZ(0);
  box-shadow: 0 0 1px rgba(0, 0, 0, 0);
  position: relative;
  background: #e1e1e1;
  -webkit-transition-property: color;
  transition-property: color;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
}
.hvr-shutter-out-vertical:before {
  content: "";
  position: absolute;
  z-index: -1;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #2098D1;
  -webkit-transform: scaleY(0);
  transform: scaleY(0);
  -webkit-transform-origin: 50%;
  transform-origin: 50%;
  -webkit-transition-property: transform;
  transition-property: transform;
  -webkit-transition-duration: 0.3s;
  transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-out;
  transition-timing-function: ease-out;
}
.hvr-shutter-out-vertical:hover, .hvr-shutter-out-vertical:focus, .hvr-shutter-out-vertical:active {
  color: white;
}
.hvr-shutter-out-vertical:hover:before, .hvr-shutter-out-vertical:focus:before, .hvr-shutter-out-vertical:active:before {
  -webkit-transform: scaleY(1);
  transform: scaleY(1);
}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$transitionShutterOutVerticalLoaded = true;
	}

	/**
	 * Load the 2d transitions stylesheet if needed
	 */
	static function load2DTransitions()
	{	    
	    $attributes = array();
	    if (Factory::getApplication()->isClient('site') && SYWPLugin::getLazyStylesheet() > 0) {
	        $attributes['rel'] = 'lazy-stylesheet';
	    }
		
	    self::getWebAssetManager()->registerAndUseStyle('syw.transitions.2d', 'syw/2d-transitions.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	}

	/**
	 * Load the background transitions stylesheet if needed
	 */
	static function loadBGTransitions()
	{	    
	    $attributes = array();
	    if (Factory::getApplication()->isClient('site') && SYWPLugin::getLazyStylesheet() > 0) {
	        $attributes['rel'] = 'lazy-stylesheet';
	    }
		
	    self::getWebAssetManager()->registerAndUseStyle('syw.transitions.bg', 'syw/bg-transitions.min.css', ['relative' => true, 'version' => 'auto'], $attributes);
	}

	/**
	 * Load the CSS needed for modals when Bootstrap CSS is missing (CSS for Bootstrap 4)
	 */
	static function loadBootstrapModals()
	{
		self::getWebAssetManager()->registerAndUseStyle('syw.bootstrap.modal', 'syw/bootstrap-modals.min.css', ['relative' => true, 'version' => 'auto']);
	}

	/**
	 * Loads the CSS needed for pure modals
	 */
	static function loadPureModalsCss()
	{
		if (self::$puremodalscssLoaded) {
			return;
		}

		$inline_css = <<< CSS
			.puremodal { display: none; }
			.puremodal-header { 
				margin-bottom: 10px; 
				padding-bottom: 10px; border-bottom: 
				1px solid #eee;
			}
			.puremodal-header h3 { margin: 0; }
			.puremodal-body iframe { 
				width: 100%; 
				overflow: auto;
				border: 0 !important; 
				display: block;
			}
			.puremodal-footer { 
				margin-top: 10px; 
				padding-top: 10px; 
				border-top: 1px solid #eee; text-align: right;
			}
CSS;
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$puremodalscssLoaded = true;
	}

	/**
	 * Loads the CSS needed for Bootstrap modals
	 *
	 * @param string $selector
	 * @param array $attributes
	 * @param number $bootstrap_version
	 */
	static function loadBootstrapModalsCss($selector = 'modal', $attributes = array('width' => '600'), $bootstrap_version = 5)
	{
		if (self::$bootstrapmodalscssLoaded) {
			return;
		}

		if ($bootstrap_version < 3) {
			$inline_css = <<< CSS
				@media (min-width: 768px) {
					#{$selector} {
						max-width: 80%;
						left: 50%;
						margin-left: auto;
						-webkit-transform: translate(-50%); -ms-transform: translate(-50%); transform: translate(-50%);
						width: {$attributes['width']}px;
					}
				}			
CSS;
		} else {
			$inline_css = <<< CSS
				@media (min-width: 768px) {
					#{$selector} .modal-dialog {
						width: 80%;
						max-width: {$attributes['width']}px;
					}
				}
CSS;
		}
		
		self::getWebAssetManager()->addInlineStyle(self::compress($inline_css));

		self::$bootstrapmodalscssLoaded = true;
	}

	/**
	 * Load the CSS needed for accessibility element visibility
	 */
	static function loadAccessibilityVisibilityStyles()
	{
	    if (self::$accessibleVisibilityLoaded) {
	        return;
	    }
	    
	    self::getWebAssetManager()->addInlineStyle('.element-invisible { position: absolute !important; height: 1px; width: 1px; overflow: hidden; clip: rect(1px, 1px, 1px, 1px); }');

	    self::$accessibleVisibilityLoaded = true;
	}

	/**
	 * Compress inline CSS
	 * @param string $inlineCSS
	 * @return string
	 */
	static function compress($inlineCSS = '', $remove_comments = false)
	{
		if ($remove_comments) {
			$inlineCSS = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $inlineCSS);
		}

		return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $inlineCSS);
	}

}
?>