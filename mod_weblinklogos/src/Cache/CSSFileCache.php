<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

namespace SYW\Module\WeblinkLogos\Site\Cache;

defined('_JEXEC') or die;

use SYW\Library\HeaderFilesCache;

class CSSFileCache extends HeaderFilesCache
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

		// card

		$overall_bgcolor = trim($params->get('overallbgcolor', '')) != '' ? trim($params->get('overallbgcolor')) : 'transparent';
		$variables[] = 'overall_bgcolor';

		$font_size = $params->get('fontsize', 90);
		$variables[] = 'font_size';

		$card_shadow = $params->get('card_shadow', false);
		$variables[] = 'card_shadow';

		$shadow_width = 8;
		$variables[] = 'shadow_width';

		$card_radius = $params->get('card_r', 0);
		$variables[] = 'card_radius';

		$card_border_width = $params->get('card_border_w', 0);
		$variables[] = 'card_border_width';

		$card_border_color = trim($params->get('card_border_c', ''));
		$variables[] = 'card_border_color';

		$overall_width = trim($params->get('overall_width', ''));
		$variables[] = 'overall_width';

		$force_width = $params->get('force_width', 1);
		$variables[] = 'force_width';

		$margin_top = $params->get('margin_top', 5);
		if ($card_shadow && $margin_top < $shadow_width) {
			$margin_top = $shadow_width;
		}
		$variables[] = 'margin_top';

		$margin_right = $params->get('margin_right', 5);
		if ($card_shadow && $margin_right < $shadow_width) {
			$margin_right = $shadow_width;
		}
		$variables[] = 'margin_right';

		$margin_bottom = $params->get('margin_bottom', 5);
		if ($card_shadow && $margin_bottom < $shadow_width) {
			$margin_bottom = $shadow_width;
		}
		$variables[] = 'margin_bottom';

		$margin_left = $params->get('margin_left', 5);
		if ($card_shadow && $margin_left < $shadow_width) {
			$margin_left = $shadow_width;
		}
		$variables[] = 'margin_left';

		// logo

		$width = $params->get('width', 120);
		$variables[] = 'width';

		$height = $params->get('height', 40);
		$variables[] = 'height';

		$logo_bgcolor = trim($params->get('logobgcolor', '')) != '' ? trim($params->get('logobgcolor')) : 'transparent';
		$variables[] = 'logo_bgcolor';

		$opacity = $params->get('opacity', 1);
		if ($opacity > 1) {
			$opacity = 1;
		}
		if ($opacity < 0) {
			$opacity = 0;
		}
		$variables[] = 'opacity';

		$restrict_width_to_image = $params->get('restrict_width', 0);
		$variables[] = 'restrict_width_to_image';

		// text

		$content_align = $params->get('content_align', 'center');
		$variables[] = 'content_align';

		$content_valign = $params->get('content_valign', 'top');
		$variables[] = 'content_valign';

		// animation

		$animated = $params->get('carousel_config', 'none') != 'none' ? true : false;
		$variables[] = 'animated';

		$horizontal = $params->get('carousel_config', 'none') == 'h' ? true : false;
		$variables[] = 'horizontal';

		$bootstrap = $params->get('arrowstyle', '') === 'pagination' ? true : false;
		$variables[] = 'bootstrap';

		$arrow_size = $params->get('arrowsize', 1);
		$variables[] = 'arrow_size';

		$arrow_offset = $params->get('arrowoffset', 0);
		$variables[] = 'arrow_offset';

		$show_arrows = $params->get('arrows', 'none') !== 'none' ? true : false;
		$variables[] = 'show_arrows';

		$show_pages = $params->get('includepages', 0);
		$variables[] = 'show_pages';

		// set all necessary parameters
		$this->params = compact($variables);
	}

	protected function getBuffer()
	{
		// get all necessary parameters
		extract($this->params);

		// 		if (function_exists('ob_gzhandler')) { // TODO not tested
		// 			ob_start('ob_gzhandler');
		// 		} else {
		ob_start();
		//		}

		// set the header
		$this->sendHttpHeaders('css');

		include JPATH_ROOT . '/media/mod_weblinklogos/styles/style.css.php';

		return $this->compress(ob_get_clean());
	}

}