<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\WeblinkLogos\Site\Cache;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use SYW\Library\HeaderFilesCache;

class JSAnimationFileCache extends HeaderFilesCache
{
	public function __construct($extension, $params = null)
	{
		parent::__construct($extension, $params);

		$this->extension = $extension;

		$variables = array();

		$suffix = $params->get('suffix');
		$variables[] = 'suffix';

		$bootstrap_version = $params->get('bootstrap_version', 2);
		$variables[] = 'bootstrap_version';

		$logos_layout = $params->get('logos_layout', 'grid');
		$variables[] = 'logos_layout';

		$card_shadow = $params->get('card_shadow', false);
		$shadow_width = 8;

		$card_width = $params->get('width', 120);
		if (trim($params->get('overall_width', '')) != '') {
			$card_width = intval($params->get('overall_width'));
		} else {
			$card_border_width = $params->get('card_border_w', 0);
			$overall_bgcolor = trim($params->get('overallbgcolor', '')) != '' ? trim($params->get('overallbgcolor')) : 'transparent';
			$logo_bgcolor = trim($params->get('logobgcolor', '')) != '' ? trim($params->get('logobgcolor')) : 'transparent';
			
			$padding = $params->get('content_spacing', 10);
			
			if ($card_border_width > 0) {
				$card_width += $card_border_width * 2;
			}
			
			if ($overall_bgcolor != 'transparent' || $card_shadow || $card_border_width > 0) {
				$card_width += $padding * 2;
			}
			
			if ($logo_bgcolor != 'transparent') {
				$card_width += $padding * 2;
			}
		}
		
		$margin_left = $params->get('margin_left', 5);
		if ($card_shadow && $margin_left < $shadow_width) {
			$margin_left = $shadow_width;
		}
		
		$margin_right = $params->get('margin_right', 5);
		if ($card_shadow && $margin_right < $shadow_width) {
			$margin_right = $shadow_width;
		}
		
		$card_width += $margin_left + $margin_right;
		
		$variables[] = 'card_width';

		$force_width = $params->get('force_width', 1);
		$variables[] = 'force_width';

		$space_between_cards = $params->get('margin_left', 5) + $params->get('margin_right', 5);
		$variables[] = 'space_between_cards';

		$horizontal = false;
		if ($params->get('carousel_config', 'none') == 'h') {
			$horizontal = true;
		}
		$variables[] = 'horizontal';

		$visible_items = $params->get('visible_items', 1);
		if (trim($visible_items) == '' || ($logos_layout == 'list' && $horizontal)) { // B/C
			$visible_items = 1;
		}
		$variables[] = 'visible_items';

// 		$direction = 'left';
// 		if (!$horizontal) {
// 			$direction = 'up';
// 		}
// 		$variables[] = 'direction';

		$move_at_once = $params->get('moveatonce', 'all');
		if ($move_at_once == 'all') {
			$move_at_once = $visible_items;
		} else {
			$move_at_once = 1;
		}
		$variables[] = 'move_at_once';

		$show_arrows = false;
		if ($params->get('arrows', 'none') != 'none') {
			$show_arrows = true;
		}
		$variables[] = 'show_arrows';

// 		$arrow_prevnext_bottom = false;
// 		if ($params->get('arrows', 'none') == 'under') {
// 			$arrow_prevnext_bottom = true;
// 		}
// 		$variables[] = 'arrow_prevnext_bottom';

		$show_pages = $params->get('includepages', 0);
		$variables[] = 'show_pages';

		$auto = $params->get('auto', 1);
		$variables[] = 'auto';

		$speed = $params->get('speed', 1000);
		$variables[] = 'speed';

		$interval = $params->get('interval', 3000);
		$variables[] = 'interval';

		$restart_on_refresh = $params->get('restart_on_refresh', 0);
		$variables[] = 'restart_on_refresh';

// 		$bootstrap = $params->get('arrowstyle', '') === 'pagination' ? true : false;
// 		$variables[] = 'bootstrap';

		// set all necessary parameters
		$this->params = compact($variables);
	}

