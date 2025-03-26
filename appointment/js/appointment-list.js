(function($, Drupal, once) {
  'use strict';

  // Update URL when filters change
  Drupal.behaviors.appointmentFilters = {
    attach: function(context, settings) {
      // Use once() instead of jQuery's .once()
      $(once('appointmentFilters', '#appointment-view-appointments-form .appointment-filters', context)).each(function() {
        var $form = $(this).closest('form');

        // Update URL when AJAX completes
        $form.on('ajaxComplete', function(event, xhr, settings) {
          // Only process our form's AJAX requests
          if (settings.extraData && settings.extraData._triggering_element_name) {
            // var status = $form.find('select[name="status_filter"]').val();
            var date = $form.find('input[name="date_filter"]').val();
            var agency = $form.find('select[name="agency_filter"]').val();
            var advisor = $form.find('select[name="advisor_filter"]').val();

            // Create URL parameters object
            var params = {
              // status: status,
              date: date,
              agency: agency,
              advisor: advisor
            };

            // Remove empty or default params
            for (var key in params) {
              if (params[key] === '' || params[key] === 'all') {
                delete params[key];
              }
            }

            // Update URL without reload
            if (typeof URL !== 'undefined') {
              var url = new URL(window.location.href);
              for (var key in params) {
                url.searchParams.set(key, params[key]);
              }
              window.history.pushState({}, '', url.toString());
            }
          }
        });
      });
    }
  };

})(jQuery, Drupal, once);
