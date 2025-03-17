(function ($) {
  $(document).ready(function() {
    $('.select-agency-button').click(function() {
      console.log("Agency button clicked!");
      var agencyId = $(this).data('agency-id');
      console.log("Selected agency ID: " + agencyId);
      $('#agency-selected').val(agencyId); // Set the hidden field with the selected agency ID

      $.ajax({
        url: '/path-to-store-agency', // Ensure this is correct
        method: 'POST',
        data: {
          agency_id: agencyId,
        },
        success: function(response) {
          console.log("Agency stored successfully");
          $('#appointment-booking-form').submit(); // Trigger form submission
        },
        error: function() {
          console.log("Error storing agency");
        }
      });
    });
  });
})(jQuery);