	public function getBuffer()
	{
		// get all necessary parameters
		extract($this->params);

		// 		if (function_exists('ob_gzhandler')) { // not tested
		// 			ob_start('ob_gzhandler');
		// 		} else {
		ob_start();
		// 		}

		// set the header
		$this->sendHttpHeaders('js');

		if (Factory::getDocument()->getDirection() == 'rtl') {
			$carousel_var = 'wl_' . $suffix . '_carousel_rtl';
		} else {
			$carousel_var = 'wl_' . $suffix . '_carousel';
		}

		echo 'document.addEventListener("readystatechange", function(event) { ';
		echo 'if (event.target.readyState === "complete") { ';

			echo 'var ' . $carousel_var . ' = tns({ ';

				if (Factory::getDocument()->getDirection() == 'rtl') {
					echo 'textDirection: "rtl", ';
				}

				echo 'container: "#weblinklogo_' . $suffix . ' ul.weblink_items", ';

				if (!$horizontal) {
					echo 'axis: "vertical", ';
					if (intval($visible_items) == 1) {
						echo 'autoHeight: true, ';
					}
					echo 'items: ' . $visible_items . ', ';
					if (intval($move_at_once) > 1) {
						echo 'slideBy: ' . $move_at_once. ', ';
					}
				} else {
					if (!$force_width && intval($visible_items) > 1) {
						echo 'responsive: { ';
						for ($x = 0; $x < intval($visible_items); $x++) {
							echo (($x == 0) ? 0 : $x * $card_width + $card_width) . ': { ';
							echo 'items: ' . ($x + 1);
							if (intval($move_at_once) > 1) {
								echo ', slideBy: ' . ($x + 1);
							}
							echo ' }';
							if ($x < intval($visible_items) - 1) {
								echo ', ';
							}
						}
						echo ' }, ';
					} else {
						echo 'items: ' . $visible_items . ', ';
					}

					if ($force_width && $logos_layout != 'list') {
						echo 'fixedWidth: ' . $card_width . ', ';
					}
				}

				echo 'swipeAngle: false, ';

				echo 'gutter: ' . $space_between_cards . ', ';

				echo 'controls: false, ';

				if (!$show_pages) {
					echo 'nav: false, ';
				} else {
					echo 'navPosition: "bottom", ';
				}

				if ($auto) {
					echo 'autoplay: true, ';
					echo 'autoplayTimeout: ' . $interval . ', ';
					if (!$restart_on_refresh) {
						echo 'autoplayResetOnVisibility: false, ';
					}
					echo 'autoplayHoverPause: true, ';
					echo 'autoplayButtonOutput: false, ';
				}

				echo 'mouseDrag: true, ';
				echo 'arrowKeys: true, ';
				echo 'speed: ' . $speed . ', ';

				echo 'onInit: function (data) { ';
					echo 'var wl = document.getElementById("weblinklogo_' . $suffix . '"); ';
					echo 'if (wl.classList) { wl.classList.add("show"); } else { wl.className += " show" } ';

					if ($show_arrows) {
						echo 'if (data.items < ' . $visible_items . ' || data.slideCount > ' . $visible_items . ') {';
							echo 'var elems = document.querySelectorAll("#weblinklogo_' . $suffix . ' .items_pagination"); ';
							echo 'var nav_length = elems.length; ';
							echo 'for (var i = 0; i < nav_length; i++) { ';
								echo 'elems[i].style.opacity = 1; ';
							echo '} ';
						echo '} ';

						echo 'document.querySelector("#next_' . $suffix . '").addEventListener("click", function (e) { ';
							echo 'e.preventDefault(); ';
							echo $carousel_var . '.goTo("next"); ';
						echo '}); ';

						echo 'document.querySelector("#prev_' . $suffix . '").addEventListener("click", function (e) { ';
							echo 'e.preventDefault(); ';
							echo $carousel_var . '.goTo("prev"); ';
						echo '}); ';
					}
				echo '} ';
			echo '}); '; // end of tns

		if ($show_arrows) {
			echo 'var wl_resizeId_' . $suffix . '; ';
			echo 'window.addEventListener("resize", function() { ';
				echo 'clearTimeout(wl_resizeId_' . $suffix . '); ';
				echo 'wl_resizeId_' . $suffix . ' = setTimeout(wl_doneResizing_' . $suffix . ', 100); ';
			echo '}); ';

			echo 'function wl_doneResizing_' . $suffix . '() {';
				echo 'var info = ' . $carousel_var . '.getInfo(); ';
				echo 'var elems = document.querySelectorAll("#weblinklogo_' . $suffix . ' .items_pagination"); ';
				echo 'var nav_length = elems.length; ';
				echo 'for (var i = 0; i < nav_length; i++) { ';
					echo 'if (info.items < ' . $visible_items . ' || info.slideCount > ' . $visible_items . ') {';
						echo 'elems[i].style.opacity = 1; ';
					echo '} else { ';
						echo 'elems[i].style.opacity = 0; ';
					echo '} ';
				echo '} ';
			echo '} ';
		}

		if ($auto) {
			echo 'document.addEventListener("modalopen", function() { ';
				echo $carousel_var . '.pause(); ';
			echo '}, false); ';

			echo 'document.addEventListener("modalclose", function() { ';
				echo $carousel_var . '.play(); ';
			echo '}, false); ';
		}

		echo '} ';
		echo '}); ';

		return $this->compress(ob_get_clean(), true, 'js');
	}

}
