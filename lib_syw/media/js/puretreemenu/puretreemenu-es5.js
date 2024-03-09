function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/*
 * A pure javascript class for creating menu trees
 *
 * @copyright    Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

var pureTreeMenu = /*#__PURE__*/function () {
  "use strict";

  function pureTreeMenu(options) {
    _classCallCheck(this, pureTreeMenu);

    this.config = {
        containerSelector: '.rawmenu',
        parentSelector: '.deeper',
        indent: true,
        iconPrefixClass: '',
        iconRetractedClass: '',
        iconExpandedClass: ''
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

	_createClass(pureTreeMenu, [{
		key: "init",
		value: function init() {
        	var container = this.config.containerSelector;
        	document.querySelectorAll(container).forEach(function (menu) {
	        	if (!this.config.indent) {
					menu.classList.add('no-indent');
				}
            	menu.querySelectorAll(this.config.parentSelector).forEach(function (deeper) {
                	var anchor = document.createElement('a');
                	var icon = '';
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
  	}, {
	    key: "switcher",
	    value: function switcher(el, retractedIcon, expandedIcon) {
			el.addEventListener('click', function (e) {
	        	e.preventDefault();
	        	var parent = el.parentNode;
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
	}]);

	return pureTreeMenu;
}();
