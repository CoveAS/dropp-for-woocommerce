/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*******************************!*\
  !*** ./resources/js/dropp.js ***!
  \*******************************/
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
        if (!location || !location.id) {
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
/******/ })()
;