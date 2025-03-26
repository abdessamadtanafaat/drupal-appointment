(function($, Drupal) {
  'use strict';

  // Update URL when AJAX completes
  $(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.extraData && settings.extraData._triggering_element_name === 'status_filter') {
      var statusFilter = $('select[name="status_filter"]').val();
      var url = new URL(window.location.href);
      url.searchParams.set('status', statusFilter);
      window.history.pushState({}, '', url.toString());
    }
  });

})(jQuery, Drupal);
