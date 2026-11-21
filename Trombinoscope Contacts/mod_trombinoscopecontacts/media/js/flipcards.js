/*
 * Flip between landscape and portrait layouts
 * v1.0.0
 *
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license	GNU General Public License version 3 or later; see LICENSE.txt
 */

if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}

function _instanceof(left, right) { if (right != null && typeof Symbol !== "undefined" && right[Symbol.hasInstance]) { return !!right[Symbol.hasInstance](left); } else { return left instanceof right; } }

function _classCallCheck(instance, Constructor) { if (!_instanceof(instance, Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var flipCards = /*#__PURE__*/function () {
  "use strict";

  function flipCards(options) {
    _classCallCheck(this, flipCards);

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

  _createClass(flipCards, [{
    key: "init",
    value: function init() {
      this.person = document.querySelectorAll(this.config.selector);

      if (this.person != null) {
        this.flip();
      }

      var timeout = false;
      window.addEventListener("resize", function () {
        if (this.person != null) {
          clearTimeout(timeout);
          timeout = setTimeout(this.flip(), 100);
        }
      }.bind(this));
    }
  }, {
    key: "flip",
    value: function flip() {
      this.person.forEach(function (el) {
        if (el.classList) {
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
  }]);

  return flipCards;
}();
