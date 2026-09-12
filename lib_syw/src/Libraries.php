<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class Libraries
{
	static $jqcLoaded = false;
	static $jqcMultipackLoaded = false;

	static $instantiatePureModalLoaded = array();
	static $instantiateBootstrapModalLoaded = array();

	static $compareLoaded = false;
	
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
	 * Load purePajinate (pure javascript)
	 * v1.0.2
	 * https://github.com/obuisard/purePajinate
	 * IE10+ compatible
	 */
	public static function loadPurePajinate($remote = false, $defer = false, $async = false)
	{		
		self::getWebAssetManager()->registerAndUseScript('syw.purepajinate', 'syw/purepajinate/purePajinate.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);
		self::getWebAssetManager()->registerAndUseScript('syw.purepajinate-es5', 'syw/purepajinate/purePajinate-es5.min.js', ['relative' => true, 'version' => 'auto'], ['nomodule' => true, 'defer' => true]);
	}

	/*
	 * function that makes it easier to switch between libraries that handle pagination written in pure Javascript
	 */
	public static function loadPurePagination($remote = false, $defer = false, $async = false)
	{
		self::loadPurePajinate($remote, $defer, $async);
	}
	
	/**
	 * Load pureTreeMenu (pure javascript)
	 * IE11+ compatible
	 */
	public static function loadPureTreeMenu($remote = false, $defer = false, $async = false)
	{
	    self::getWebAssetManager()->registerAndUseScript('syw.puretreemenu', 'syw/puretreemenu/puretreemenu.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);
	    self::getWebAssetManager()->registerAndUseScript('syw.puretreemenu-es5', 'syw/puretreemenu/puretreemenu-es5.min.js', ['relative' => true, 'version' => 'auto'], ['nomodule' => true, 'defer' => true]);
	}

	/**
	 * Load Tiny Slider (pure javascript)
	 * v2.9.2
	 * https://github.com/ganlanyuan/tiny-slider
	 * IE8+ compatible
	 * the CSS file has been modified to add styling of the dots
	 * the JS file has been modified to add RTL support
	 */
	public static function loadTinySlider($remote = false, $defer = false, $async = false)
	{
		// WARNING loading the library remotely won't have the RTL fix
// 		$remote = false;

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}		

// 		if ($remote) {
// 			self::getWebAssetManager()->registerAndUseStyle('syw.tinyslider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.css');
// 			self::getWebAssetManager()->registerAndUseScript('syw.tinyslider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.js', [], $attributes);
// 			self::getWebAssetManager()->addInlineStyle('.tns-slider{-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}.tns-nav{text-align:center;margin:10px 0}.tns-nav>[aria-controls]{width:9px;height:9px;padding:0;margin:0 5px;border-radius:50%;background:#ddd;border:0}.tns-nav>.tns-nav-active{background:#999}');
// 		} else {
			self::getWebAssetManager()->registerAndUseStyle('syw.tinyslider', 'syw/tinyslider/tiny-slider.min.css', ['relative' => true, 'version' => 'auto']);
			self::getWebAssetManager()->registerAndUseScript('syw.tinyslider', 'syw/tinyslider/tiny-slider.min.js', ['relative' => true, 'version' => 'auto'], $attributes);
// 		}
	}

	/**
	 * Load Tingle (pure javascript)
	 * v0.15.2
	 * https://github.com/robinparisi/tingle
	 * ? compatible
	 */
	public static function loadTingle($remote = false, $defer = false, $async = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		if ($remote) {
			self::getWebAssetManager()->registerAndUseStyle('syw.tingle', 'https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle.min.css');
			self::getWebAssetManager()->registerAndUseScript('syw.tingle', 'https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle.min.js', [], $attributes);
		} else {
			self::getWebAssetManager()->registerAndUseStyle('syw.tingle', 'syw/tingle/tingle.min.css', ['relative' => true, 'version' => 'auto']);
			self::getWebAssetManager()->registerAndUseScript('syw.tingle', 'syw/tingle/tingle.min.js', ['relative' => true, 'version' => 'auto'], $attributes);
		}
	}

	/*
	 * function that makes it easier to switch between libraries that handle modals written in pure Javascript
	 */
	public static function loadPureModal($remote = false, $defer = false, $async = false)
	{
		self::loadTingle($remote, $defer, $async);
	}

	/**
	 * Loads the code that instantiates and sets up the modals written in pure Javascript
	 *
	 * @param string $selector
	 */
	public static function instantiatePureModal($selector = 'modal')
	{
		if (in_array($selector, self::$instantiatePureModalLoaded)) {
			return;
		}

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$close_label = Text::_('LIB_SYW_MODAL_CLOSE');

		$selector_variable = str_replace('-', '_', $selector); // javascript does not like - in the name

		$inline_js = <<< JS
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState === "complete") {
				
					var {$selector_variable} = new tingle.modal({
						stickyFooter: true,
						closeLabel: "{$close_label}",
						onOpen: function() {
							document.querySelector(".tingle-modal .puremodal-close").addEventListener("click", function() { {$selector_variable}.close(); });
							
							document.querySelector("body").classList.add('modal-open');
							var event = document.createEvent('Event');
							event.initEvent('modalopen', true, true);
							document.dispatchEvent(event);
						},
						onClose: function() {
							this.setContent("");
							document.querySelector("#{$selector}Label").textContent = "";
							document.querySelector("#{$selector} .iframe").setAttribute("src", "about:blank");
							
							document.querySelector("body").classList.remove('modal-open');
							var event = document.createEvent('Event');
							event.initEvent('modalclose', true, true);
							document.dispatchEvent(event);
						}
					});
					
					var clickable = document.querySelectorAll(".{$selector}");
					for (var i = 0; i < clickable.length; i++) {
						clickable[i].addEventListener("click", function() {
						
							var dataTitle = this.getAttribute("data-modaltitle");
							if (typeof (dataTitle) !== "undefined" && dataTitle !== null) {
								document.querySelector("#{$selector}Label").textContent = dataTitle;
							}
							
							var dataURL = this.getAttribute("href");
							document.querySelector("#{$selector} .iframe").setAttribute("src", dataURL);
							
							{$selector_variable}.setContent(document.querySelector("#{$selector}").innerHTML);
							{$selector_variable}.open();
						});
					}
					
				}
			});
