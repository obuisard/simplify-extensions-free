<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace SYW\Library\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use SYW\Library\Stylesheets as SYWStylesheets;

class SywiconpickerField extends FormField
{
	public $type = 'Sywiconpicker';

	protected $icons;
	protected $icongroups;
	protected $emptyicon;
	protected $buttonlabel;
	protected $buttonrole;
	protected $help;
	protected $icomoon;
	protected $editable;

	protected function getIconGroup($icongroup)
	{
		$icons = array();

		switch ($icongroup) {
			case 'communications':
				$icons[] = 'email';
				$icons[] = 'mail_outline';
				$icons[] = 'drafts';
				$icons[] = 'inbox2';
				$icons[] = 'dialpad';
				$icons[] = 'phone-android';
				$icons[] = 'phone-iphone';
				$icons[] = 'phone';
				$icons[] = 'call';
				$icons[] = 'phone_forwarded';
				$icons[] = 'phone_in_talk';
				$icons[] = 'call_end';
				$icons[] = 'skype';
				$icons[] = 'fax';
				$icons[] = 'record_voice_over';
				$icons[] = 'voicemail';
				$icons[] = 'messenger';
				$icons[] = 'textsms';
				$icons[] = 'chat2';
				$icons[] = 'question_answer';
				break;
			case 'equipment':
				$icons[] = 'computer';
				$icons[] = 'desktop-windows';
				$icons[] = 'desktop_mac';
				$icons[] = 'tablet-android';
				$icons[] = 'tv';
				$icons[] = 'keyboard2';
				$icons[] = 'keyboard-voice';
				$icons[] = 'mouse2';
				$icons[] = 'watch';
				$icons[] = 'camera';
				$icons[] = 'camera-alt';
				$icons[] = 'devices_other';
				$icons[] = 'videogame_asset';
				$icons[] = 'router';
				$icons[] = 'wifi';
				$icons[] = 'bluetooth';
				break;
			case 'transportation':
				$icons[] = 'directions-bike';
				$icons[] = 'directions-bus';
				$icons[] = 'directions-car';
				$icons[] = 'directions-ferry';
				$icons[] = 'directions-subway';
				$icons[] = 'directions-train';
				$icons[] = 'directions-walk';
				$icons[] = 'subway';
				$icons[] = 'train';
				$icons[] = 'tram';
				$icons[] = 'local-shipping';
				$icons[] = 'local-taxi';
				$icons[] = 'traff';
				$icons[] = 'flight';
				$icons[] = 'flight_land';
				$icons[] = 'flight_takeoff';
				$icons[] = 'motorcycle';
				$icons[] = 'airport_shuttle';
				$icons[] = 'local_gas_station';
				$icons[] = 'directions';
				$icons[] = 'directions2';
				break;
			case 'location':
				$icons[] = 'home';
				$icons[] = 'explore';
				$icons[] = 'my_location';
				$icons[] = 'location-on';
				$icons[] = 'location_searching';
				$icons[] = 'person_pin_circle';
				$icons[] = 'gps-fixed';
				$icons[] = 'near_me';
				$icons[] = 'hotel';
				$icons[] = 'local-attraction';
				$icons[] = 'local-bar';
				$icons[] = 'local-cafe';
				$icons[] = 'local-florist';
				$icons[] = 'local-hospital';
				$icons[] = 'local-library';
				$icons[] = 'books';
				$icons[] = 'local-mall';
				$icons[] = 'local-parking';
				$icons[] = 'local-pizza';
				$icons[] = 'map';
				$icons[] = 'navigation';
				$icons[] = 'restaurant-menu';
				$icons[] = 'store-mall-directory';
				$icons[] = 'location-city';
				$icons[] = 'publ';
				$icons[] = 'school';
				$icons[] = 'office';
				$icons[] = 'building';
				$icons[] = 'paw';
				$icons[] = 'spoon';
				$icons[] = 'futbol-o';
				$icons[] = 'weekend';
				$icons[] = 'airline_seat_individual_suite';
				$icons[] = 'airline_seat_recline_extra';
				$icons[] = 'airline_seat_recline_normal';
				$icons[] = 'wc';
				$icons[] = 'beach_access';
				$icons[] = 'business_center';
				$icons[] = 'casino';
				$icons[] = 'fitness_center';
				$icons[] = 'free_breakfast';
				$icons[] = 'golf_course';
				$icons[] = 'pool';
				$icons[] = 'theaters';
				$icons[] = 'nature';
				$icons[] = 'nature_people';
				break;
			case 'social':
				$icons[] = 'group';
				$icons[] = 'supervisor_account';
				$icons[] = 'person';
				$icons[] = 'person_outline';
				$icons[] = 'share2';
				$icons[] = 'omega';
				$icons[] = 'blogger';
				$icons[] = 'chat';
				$icons[] = 'comment';
				$icons[] = 'vcard';
				$icons[] = 'insert-emoticon';
				$icons[] = 'favorite';
				$icons[] = 'thumb-down';
				$icons[] = 'thumb-up';
				$icons[] = 'thumbs-up-down';
				$icons[] = 'star';
				$icons[] = 'star-half';
				$icons[] = 'star-outline';
				$icons[] = 'flickr';
				$icons[] = 'vimeo';
				$icons[] = 'twitter';
				$icons[] = 'facebook';
				$icons[] = 'google';
				$icons[] = 'googleplus';
				$icons[] = 'pinterest';
				$icons[] = 'tumblr';
				$icons[] = 'linkedin';
				$icons[] = 'dribbble';
				$icons[] = 'stumbleupon';
				$icons[] = 'lastfm';
				$icons[] = 'spotify';
				$icons[] = 'instagram';
				$icons[] = 'circles';
				$icons[] = 'youtube-play';
				$icons[] = 'recent_actors';
				$icons[] = 'contacts';
				$icons[] = 'import_contacts';
				break;
			case 'agenda':
				$icons[] = 'alarm';
				$icons[] = 'calendar';
				$icons[] = 'event';
				$icons[] = 'today';
				$icons[] = 'event-note';
				$icons[] = 'view-agenda';
				$icons[] = 'watch_later';
				$icons[] = 'timelapse';
				$icons[] = 'timer';
				$icons[] = 'access_time';
				$icons[] = 'event_seat';
				$icons[] = 'trophy';
				$icons[] = 'gift';
				$icons[] = 'cake';
				$icons[] = 'birthday-cake';
				$icons[] = 'cake2';
				$icons[] = 'timeline';
				break;
			case 'finances':
				$icons[] = 'account-balance';
				$icons[] = 'account-box';
				$icons[] = 'credit-card';
				$icons[] = 'receipt';
				$icons[] = 'shopping-cart';
				$icons[] = 'wallet-giftcard';
				$icons[] = 'wallet-membership';
				$icons[] = 'attach-money';
				$icons[] = 'paypal';
				$icons[] = 'google-wallet';
				$icons[] = 'cc-visa';
				$icons[] = 'cc-mastercard';
				$icons[] = 'cc-discover';
				$icons[] = 'cc-amex';
				$icons[] = 'euro_symbol';
				break;
			case 'files':
				$icons[] = 'sd-storage';
				$icons[] = 'storage';
				$icons[] = 'attach-file';
				$icons[] = 'attachment';
				$icons[] = 'insert-drive-file';
				$icons[] = 'description';
				$icons[] = 'file-download';
				$icons[] = 'file-upload';
				$icons[] = 'folder';
				$icons[] = 'dropbox';
				$icons[] = 'evernote';
				$icons[] = 'picasa';
				$icons[] = 'archive';
				$icons[] = 'unarchive';
				$icons[] = 'cloud';
				break;
			case 'systems':
				$icons[] = 'stack-overflow';
				$icons[] = 'apple';
				$icons[] = 'windows';
				$icons[] = 'android';
				$icons[] = 'linux';
				$icons[] = 'wordpress';
				$icons[] = 'drupal';
				$icons[] = 'joomla';
				$icons[] = 'verified-user';
				$icons[] = 'security';
				$icons[] = 'bug-report';
				$icons[] = 'settings';
				break;
			case 'accessibility':
				$icons[] = 'hearing';
				$icons[] = 'accessibility';
				$icons[] = 'accessible';
				$icons[] = 'touch_app';
				break;
			case 'media':
				$icons[] = 'movie';
				$icons[] = 'movie_filter';
				$icons[] = 'subtitles';
				$icons[] = 'music_note';
				$icons[] = 'queue_music';
				$icons[] = 'music_video';
				$icons[] = 'playlist_play';
				$icons[] = 'playlist_add_check';
				$icons[] = 'slow_motion_video';
				$icons[] = 'album';
				$icons[] = 'speaker';
				$icons[] = 'camera_roll';
				$icons[] = 'photo_filter';
				$icons[] = 'radio';
				$icons[] = 'videocam';
				break;

			case 'other':
				$icons[] = 'assignment';
				$icons[] = 'book';
				$icons[] = 'bookmark';
				$icons[] = 'help';
				$icons[] = 'info2';
				$icons[] = 'label';
				$icons[] = 'language';
				$icons[] = 'language2';
				$icons[] = 'picture-in-picture';
				$icons[] = 'query-builder';
				$icons[] = 'stars';
				$icons[] = 'extension';
				$icons[] = 'dashboard';
				$icons[] = 'format-quote';
				$icons[] = 'view-carousel';
				$icons[] = 'visibility';
				$icons[] = 'equalizer';
				$icons[] = 'games';
				$icons[] = 'add-circle';
				$icons[] = 'content-paste';
				$icons[] = 'create';
				$icons[] = 'flag';
				$icons[] = 'forward';
				$icons[] = 'remove-circle';
				$icons[] = 'save';
				$icons[] = 'dvr';
				$icons[] = 'now-wallpaper';
				$icons[] = 'now-widgets';
				$icons[] = 'insert-photo';
				$icons[] = 'color-lens';
				$icons[] = 'filter-frames';
				$icons[] = 'healing';
				$icons[] = 'style';
				$icons[] = 'wb-sunny';
				$icons[] = 'apps';
				$icons[] = 'check-box';
				$icons[] = 'radio-button-on';
				$icons[] = 'pushpin';
				$icons[] = 'quotes-right';
				$icons[] = 'power-cord';
				$icons[] = 'tag';
				$icons[] = 'leaf';
				$icons[] = 'newspaper';
				$icons[] = 'lifebuoy';
				$icons[] = 'work';
				$icons[] = 'briefcase';
				$icons[] = 'hourglass';
				$icons[] = 'gauge';
				$icons[] = 'network';
				$icons[] = 'key';
				$icons[] = 'vpn_key';
				$icons[] = 'suitcase';
				$icons[] = 'light-bulb';
				$icons[] = 'box';
				$icons[] = 'ticket';
				$icons[] = 'rss';
				$icons[] = 'pie';
				$icons[] = 'lock';
				$icons[] = 'lock-open';
				$icons[] = 'info';
				$icons[] = 'docs';
				$icons[] = 'tag2';
				$icons[] = 'tags';
				$icons[] = 'chain';
				$icons[] = 'sitemap';
				$icons[] = 'new-releases';
				$icons[] = 'droplets';
				$icons[] = 'fiber_new';
				$icons[] = 'art_track';
				$icons[] = 'web_asset';
				$icons[] = 'highlight';
				$icons[] = 'developer_board';
				$icons[] = 'filter_vintage';
				$icons[] = 'power';
				$icons[] = 'build';
				$icons[] = 'fingerprint';
				$icons[] = 'pets';
				$icons[] = 'rowing';
				$icons[] = 'pan_tool';
				$icons[] = 'ac_unit';
				$icons[] = 'child_care';
				$icons[] = 'child_friendly';
				$icons[] = 'hot_tub';
				$icons[] = 'kitchen';
				$icons[] = 'room_service';
				$icons[] = 'smoke_free';
				$icons[] = 'smoking_rooms';
				$icons[] = 'spa';
				$icons[] = 'goat';
				$icons[] = 'update';
				$icons[] = 'launch';
				$icons[] = 'toys';
				break;
 			case 'icomoon': // icons compatible from J3.1 throughout J3.8
 				$icons[] = 'icomoon-home';
 				$icons[] = 'icomoon-user';
 				$icons[] = 'icomoon-lock';
 				$icons[] = 'icomoon-comment';
 				$icons[] = 'icomoon-comments-2';
 				$icons[] = 'icomoon-edit';
 				$icons[] = 'icomoon-pencil-2';
 				$icons[] = 'icomoon-folder-open';
 				$icons[] = 'icomoon-folder-close';
 				$icons[] = 'icomoon-picture';
 				$icons[] = 'icomoon-pictures';
 				$icons[] = 'icomoon-list';
 				$icons[] = 'icomoon-power-cord';
 				$icons[] = 'icomoon-cube';
 				$icons[] = 'icomoon-puzzle';
 				$icons[] = 'icomoon-flag';
 				$icons[] = 'icomoon-tools';
 				$icons[] = 'icomoon-cogs';
 				$icons[] = 'icomoon-options';
 				$icons[] = 'icomoon-equalizer';
 				$icons[] = 'icomoon-wrench';
 				$icons[] = 'icomoon-brush';
 				$icons[] = 'icomoon-eye';
 				$icons[] = 'icomoon-star-empty';
 				$icons[] = 'icomoon-star-2';
 				$icons[] = 'icomoon-star';
 				$icons[] = 'icomoon-calendar';
 				$icons[] = 'icomoon-calendar-2';
 				$icons[] = 'icomoon-help';
 				$icons[] = 'icomoon-support';
 				$icons[] = 'icomoon-warning';
 				$icons[] = 'icomoon-ok';
 				$icons[] = 'icomoon-cancel';
 				$icons[] = 'icomoon-minus';
 				$icons[] = 'icomoon-trash';
 				$icons[] = 'icomoon-mail';
 				$icons[] = 'icomoon-mail-2';
 				$icons[] = 'icomoon-unarchive';
 				$icons[] = 'icomoon-archive';
 				$icons[] = 'icomoon-box-add';
 				$icons[] = 'icomoon-box-remove';
 				$icons[] = 'icomoon-search';
 				$icons[] = 'icomoon-filter';
 				$icons[] = 'icomoon-camera';
 				$icons[] = 'icomoon-play';
 				$icons[] = 'icomoon-music';
 				$icons[] = 'icomoon-grid-view';
 				$icons[] = 'icomoon-grid-view-2';
 				$icons[] = 'icomoon-menu';
 				$icons[] = 'icomoon-thumbs-up';
 				$icons[] = 'icomoon-thumbs-down';
 				$icons[] = 'icomoon-remove';
 				$icons[] = 'icomoon-plus-2';
 				$icons[] = 'icomoon-minus-2';
 				$icons[] = 'icomoon-key';
 				$icons[] = 'icomoon-quote';
 				$icons[] = 'icomoon-quote-2';
 				$icons[] = 'icomoon-database';
 				$icons[] = 'icomoon-location';
 				$icons[] = 'icomoon-zoom-in';
 				$icons[] = 'icomoon-zoom-out';
 				$icons[] = 'icomoon-expand';
 				$icons[] = 'icomoon-contract';
 				$icons[] = 'icomoon-expand-2';
 				$icons[] = 'icomoon-contract-2';
 				$icons[] = 'icomoon-health';
 				$icons[] = 'icomoon-wand';
 				$icons[] = 'icomoon-refresh';
 				$icons[] = 'icomoon-vcard';
 				$icons[] = 'icomoon-clock';
 				$icons[] = 'icomoon-compass';
 				$icons[] = 'icomoon-address';
 				$icons[] = 'icomoon-feed';
 				$icons[] = 'icomoon-flag-2';
 				$icons[] = 'icomoon-pin';
 				$icons[] = 'icomoon-lamp';
 				$icons[] = 'icomoon-chart';
 				$icons[] = 'icomoon-bars';
 				$icons[] = 'icomoon-pie';
 				$icons[] = 'icomoon-dashboard';
 				$icons[] = 'icomoon-lightning';
 				$icons[] = 'icomoon-move';
 				$icons[] = 'icomoon-loop';
 				$icons[] = 'icomoon-shuffle';
 				$icons[] = 'icomoon-printer';
 				$icons[] = 'icomoon-color-palette';
 				$icons[] = 'icomoon-camera-2';
 				$icons[] = 'icomoon-file';
 				$icons[] = 'icomoon-cart';
 				$icons[] = 'icomoon-basket';
 				$icons[] = 'icomoon-broadcast';
 				$icons[] = 'icomoon-screen';
 				$icons[] = 'icomoon-tablet';
 				$icons[] = 'icomoon-mobile';
 				$icons[] = 'icomoon-users';
 				$icons[] = 'icomoon-briefcase';
 				$icons[] = 'icomoon-download';
 				$icons[] = 'icomoon-upload';
 				$icons[] = 'icomoon-bookmark';
 				$icons[] = 'icomoon-out-2';

 				//if (version_compare(JVERSION, '3.2', 'ge')) {
 					$icons[] = 'icomoon-joomla';
 					$icons[] = 'icomoon-link';
 					$icons[] = 'icomoon-phone';
 					$icons[] = 'icomoon-phone-2';
 					$icons[] = 'icomoon-tag';
 					$icons[] = 'icomoon-tag-2';
 					$icons[] = 'icomoon-tags';
 					$icons[] = 'icomoon-tags-2';
 					$icons[] = 'icomoon-equalizer';
 					$icons[] = 'icomoon-unlock';
 					$icons[] = 'icomoon-scissors';
 					$icons[] = 'icomoon-book';
 					$icons[] = 'icomoon-calendar-3';
 					$icons[] = 'icomoon-shield';
 					$icons[] = 'icomoon-heart';
 					$icons[] = 'icomoon-smiley-happy';
 					$icons[] = 'icomoon-smiley-happy-2';
 					$icons[] = 'icomoon-smiley-sad';
 					$icons[] = 'icomoon-smiley-sad-2';
 					$icons[] = 'icomoon-smiley-neutral';
 					$icons[] = 'icomoon-smiley-neutral-2';
 					$icons[] = 'icomoon-credit';
 					$icons[] = 'icomoon-credit-2';
 				//}
		}

		$iconlist = '';
		foreach ($icons as $index => $icon_item) {

			if ($index == count($icons) - 1) {
				$iconlist .= '<li style="width: auto; display: inline-block; border: none; margin: 2px 2px 10px 2px;" data-SYWicon="'.$icon_item.'">';
			} else {
				$iconlist .= '<li style="width: auto; display: inline-block; border: none; margin: 2px;" data-SYWicon="'.$icon_item.'">';
			}
			$iconlist .= '<a href="#" class="dropdown-item badge bg-light text-dark hvr-radial-out hasTooltip" style="padding: 8px; font-size: 1.4em" title="'.$icon_item.'" onclick="return false;"><i class="SYWicon-'.$icon_item.'"></i></a>';
			$iconlist .= '</li>';
		}

		return $iconlist;
	}

