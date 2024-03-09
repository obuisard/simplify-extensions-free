/*
 * A pure javascript class for creating menu trees
 *
 * @copyright    Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

 class pureTreeMenu {
    constructor(options) {
        this.config = {
            containerSelector: '.rawmenu',
            parentSelector: '.deeper',
            indent: true,
            iconPrefixClass: '',
            iconRetractedClass: '',
            iconExpandedClass: ''
        };
        if (typeof options !== "undefined") {
            let userSettings = options;
            for (let attrname in userSettings) {
                if (userSettings[attrname] != undefined) {
                    this.config[attrname] = userSettings[attrname];
                }
            }
        }
        this.init();
    }
    init() {
        let container = this.config.containerSelector;
        document.querySelectorAll(container).forEach(function (menu) {
	        if (!this.config.indent) {
				menu.classList.add('no-indent');
			}
            menu.querySelectorAll(this.config.parentSelector).forEach(function (deeper) {
                let anchor = document.createElement('a');
                let icon = '';
                if (this.config.iconPrefixClass == '') {
					icon = ' no-icon';
				} else {
					icon = ' ' + this.config.iconPrefixClass;
					if (deeper.classList.contains('active') || deeper.classList.contains('expanded')) {
						if (this.config.iconExpandedClass) {
							icon += ' ' + this.config.iconExpandedClass;
						}
					} else {
						if (this.config.iconRetractedClass) {
							icon += ' ' + this.config.iconRetractedClass;
						}
					}
				}				
                anchor.setAttribute('class', 'expcol' + icon);
                anchor.setAttribute('href', '');
                anchor.setAttribute('onclick', 'return false');
                deeper.insertBefore(anchor, deeper.firstChild);
            }, this);
            menu.querySelectorAll(this.config.parentSelector + ' .expcol').forEach(function (expand) {
				this.switcher(expand, this.config.iconRetractedClass, this.config.iconExpandedClass);
            }, this);
            menu.querySelectorAll(this.config.parentSelector + ' a[href="#"]').forEach(function (expand) {
                this.switcher(expand, this.config.iconRetractedClass, this.config.iconExpandedClass);
            }, this);
        }, this);
    }
    switcher(el, retractedIcon, expandedIcon) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            let parent = el.parentNode;
            if (parent.classList.contains('active')) {
				return;
			}
            if (parent.classList.contains('expanded')) {
                parent.classList.remove('expanded');
                if (retractedIcon) { el.classList.add(retractedIcon); }
                if (expandedIcon) { el.classList.remove(expandedIcon); }
            } else {
                parent.classList.add('expanded');
                if (expandedIcon) { el.classList.add(expandedIcon); }
                if (retractedIcon) { el.classList.remove(retractedIcon); }
            }
		}, this);
	}
}

window.pureTreeMenu = pureTreeMenu; // scope expanded outside the module
