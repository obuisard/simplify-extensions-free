/*
 * Flip between landscape and portrait layouts
 * v1.0.0
 *
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license	GNU General Public License version 3 or later; see LICENSE.txt
 */

class flipCards {
	constructor(options) {
		this.config = {
			selector: '.person',
			min_flip_width: 200
		};
		if (typeof options !== "undefined") {
			var userSettings = options;
			for (var attrname in userSettings) {
				if (userSettings[attrname] != undefined) {
					this.config[attrname] = userSettings[attrname];
				}
			}
		}
		this.init();
	}
	
	init() {
		this.person = document.querySelectorAll(this.config.selector);
		if (this.person != null) {
			this.flip();
		}
		var timeout = false;
		window.addEventListener("resize", function() {
			if (this.person != null) {
				clearTimeout(timeout);
				timeout = setTimeout(this.flip(), 100);
			}
		}.bind(this));
	}
	
	flip() { 
		this.person.forEach(function (el) {
//		for (var i = 0; i < this.person.length; i++) {
//			var el = this.person[i]; console.log(el.offsetWidth);
			if (el.classList) { /* if the browser does not support classList, do nothing, avoid crash */
				if (el.offsetWidth > this.config.min_flip_width) {
	
		        	if (el.classList.contains("pl")) {
	        			el.classList.add("picture_left");
	        			el.classList.remove("pl");
	        			el.classList.remove("picture_top");
	        		} else if (el.classList.contains("pr")) {
	        			el.classList.add("picture_right");
	        			el.classList.remove("pr");
	        			el.classList.remove("picture_top");
	        		}

					if (el.classList.contains("gpl")) {
						el.classList.add("ghost_picture_left");
						el.classList.remove("gpl");
						el.classList.remove("ghost_picture_top");
					} else if (el.classList.contains("gpr")) {
						el.classList.add("ghost_picture_right");
						el.classList.remove("gpr");
						el.classList.remove("ghost_picture_top");
					}
				} else {
					if (el.classList.contains("picture_left")) {
						el.classList.add("pl");
						el.classList.remove("picture_left");
						el.classList.add("picture_top");
					} else if (el.classList.contains("picture_right")) {
						el.classList.add("pr");
						el.classList.remove("picture_right");
						el.classList.add("picture_top");
					}

					if (el.classList.contains("ghost_picture_left")) {
						el.classList.add("gpl");
						el.classList.remove("ghost_picture_left");
						el.classList.add("ghost_picture_top");
					} else if (el.classList.contains("ghost_picture_right")) {
						el.classList.add("gpr");
						el.classList.remove("ghost_picture_right");
						el.classList.add("ghost_picture_top");
					}
				}
	    	}
		}, this);
	} 
}
