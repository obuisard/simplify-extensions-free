<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class Libraries
{
	static $purePajinateLoaded = false;
	static $lazysizesLoaded = false;
	static $tinySliderLoaded = false;
	static $tingleLoaded = false;

	static $jq_owlLoaded = false;

	static $jqcLoaded = false;
	static $jqcMultipackLoaded = false;
	static $jqcthrottleLoaded = false;
	static $jqctouchLoaded = false;
	static $jqcmousewheelLoaded = false;
	static $jqctransitLoaded = false;

	static $highresLoaded = array();
	static $instantiatePureModalLoaded = array();

	static $compareLoaded = false;

	/**
	 * Load purePajinate (pure javascript)
	 * v1.0.0
	 * https://github.com/obuisard/purePajinate
	 * IE10+ compatible
	 */
	static function loadPurePajinate($remote = false, $defer = false, $async = false)
	{
		if (self::$purePajinateLoaded) {
			return;
		}

		$minified = (JDEBUG) ? '' : '.min';

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wam->registerAndUseScript('syw.purePajinate', 'syw/purepajinate/purePajinate' . $minified . '.js', ['relative' => true, 'version' => 'auto'], $attributes);

		//HTMLHelper::script('syw/purepajinate/purePajinate' . $minified . '.js', array('relative' => true, 'version' => 'auto'), $attributes);

		self::$purePajinateLoaded = true;
	}

	/*
	 * function that makes it easier to switch between libraries that handle pagination written in pure Javascript
	 */
	static function loadPurePagination($remote = false, $defer = false, $async = false)
	{
		self::loadPurePajinate($remote, $defer, $async);
	}

	/**
	 * Load Tiny Slider (pure javascript)
	 * v2.9.2
	 * https://github.com/ganlanyuan/tiny-slider
	 * IE8+ compatible
	 * the CSS file has been modified to add styling of the dots
	 * the JS file has been modified to add RTL support
	 */
	static function loadTinySlider($remote = false, $defer = false, $async = false)
	{
		if (self::$tinySliderLoaded) {
			return;
		}

		$minified = (JDEBUG) ? '' : '.min';

		// WARNING loading the library remotely won't have the RTL fix
		$remote = false;

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {

			$wam->registerAndUseStyle('syw.tinyslider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.css');
			$wam->registerAndUseScript('syw.tinyslider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.js', [], $attributes);

			$wam->addInlineStyle('.tns-slider{-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}.tns-nav{text-align:center;margin:10px 0}.tns-nav>[aria-controls]{width:9px;height:9px;padding:0;margin:0 5px;border-radius:50%;background:#ddd;border:0}.tns-nav>.tns-nav-active{background:#999}');

			//$doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.css');

			// style additions
			//$doc->addStyleDeclaration('.tns-slider{-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}.tns-nav{text-align:center;margin:10px 0}.tns-nav>[aria-controls]{width:9px;height:9px;padding:0;margin:0 5px;border-radius:50%;background:#ddd;border:0}.tns-nav>.tns-nav-active{background:#999}');

			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider' . $minified . '.js');
		} else {

			$wam->registerAndUseStyle('syw.tinyslider', 'syw/tinyslider/tiny-slider' . $minified . '.css', ['relative' => true, 'version' => 'auto']);
			$wam->registerAndUseScript('syw.tinyslider', 'syw/tinyslider/tiny-slider' . $minified . '.js', ['relative' => true, 'version' => 'auto'], $attributes);

			//HTMLHelper::stylesheet('syw/tinyslider/tiny-slider' . $minified . '.css', array('relative' => true, 'version' => 'auto'));
			//HTMLHelper::script('syw/tinyslider/tiny-slider' . $minified . '.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$tinySliderLoaded = true;
	}

	/**
	 * Load Tingle (pure javascript)
	 * v0.15.2
	 * https://github.com/robinparisi/tingle
	 * ? compatible
	 */
	static function loadTingle($remote = false, $defer = false, $async = false)
	{
		if (self::$tingleLoaded) {
			return;
		}

		$minified = (JDEBUG) ? '' : '.min';

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {

			$wam->registerAndUseStyle('syw.tingle', 'https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle' . $minified . '.css');
			$wam->registerAndUseScript('syw.tingle', 'https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle' . $minified . '.js', [], $attributes);

			//$doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle' . $minified . '.css');
			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.2/tingle' . $minified . '.js');
		} else {

			$wam->registerAndUseStyle('syw.tingle', 'syw/tingle/tingle' . $minified . '.css', ['relative' => true, 'version' => 'auto']);
			$wam->registerAndUseScript('syw.tingle', 'syw/tingle/tingle' . $minified . '.js', ['relative' => true, 'version' => 'auto'], $attributes);

			//HTMLHelper::stylesheet('syw/tingle/tingle' . $minified . '.css', array('relative' => true, 'version' => 'auto'));
			//HTMLHelper::script('syw/tingle/tingle' . $minified . '.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$tingleLoaded = true;
	}

	/*
	 * function that makes it easier to switch between libraries that handle modals written in pure Javascript
	 */
	static function loadPureModal($remote = false, $defer = false, $async = false)
	{
		self::loadTingle($remote, $defer, $async);
	}

	/*
	 * loads the code that instantiates and sets up the modals written in pure Javascript
	 */
	static function instantiatePureModal($selector = 'modal')
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

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wam->addInlineScript(self::compress($inline_js));
		//Factory::getDocument()->addScriptDeclaration(self::compress($inline_js));

		self::$instantiatePureModalLoaded[] = $selector;
	}

	/**
	 * Load Owl Carousel (jQuery plugin)
	 * v2.3.4
	 * https://github.com/OwlCarousel2/OwlCarousel2
	 */
	static function loadOwlCarousel($remote = false, $defer = false, $async = false)
	{
		if (self::$jq_owlLoaded) {
			return;
		}

		$minified = (JDEBUG) ? '' : '.min';

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {

			$wam->registerAndUseStyle('syw.owlcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel' . $minified . '.css');
			$wam->registerAndUseScript('syw.owlcarousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel' . $minified . '.js', [], $attributes, ['jquery']);

			//$doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel' . $minified . '.css');
			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel' . $minified . '.js');
		} else {

			$wam->registerAndUseStyle('syw.owlcarousel', 'syw/owlcarousel/owl.carousel' . $minified . '.css', ['relative' => true, 'version' => 'auto']);
			$wam->registerAndUseScript('syw.owlcarousel', 'syw/owlcarousel/owl.carousel' . $minified . '.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);

			//HTMLHelper::stylesheet('syw/owlcarousel/owl.carousel' . $minified . '.css', array('relative' => true, 'version' => 'auto'));
			//HTMLHelper::script('syw/owlcarousel/owl.carousel' . $minified . '.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$jq_owlLoaded = true;
	}

	/**
	 * Load Lazysizes (pure javascript)
	 * v5.2.0
	 * https://github.com/aFarkas/lazysizes
	 */
	static function loadLazysizes($remote = false, $defer = false, $async = false)
	{
		if (self::$lazysizesLoaded) {
			return;
		}

		$minified = (JDEBUG) ? '' : '.min';

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {
			$wam->registerAndUseScript('syw.lazysizes', 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.0/lazysizes.min.js', [], $attributes, ['jquery']);
			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.2.0/lazysizes.min.js'); // only minified version
		} else {
			$wam->registerAndUseScript('syw.lazysizes', 'syw/lazysizes/lazysizes' . $minified . '.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
			//HTMLHelper::script('syw/lazysizes/lazysizes' . $minified . '.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$lazysizesLoaded = true;
	}

	/**
	 * Load the carousel carouFredSel library (jQuery plugins)
	 * v6.2.1
	 */
	static function loadCarousel($throttle = true, $touch = true, $mousewheel = false, $transit = false, $defer = false, $async = false, $remote = false)
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

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if (!self::$jqcMultipackLoaded && $will_use_multipack) { // multi-pack is not used when debug or when remote

			$wam->registerAndUseScript('syw.caroufredsel', 'syw/carousel/jquery.carouFredSel.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
			//HTMLHelper::script('syw/carousel/jquery.carouFredSel.min.js', array('relative' => true, 'version' => 'auto'), $attributes);

			self::$jqcMultipackLoaded = true;
		} else {

			if (self::$jqcLoaded) {
				return;
			}

			if ($remote) {
				$wam->registerAndUseScript('syw.caroufredsel', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.caroufredsel/6.2.1/jquery.carouFredSel.packed.js', [], $attributes, ['jquery']); // only minified version
				//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.caroufredsel/6.2.1/jquery.carouFredSel.packed.js'); // only minified version
			} else {
				$wam->registerAndUseScript('syw.caroufredsel', 'syw/carousel/jquery.carouFredSel-6.2.1' . ((JDEBUG) ? '' : '-packed') . '.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
				//HTMLHelper::script('syw/carousel/jquery.carouFredSel-6.2.1' . ((JDEBUG) ? '' : '-packed') . '.js', array('relative' => true, 'version' => 'auto'), $attributes);
			}

			self::$jqcLoaded = true;
		}
	}

	/**
	 * jquery.ba-throttle-debounce
	 * v1.1
	 */
	static function loadCarousel_throttle($defer = false, $async = false, $remote = false)
	{
		if (self::$jqcthrottleLoaded) {
			return;
		}

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {
			$wam->registerAndUseScript('syw.caroufredsel.throttle', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce' . ((JDEBUG) ? '' : '.min') . '.js', [], $attributes, ['jquery']);
			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce' . ((JDEBUG) ? '' : '.min') . '.js');
		} else {
			$wam->registerAndUseScript('syw.caroufredsel.throttle', 'syw/carousel/jquery.ba-throttle-debounce.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
			//HTMLHelper::script('syw/carousel/jquery.ba-throttle-debounce.min.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$jqcthrottleLoaded = true;
	}

	/**
	 * jquery.touchSwipe
	 * v1.6.18
	 */
	static function loadCarousel_touch($defer = false, $async = false, $remote = false)
	{
		if (self::$jqctouchLoaded) {
			return;
		}

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		if ($remote) {
			$wam->registerAndUseScript('syw.caroufredsel.touch', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.18/jquery.touchSwipe' . ((JDEBUG) ? '' : '.min') . '.js', [], $attributes, ['jquery']);
			//$doc->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.touchswipe/1.6.18/jquery.touchSwipe' . ((JDEBUG) ? '' : '.min') . '.js');
		} else {
			$wam->registerAndUseScript('syw.caroufredsel.touch', 'syw/carousel/jquery.touchSwipe.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
			//HTMLHelper::script('syw/carousel/jquery.touchSwipe.min.js', array('relative' => true, 'version' => 'auto'), $attributes);
		}

		self::$jqctouchLoaded = true;
	}

	/**
	 * jquery.mousewheel
	 * v3.0.6
	 */
	static function loadCarousel_mousewheel($defer = false, $async = false, $remote = false)
	{
		if (self::$jqcmousewheelLoaded) {
			return;
		}

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->registerAndUseScript('syw.caroufredsel.mousewheel', 'syw/carousel/jquery.mousewheel.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
		//HTMLHelper::script('syw/carousel/jquery.mousewheel.min.js', array('relative' => true, 'version' => 'auto'), $attributes);

		self::$jqcmousewheelLoaded = true;
	}

	/**
	 * jquery.transit
	 * v?
	 */
	static function loadCarousel_transit($defer = false, $async = false, $remote = false)
	{
		if (self::$jqctransitLoaded) {
			return;
		}

		$attributes = array();
		if ($defer) {
			$attributes['defer'] = true;
		}
		if ($async) {
			$attributes['async'] = 'async';
		}

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->registerAndUseScript('syw.caroufredsel.transit', 'syw/carousel/jquery.transit.min.js', ['relative' => true, 'version' => 'auto'], $attributes, ['jquery']);
		//HTMLHelper::script('syw/carousel/jquery.transit.min.js', array('relative' => true, 'version' => 'auto'), $attributes);

		self::$jqctransitLoaded = true;
	}

	/**
	 * add lazyload class when on high resolution devices only
	 * @param string $selector
	 * @param boolean $lazyload
	 * @param string $lazyload_image
	 */
	static function triggerLazysizes($selector = 'img', $lazyload = false, $lazyload_image = '')
	{
		if (in_array($selector, self::$highresLoaded)) {
			return;
		}

		$javascript = array();

		$javascript[] = 'document.addEventListener("readystatechange", function(event) { ';
			$javascript[] = 'if (event.target.readyState == "complete") { ';

				$javascript[] = 'var elements = document.querySelectorAll("' . $selector . '[data-src]"); ';

				$javascript[] = 'if (window.devicePixelRatio > 1) { '; // undefined > 1 results in false (IE < 11 do not support the property)

					$javascript[] = 'for (var i = 0; i < elements.length; i++) { ';
						$javascript[] = 'el = elements[i]; ';
						$javascript[] = 'if (el.classList) { el.classList.add("lazyload"); } else { el.className += " lazyload" } ';
						if ($lazyload && $lazyload_image) {
							$javascript[] = 'el.setAttribute("src", "' . $lazyload_image . '"); ';
						}
					$javascript[] = '} ';

				$javascript[] = '}';

				if ($lazyload && $lazyload_image) {
					$javascript[] = ' else {';

						$javascript[] = 'for (var i = 0; i < elements.length; i++) { ';
							$javascript[] = 'el = elements[i]; ';
							$javascript[] = 'if (el.classList) { el.classList.add("lazyload"); } else { el.className += " lazyload" } ';
							$javascript[] = 'el.setAttribute("data-src", el.getAttribute("src")); ';
							$javascript[] = 'el.setAttribute("src", "' . $lazyload_image . '"); ';
						$javascript[] = '} ';

					$javascript[] = '}';
				}

			$javascript[] = '} ';
		$javascript[] = '}); ';

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->addInlineScript(implode($javascript));
		//Factory::getDocument()->addScriptDeclaration(implode($javascript));

		self::$highresLoaded[] = $selector;
	}

	/**
	 * Load the comparison version function if needed
	 */
	static function loadCompareVersions()
	{
		if (self::$compareLoaded) {
			return;
		}

		// returns false if version e > t (version is 1.3.2 for example)
		$compareScript = 'function SYWCompareVersions(e,t){var r=!1;if(e==t)return!0;"object"!=typeof e&&(e=e.toString().split(".")),"object"!=typeof t&&(t=t.toString().split("."));for(var o=0;o<Math.max(e.length,t.length);o++){if(void 0==e[o]&&(e[o]=0),void 0==t[o]&&(t[o]=0),Number(e[o])<Number(t[o])){r=!0;break}if(e[o]!=t[o])break}return r};';

		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$wam->addInlineScript($compareScript);
		//Factory::getDocument()->addScriptDeclaration($compareScript);

		self::$compareLoaded = true;
	}

	/**
	 * Compress inline JS
	 * @param string $inlineJS
	 * @return string
	 */
	static function compress($inlineJS = '', $remove_comments = false)
	{
		if ($remove_comments) {
			$inlineJS = preg_replace('!\/\*[\s\S]*?\*\/|\/\/.*!', '', $inlineJS);
		}

		return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $inlineJS);
	}

}
?>
