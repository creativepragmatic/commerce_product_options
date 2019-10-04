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
              if (data === 'SOLD OUT') {
                if ($('#edit-register').is('button')) {
                  $('#edit-register').html(data);
                }
                else if($('#edit-register').is('input')) {
                  $('#edit-register').prop('value', data);
                }
                $('#edit-register').prop('disabled', true);
                $('#strong-limited').remove();
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
})(jQuery, Drupal);
