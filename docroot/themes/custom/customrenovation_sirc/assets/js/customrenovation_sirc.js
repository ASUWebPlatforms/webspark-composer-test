/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/customrenovation_sirc.js":
/*!************************************!*\
  !*** ./src/js/customrenovation_sirc.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.customRenovation_sirc = {
    attach: function attach(context, settings) {
      // Academic units script
      $('.leader-pic:empty').parent('.leader-thumb').detach();
      $('.leader-thumb:nth-child(2)').next('.col-md-8').removeClass('col-md-8').addClass('col-md-12');
      $('.views-element-container .researchunits [data-toggle="collapse"]').addClass('collapsed');
      $('.views-element-container .researchunits [data-toggle="collapse"]').click(function () {
        if ($(this).hasClass('collapsed')) {
          $(this).parent().next('.leaders').hide();
        } else {
          $(this).parent().next('.leaders').show();
        }
      });
      $('.expcl').click(function () {
        $('.card.card-body.collapse.clearfix').removeAttr('style');
        $('[data-toggle="collapse"]').removeClass('collapsed');
        $('.card.card-body.collapse.clearfix').addClass('show');
        $('.lead-name').addClass('collapse');
        $(this).toggleClass('active');
        $('.collcl').toggleClass('active');
      });
      $('.collcl').click(function () {
        $('[data-toggle="collapse"]').addClass('collapsed');
        $('.card.card-body.clearfix.collapse.show').removeClass('show');
        $('.lead-name').removeClass('collapse');
        $(this).toggleClass('active');
        $('.expcl').toggleClass('active');
        $('.card.card-body.collapse.clearfix').removeAttr('style');
      });
      $(function () {
        // Fix displayed markup on image carousel
        $(".glide__slide figcaption .uds-caption-text p").each(function () {
          var text = jQuery(this).text();
          var replace = text.replace(/&lt;(.*?)&gt;/g, '');
          $(this).replaceWith(replace);
        });
        // Remove padding from main on last section with "last-bg" class.
        $('.last-bg').closest('main').removeClass('pb-5');
      });
    }
  };
})(jQuery, Drupal);

/***/ }),

/***/ "./src/sass/customrenovation_sirc.style.scss":
/*!**********************************************!*\
  !*** ./src/sass/customrenovation_sirc.style.scss ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*********************************************************************************!*\
  !*** multi ./src/js/customrenovation_sirc.js ./src/sass/customrenovation_sirc.style.scss ***!
  \*********************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /var/www/html/web/themes/customrenovation_sirc/src/js/customrenovation_sirc.js */"./src/js/customrenovation_sirc.js");
module.exports = __webpack_require__(/*! /var/www/html/web/themes/customrenovation_sirc/src/sass/customrenovation_sirc.style.scss */"./src/sass/customrenovation_sirc.style.scss");


/***/ })

/******/ });
//# sourceMappingURL=customrenovation_sirc.js.map