JS;

		self::getWebAssetManager()->addInlineScript(self::compress($inline_js));

		self::$instantiatePureModalLoaded[] = $selector;
	}

	/**
	 * Loads the code that instantiates and sets up the modals for Bootstrap
	 *
	 * @param string $selector
	 * @param array $attributes
	 * @param number $bootstrap_version
	 */
	public static function instantiateBootstrapModal($selector = 'modal', $attributes = array('default_title' => ''), $bootstrap_version = 5)
	{
		if (in_array($selector, self::$instantiateBootstrapModalLoaded)) {
			return;
		}

		if ($bootstrap_version < 5) {
			$inline_js = <<< JS
				jQuery(document).ready(function($) {
				
					$('.{$selector}').on('click', function () {
						var dataTitle = $(this).attr('data-modaltitle');
						if (typeof (dataTitle) !== 'undefined' && dataTitle !== null) {
							$('#{$selector}').find('.modal-title').text(dataTitle);
						}
						var dataURL = $(this).attr('href');
						$('#{$selector}').find('.iframe').attr('src', dataURL);
					});
					
					$('#{$selector}').on('show.bs.modal', function() {
						$('body').addClass('modal-open');
						var event = document.createEvent('Event');
						event.initEvent('modalopen', true, true);
						document.dispatchEvent(event);
JS;
			
			if (isset($attributes['height'])) {
				$inline_js .= <<< JS
					}).on('shown.bs.modal', function() {
						var modal_body = $(this).find('.modal-body');
						modal_body.css({'max-height': {$attributes['height']}});
						var padding = parseInt(modal_body.css('padding-top')) + parseInt(modal_body.css('padding-bottom'));
						modal_body.find('.iframe').css({'height': ({$attributes['height']} - padding)});
JS;
			}
			
			$inline_js .= <<< JS
				}).on('hide.bs.modal', function () {
					$(this).find('.modal-title').text('{$attributes['default_title']}');
					var modal_body = $(this).find('.modal-body');
					modal_body.css({'max-height': 'initial'});
					modal_body.find('.iframe').attr('src', 'about:blank');
					$('body').removeClass('modal-open');
					var event = document.createEvent('Event');
					event.initEvent('modalclose', true, true);
					document.dispatchEvent(event);
				});
				
			});
JS;
		} else {
			// event.relatedTarget : elt that triggered the call

			$inline_js = <<< JS
				document.addEventListener("readystatechange", function(event) {
					if (event.target.readyState === "complete") {
					
						var modal = document.getElementById("{$selector}");
					
						modal.addEventListener("show.bs.modal", function (event) {						
							var link = event.relatedTarget;
							if (typeof (link) !== "undefined" && link !== null) {
								var dataTitle = link.getAttribute("data-modaltitle");
								if (typeof (dataTitle) !== "undefined" && dataTitle !== null) {
									this.querySelector(".modal-title").innerText = dataTitle;
								}
								var dataURL = link.getAttribute("href");
								this.querySelector(".iframe").setAttribute("src", dataURL);
							}					
							document.querySelector("body").classList.add("modal-open");
							var event = document.createEvent("Event"); 
							event.initEvent("modalopen", true, true); 
							document.dispatchEvent(event);
						}, this);
						
						modal.addEventListener("shown.bs.modal", function (event) {
							var modal_body = this.querySelector(".modal-body");
						}, this);
						
						modal.addEventListener("hide.bs.modal", function (event) {
							this.querySelector(".modal-title").innerText = "{$attributes['default_title']}";
							var modal_body = this.querySelector(".modal-body");
							modal_body.querySelector(".iframe").setAttribute("src", "about:blank");
							document.querySelector("body").classList.remove("modal-open");
							var event = document.createEvent("Event"); 
							event.initEvent("modalclose", true, true); 
							document.dispatchEvent(event);
						}, this);
					}
				});
JS;
		}		

		self::getWebAssetManager()->addInlineScript(self::compress($inline_js));

		self::$instantiateBootstrapModalLoaded[] = $selector;
	}

	/**
	 * Load Owl Carousel (jQuery plugin)
	 * v2.3.4
	 * https://github.com/OwlCarousel2/OwlCarousel2
	 */
	public static function loadOwlCarousel($remote = false, $defer = false, $async = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		if ($remote) {
			self::getWebAssetManager()->registerAndUseStyle('syw.owlcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css');
			self::getWebAssetManager()->registerAndUseScript('syw.owlcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', [], $attributes, ['jquery-migrate']);
		} else {
			self::getWebAssetManager()->registerAndUseStyle('syw.owlcarousel', 'syw/owlcarousel/owl.carousel.min.css', ['relative' => true, 'version' => 'auto']);
			self::getWebAssetManager()->registerAndUseScript('syw.owlcarousel', 'syw/owlcarousel/owl.carousel.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
		}
	}

	/**
	 * Load the carousel carouFredSel library (jQuery plugins)
	 * v6.2.1
	 */
	public static function loadCarousel($throttle = true, $touch = true, $mousewheel = false, $transit = false, $defer = false, $async = false, $remote = false)
	{
		if (self::$jqcMultipackLoaded && !$mousewheel && !$transit) {
			return;
		}

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$will_use_multipack = false;
		if (!self::$jqcLoaded && $throttle && $touch && !$mousewheel && !$transit && !JDEBUG && !$remote) {
			$will_use_multipack = true;
		}

		if ($throttle && !self::$jqcMultipackLoaded && !$will_use_multipack) {
			self::loadCarousel_throttle($defer, $async, $remote);
		}

		if ($touch && !self::$jqcMultipackLoaded && !$will_use_multipack) {
			self::loadCarousel_touch($defer, $async, $remote);
		}

		if ($mousewheel) {
			self::loadCarousel_mousewheel($defer, $async, $remote);
		}

		if ($transit) {
			self::loadCarousel_transit($defer, $async, $remote);
		}

		if (!self::$jqcMultipackLoaded && $will_use_multipack) { // multi-pack is not used when debug or when remote

			self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel', 'syw/carousel/jquery.carouFredSel.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);

			self::$jqcMultipackLoaded = true;
		} else {

			if (self::$jqcLoaded) {
				return;
			}

			if ($remote) {
				self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.caroufredsel/6.2.1/jquery.carouFredSel.packed.js', [], $attributes, ['jquery-migrate']); // only minified version
			} else {
			    self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel', 'syw/carousel/jquery.carouFredSel-6.2.1.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
			}

			self::$jqcLoaded = true;
		}
	}

	/**
	 * jquery.ba-throttle-debounce
	 * v1.1
	 */
	public static function loadCarousel_throttle($defer = false, $async = false, $remote = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}		

		if ($remote) {
		    self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.throttle', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce.min.js', [], $attributes, ['jquery-migrate']);
		} else {
			self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.throttle', 'syw/carousel/jquery.ba-throttle-debounce.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
		}
	}

	/**
	 * jquery.touchSwipe
	 * v1.6.18
	 */
	public static function loadCarousel_touch($defer = false, $async = false, $remote = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		if ($remote) {
		    self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.touch', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.18/jquery.touchSwipe.min.js', [], $attributes, ['jquery-migrate']);
		} else {
			self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.touch', 'syw/carousel/jquery.touchSwipe.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
		}
	}

	/**
	 * jquery.mousewheel
	 * v3.0.6
	 */
	public static function loadCarousel_mousewheel($defer = false, $async = false, $remote = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}		

		self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.mousewheel', 'syw/carousel/jquery.mousewheel.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
	}

	/**
	 * jquery.transit
	 * v?
	 */
	public static function loadCarousel_transit($defer = false, $async = false, $remote = false)
	{
		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		self::getWebAssetManager()->registerAndUseScript('syw.caroufredsel.transit', 'syw/carousel/jquery.transit.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery-migrate']);
	}

	/**
	 * Load the comparison version function if needed
	 */
	public static function loadCompareVersions()
	{
		if (self::$compareLoaded) {
			return;
		}

		// returns false if version e > t (version is 1.3.2 for example)
		$compareScript = 'function SYWCompareVersions(e,t){var r=!1;if(e==t)return!0;"object"!=typeof e&&(e=e.toString().split(".")),"object"!=typeof t&&(t=t.toString().split("."));for(var o=0;o<Math.max(e.length,t.length);o++){if(void 0==e[o]&&(e[o]=0),void 0==t[o]&&(t[o]=0),Number(e[o])<Number(t[o])){r=!0;break}if(e[o]!=t[o])break}return r};';

		self::getWebAssetManager()->addInlineScript($compareScript);

		self::$compareLoaded = true;
	}

	/**
	 * Compress inline JS
	 * @param string $inlineJS
	 * @return string
	 */
	public static function compress($inlineJS = '', $remove_comments = false)
	{
		if ($remove_comments) {
			$inlineJS = preg_replace('!\/\*[\s\S]*?\*\/|\/\/.*!', '', $inlineJS);
		}

		return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $inlineJS);
	}

}
?>
