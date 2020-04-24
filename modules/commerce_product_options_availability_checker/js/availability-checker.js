(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.checkAvailability = {
    attach: function (context, settings) {

      var $skuSelects = [];

      if ($('#sku-generation').val() === 'bySegment') {
        $('select', context).once().each(function() {
          if ($(this).attr('data-sku-generation') === 'Yes') {

            $skuSelects.push($(this));

            $(this).change(function() {
              checkAvailability();
            });
          }
        });
      }

      function checkAvailability() {

        var sku = $('#base-sku').val();

        for (var i = 0; i < $skuSelects.length; i++) {
          if ($skuSelects[i].val().length > 0) {
            sku = sku + '-' + $skuSelects[i].val();
          }
          else {
            return;
          }
        }

        // Disable registration button while checking availability.
        if ($('#edit-register').is('button')) {
          $('#edit-register').html('Checking...');
        }
        else if($('#edit-register').is('input')) {
          $('#edit-register').prop('value', 'Checking...');
        }
        $('#edit-register').prop('disabled', true);

        $.get(Drupal.url('rest/session/token')).done(function (csrfToken) {
          $.ajax({
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': csrfToken
            },
            method: 'GET',
            url: drupalSettings.path.baseUrl + 'commerce-product-options/availability/' + sku + '?_format=json',
            success: function(data, textStatus, xhr) {
              if (data === 'UNAVAILABLE') {
                if ($('#edit-register').is('button')) {
                  $('#edit-register').html(data);
                }
                else if($('#edit-register').is('input')) {
                  $('#edit-register').prop('value', data);
                }
                $('#edit-register').prop('disabled', true);
                $('#strong-limited').remove();
                $('#wait-list-add').css('display', 'block');
              }
              else if (data === 'plentiful') {
                if ($('#edit-register').is('button')) {
                  $('#edit-register').html('Add to cart');
                }
                else if($('#edit-register').is('input')) {
                  $('#edit-register').prop('value', 'Add to cart');
                }
                $('#edit-register').prop('disabled', false);
                $('#strong-limited').remove();
              }
              else {
                if ($('#edit-register').is('button')) {
                  $('#edit-register').html('Add to cart');
                }
                else if($('#edit-register').is('input')) {
                  $('#edit-register').prop('value', 'Add to cart');
                }
                $('#edit-register').prop('disabled', false);
                $('#strong-limited').remove();
                $('#edit-actions').after('<p id="strong-limited" style="text-align: center; margin-bottom: 0.75rem;">' + data + '</p>');
              }
            },
            error: function(xhr, textStatus, errorThrown) {
              //console.log(xhr);
              //console.log(textStatus);
              //console.log(errorThrown);
              //console.log(xhr.responseText);
            }
          });
        });
      }
    }
  };

  Drupal.behaviors.waitListMessage = {
    attach: function (context) {
      var $context = $(context);
      var $waitListBlock = $context.find('#wait-list-block');
      var $waitListParent = $waitListBlock.parent();
      var $productName = $context.find('#product-name');
      var $waitListLink = $context.find('#wait-list-add');
      var $waitListClose = $context.find('#wait-list-close');

      $waitListLink.once('wait-list-add-processed').on('click', function (ev) {
        ev.preventDefault();

        var registrationData = {};
        registrationData.product = $productName.val();
        registrationData.details = {};

        $('.commerce-order-item-add-to-cart-form input, .commerce-order-item-add-to-cart-form select').each(

          function() {

            var element = $(this);
            if (element.is('select') && element.val()) {
              registrationData.details[element.attr('name')] = element.find('option:selected').text();
            }
            else if ((element.is('input') && element.attr('type') === 'text') && element.val()) {
              registrationData.details[element.attr('name')] = element.val();
            } 
          }
        );

        $.get(Drupal.url('rest/session/token')).done(function (csrfToken) {
          $.ajax({
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': csrfToken
            },
            method: 'POST',
            url: drupalSettings.path.baseUrl + 'commerce-product-options/waitlist' + '?_format=json',
            data: JSON.stringify(registrationData),
            success: function(data, textStatus, xhr) {
              $waitListBlock.css('display', 'inline-block');
              $waitListBlock.width($waitListParent.width() - 40);

              var waitListTop = Math.floor($waitListParent.offset().top) + $waitListParent.height() - $waitListBlock.height() - 36;
              var waitListLeft = Math.floor($waitListParent.offset().left) + 11;
              $waitListBlock.offset({ top: waitListTop, left: waitListLeft });
            },
            error: function(xhr, textStatus, errorThrown) {
              alert('We are sorry. Due to technical difficulties, we were unable to process your request to be added to the waitlist. Please use the site contact form to request addition to the wait list. Contact the site administrator if the problem persists.');
            }
          });
        });
      });

      $waitListClose.once('wait-list-close-processed').on('click', function (ev) {
        ev.preventDefault();
        $waitListBlock.css('display', 'none');
      });
    }
  };
})(jQuery, Drupal);
