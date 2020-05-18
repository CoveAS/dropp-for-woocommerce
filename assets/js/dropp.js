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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/dropp.js":
/*!*******************************!*\
  !*** ./resources/js/dropp.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports) {

jQuery(function ($) {
  var form = $('form.checkout');
  var loading_status = 0;
  var dropp_handler = {
    click: function click(e) {
      if (typeof chooseDroppLocation === 'undefined') {
        // @TODO: Error handling for when the choose dropp location function does not exist
        return;
      }

      e.preventDefault();
      var elem = $(this).closest('.dropp-location');
      if (!location.length) ;
      var instance_id = elem.data('instance_id');
      chooseDroppLocation().then(function (location) {
        if (!location.id) {
          // Something went wrong.
          // @TODO
          console.error(location);
          return;
        } // Show the name.


        elem.find('.dropp-location__name').text(location.name).show(); // A location was picked. Save it.

        $.post(_dropp.ajaxurl, {
          action: 'dropp_set_location',
          instance_id: instance_id,
          location_id: location.id,
          location_name: location.name,
          location_address: location.address,
          location_pricetype: location.pricetype
        }, function () {
          // Location was saved to session.
          form.trigger('update_checkout');
        });
      })["catch"](function (error) {
        // Something went wrong.
        // @TODO.
        console.error(error);
      });
    },
    show_selector: function show_selector() {
      $('.dropp-error').hide();
      $('.dropp-location').show();
      $('.dropp-location .button').on('click', dropp_handler.click);
      $('#shipping_method').unblock();
    },
    block_shipping_methods: function block_shipping_methods() {
      $('#shipping_method').block({
        message: null,
        overlayCSS: {
          background: '#fff',
          opacity: 0.6
        }
      });
    },
    init: function init() {
      if (!loading_status && $('.dropp-location').length) {
        // Only load the external script if Dropp.is is part of the available shipping options
        var script = document.createElement('script');
        script.src = _dropp.dropplocationsurl;
        script.onload = dropp_handler.success;
        script.dataset.storeId = _dropp.storeid;
        document.body.appendChild(script);
        dropp_handler.block_shipping_methods();
        loading_status = 1;
      } else if (2 == loading_status) {
        dropp_handler.show_selector();
      } else if (1 == loading_status) {
        // Shipping methods were updated, but the selector is still loading.
        dropp_handler.block_shipping_methods();
      }
    },
    success: function success(content, textStatus, jqXHR) {
      loading_status = 2;
      dropp_handler.show_selector();
    },
    error: function error(jqXHR, textStatus, errorThrown) {
      loading_status = 0;
      $('.dropp-error').show().text(_dropp.i18n.error_loading);
      $('.dropp-location').hide();
      $('#shipping_method').unblock();
    }
  };
  $(document).on('updated_checkout', dropp_handler.init);
  dropp_handler.init();
});

/***/ }),

/***/ 1:
/*!*************************************!*\
  !*** multi ./resources/js/dropp.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /mnt/c/Workspace/Projects/dropp.x/public/wp-content/plugins/dropp-for-woocommerce/resources/js/dropp.js */"./resources/js/dropp.js");


/***/ })

/******/ });