	protected function getIcons()
	{
	    $iconlist = '';

	    $icongrouplist = array('communications', 'equipment', 'transportation', 'location', 'social', 'agenda', 'finances', 'files', 'systems', 'accessibility', 'media', 'other');
	    if ($this->icomoon) {
	        $icongrouplist[] = 'icomoon';
	    }

		foreach ($icongrouplist as $icongrouplist_item) {
		    $iconlist .= '<li><h6 class="dropdown-header">'.Text::_('LIB_SYW_ICONPICKER_ICONGROUP_'.strtoupper($icongrouplist_item)).'</h6></li>';
		    $iconlist .= self::getIconGroup($icongrouplist_item);
		}

		return $iconlist;
	}

	protected function getInput()
	{
		$wam = Factory::getApplication()->getDocument()->getWebAssetManager();

		$lang = Factory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
		HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

		$wam->registerAndUseStyle('syw.font', 'syw/fonts.min.css', ['relative' => true, 'version' => 'auto']);

		if ($this->icomoon) {
		    $wam->registerAndUseStyle('syw.font.icomoon', 'syw/fonts-icomoon.min.css', ['relative' => true, 'version' => 'auto']);
		}

		$transition_method = SYWStylesheets::getTransitionMethod('hvr-radial-out');
		SYWStylesheets::$transition_method();

		$wam->addInlineScript('
			document.addEventListener("readystatechange", function(event) {
				if (event.target.readyState == "complete") {

                    let select_' . $this->id . ' = document.getElementById("' . $this->id . '_select");
                    if (select_' . $this->id . ' != null) {
                        let options_' . $this->id . ' = select_' . $this->id . '.querySelectorAll("li[data-SYWicon]");
    		            let input_' . $this->id . ' = document.getElementById("' . $this->id . '");
                        let icon_' . $this->id . ' = document.getElementById("' . $this->id . '_icon");

                        if (input_' . $this->id . '.value != "") {
                            let entry_value = select_' . $this->id . '.querySelector("li[data-SYWicon=\'' . $this->value . '\']");
                            if (entry_value != null) {
                                entry_value.querySelector("a").classList.add("bg-primary", "text-light");
                                entry_value.querySelector("a").classList.remove("bg-light", "text-dark");
                            }
                        }

                        for (let i = 0; i < options_' . $this->id . '.length; i++) {
                            options_' . $this->id . '[i].addEventListener("click", function(event) {

                                if (input_' . $this->id . '.value != "") {
                                    let entry_value = select_' . $this->id . '.querySelector("li[data-SYWicon=\'" + input_' . $this->id . '.value + "\']");
                                    if (entry_value != null) {
                                        entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                        entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }

                                this.querySelector("a").classList.add("bg-primary", "text-light");
                                this.querySelector("a").classList.remove("bg-light", "text-dark");

                                let selected_icon = this.getAttribute("data-SYWicon");
                                input_' . $this->id . '.value = selected_icon;
                                icon_' . $this->id . '.setAttribute("class", "SYWicon-" + selected_icon);
                            });
                        }

                        document.getElementById("' . $this->id . '_default").addEventListener("click", function(event) {
                            if (input_' . $this->id . '.value != "") {
                                let entry_value = select_' . $this->id . '.querySelector("li[data-SYWicon=\'" + input_' . $this->id . '.value + "\']");
                                if (entry_value != null) {
                                    entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                    entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                }
                            }
                            ' . (empty($this->default) ? '
                            input_' . $this->id . '.value = "";
                            icon_' . $this->id . '.setAttribute("class", "SYWicon-' . $this->emptyicon . '");
                            ' : '
                            input_' . $this->id . '.value = "' . $this->default . '";
                            icon_' . $this->id . '.setAttribute("class", "SYWicon-' . $this->default . '");
                            ') . '
                        });

                        ' . ($this->editable ? '
                        input_' . $this->id . '.addEventListener("change", function(event) {
                            if (this.value != "") {

                                icon_' . $this->id . '.setAttribute("class", "SYWicon-" + this.value);

                                for (let i = 0; i < options_' . $this->id . '.length; i++) {
                                    if (this.value == options_' . $this->id . '[i].getAttribute("data-SYWicon")) {
                                        options_' . $this->id . '[i].querySelector("a").classList.add("bg-primary", "text-light");
                                        options_' . $this->id . '[i].querySelector("a").classList.remove("bg-light", "text-dark");
                                    } else {
                                        options_' . $this->id . '[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                        options_' . $this->id . '[i].querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }
                            } else {
                                ' . (empty($this->default) ? '
                                icon_' . $this->id . '.setAttribute("class", "SYWicon-' . $this->emptyicon . '");
                                ' : '
                                input_' . $this->id . '.value = "' . $this->default . '";
                                icon_' . $this->id . '.setAttribute("class", "SYWicon-' . $this->default . '");
                                ') . '

                                for (let i = 0; i < options_' . $this->id . '.length; i++) {
                                    options_' . $this->id . '[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                    options_' . $this->id . '[i].querySelector("a").classList.add("bg-light", "text-dark");
                                }
                            }
                        });
                        ' : '
                        ') . '
                    }

                    document.addEventListener("subform-row-add", function(e) {
                        let sywip = e.detail.row.querySelector(".iconpicker");
                        if (sywip != null) {
                            let sywip_options = sywip.querySelectorAll("li[data-SYWicon]");
                            let sywip_input = sywip.querySelector("input[data-name=input-icon]");
                            let sywip_icon = sywip.querySelector("i[data-name=icon]");

                            for (let i = 0; i < sywip_options.length; i++) {
                                sywip_options[i].addEventListener("click", function(event) {

                                    if (sywip_input.value != "") {
                                        let entry_value = sywip.querySelector("li[data-SYWicon=\'" + sywip_input.value + "\']");
                                        if (entry_value != null) {
                                            entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                            entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }

                                    this.querySelector("a").classList.add("bg-primary", "text-light");
                                    this.querySelector("a").classList.remove("bg-light", "text-dark");

                                    let selected_icon = this.getAttribute("data-SYWicon");
                                    sywip_input.value = selected_icon;
                                    sywip_icon.setAttribute("class", "SYWicon-" + selected_icon);
                                });
                            }

                            sywip.querySelector("button[data-name=default-icon]").addEventListener("click", function(event) {
                                if (sywip_input.value != "") {
                                    let entry_value = sywip.querySelector("li[data-SYWicon=\'" + sywip_input.value + "\']");
                                    if (entry_value != null) {
                                        entry_value.querySelector("a").classList.remove("bg-primary", "text-light");
                                        entry_value.querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }
                                ' . (empty($this->default) ? '
                                sywip_input.value = "";
                                sywip_icon.setAttribute("class", "SYWicon-' . $this->emptyicon . '");
                                ' : '
                                sywip_input.value = "' . $this->default . '";
                                sywip_icon.setAttribute("class", "SYWicon-' . $this->default . '");
                                ') . '
                            });

                            ' . ($this->editable ? '
                            sywip_input.addEventListener("change", function(event) {
                                if (this.value != "") {

                                    sywip_icon.setAttribute("class", "SYWicon-" + this.value);

                                    for (let i = 0; i < sywip_options.length; i++) {
                                        if (this.value == sywip_options[i].getAttribute("data-SYWicon")) {
                                            sywip_options[i].querySelector("a").classList.add("bg-primary", "text-light");
                                            sywip_options[i].querySelector("a").classList.remove("bg-light", "text-dark");
                                        } else {
                                            sywip_options[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                            sywip_options[i].querySelector("a").classList.add("bg-light", "text-dark");
                                        }
                                    }
                                } else {
                                    ' . (empty($this->default) ? '
                                    sywip_icon.setAttribute("class", "SYWicon-' . $this->emptyicon . '");
                                    ' : '
                                    sywip_input.value = "' . $this->default . '";
                                    sywip_icon.setAttribute("class", "SYWicon-' . $this->default . '");
                                    ') . '

                                    for (let i = 0; i < sywip_options.length; i++) {
                                        sywip_options[i].querySelector("a").classList.remove("bg-primary", "text-light");
                                        sywip_options[i].querySelector("a").classList.add("bg-light", "text-dark");
                                    }
                                }
                            });
                            ' : '
                            ') . '
                        }
                    });
                }
			});
		');

		$html = '';

		$html .= '<div class="iconpicker input-group">';

 		if (!empty($this->value)) {
 			$html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="SYWicon-'.$this->value.'" data-name="icon" aria-hidden="true"></i></span>';
 		} else {
			if (empty($this->default)) {
				$html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="SYWicon-'.$this->emptyicon.'" data-name="icon" aria-hidden="true"></i></span>';
			} else {
				$html .= '<span class="input-group-text"><i id="'.$this->id.'_icon" class="SYWicon-'.$this->default.'" data-name="icon" aria-hidden="true"></i></span>';
			}
 		}

 		if ($this->editable) {
			$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' data-name="input-icon" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" class="form-control" />';
		} else {
			//$html .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
			$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' data-name="input-icon" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" readonly="readonly" class="form-control" />';
		}

		$html .= '<div class="btn-group" style="margin: 0">';
			$html .= '<button type="button" id="'.$this->id.'_caret"'.($this->disabled ? ' disabled="disabled"' : '').' style="border-radius:0" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="' . Text::_('LIB_SYW_ICONPICKER_SELECTICON') . '"></button>';
			$html .= '<ul id="'.$this->id.'_select" class="dropdown-menu dropdown-menu-end" aria-labelledby="'.$this->id.'_caret" style="min-width: 250px; max-height: 200px; overflow: auto;">';

		if (isset($this->icons)) {
			$icons = explode(",", $this->icons);
			foreach ($icons as $icon_item) {
				$html .= '<li style="width: auto; display: inline-block; border: none; margin: 2px;" data-SYWicon="'.$icon_item.'"><a href="#" class="dropdown-item badge bg-light text-dark hasTooltip" style="padding: 8px; font-size: 1.4em" title="'.$icon_item.'" aria-label="'.$icon_item.'" onclick="return false;"><i class="SYWicon-'.$icon_item.'"></i></a></li>';
			}
		} else if (isset($this->icongroups)) {
			$icongroups = explode(",", $this->icongroups);
			foreach ($icongroups as $icongroup_item) {
				$html .= '<li><h6 class="dropdown-header">'.Text::_('LIB_SYW_ICONPICKER_ICONGROUP_'.strtoupper($icongroup_item)).'</h6></li>';
				$html .= self::getIconGroup($icongroup_item);
			}
		} else {
			$html .= self::getIcons();
		}

		$html .= '</ul>';
		$html .= '</div>';

		$default_class_extra = '';
		//if (empty($this->value) || (!empty($this->default) && $this->default == $this->value)) {
			if ($this->buttonrole == 'default') {
				$default_class_extra = ' btn-secondary';
			}
		//}

		if ($this->buttonrole == 'clear') {
		    $default_class_extra = ' btn-danger';
		}

		$html .= '<button type="button" data-name="default-icon" id="'.$this->id.'_default"'.($this->disabled ? ' disabled="disabled"' : '').' class="btn'.$default_class_extra.' hasTooltip" title="' . htmlspecialchars($this->buttonlabel, ENT_COMPAT, 'UTF-8') . '" aria-label="' . htmlspecialchars($this->buttonlabel, ENT_COMPAT, 'UTF-8') . '">';
		if ($this->buttonrole == 'clear') {
			$html .= '<i class="icon-remove"></i>';
		} else {
			$html .= $this->buttonlabel;
		}
		$html .= '</button>';

		$html .= '</div>';

		if ($this->help) {
			$html .= '<span class="help-block" style="font-size: .8rem">'.Text::_($this->help).'</span>';
		}

		return $html;
	}

	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->icons = isset($this->element['icons']) ? (string)$this->element['icons'] : null;
			$this->icongroups = isset($this->element['icongroups']) ? (string)$this->element['icongroups'] : null;
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->icomoon = isset($this->element['icomoon']) ? filter_var($this->element['icomoon'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->editable = isset($this->element['editable']) ? filter_var($this->element['editable'], FILTER_VALIDATE_BOOLEAN) : false;
			$this->buttonrole = isset($this->element['buttonrole']) ? Text::_((string)$this->element['buttonrole']) : 'default';
			$this->buttonlabel = isset($this->element['buttonlabel']) ? Text::_((string)$this->element['buttonlabel']) : ($this->buttonrole == 'clear' ? Text::_('JCLEAR') : Text::_('JDEFAULT'));
			$this->emptyicon = isset($this->element['emptyicon']) ? (string)$this->element['emptyicon'] : ($this->buttonrole == 'default' ? 'question' : '');

			if (!empty($this->value)) {
				$this->value = str_replace('SYWicon-', '', $this->value);

				if (strpos($this->value, 'icon-') !== false) {
					$this->value = str_replace('icon-', 'icomoon-', $this->value);
				}
			}
		}

		return $return;
	}

}
?>
