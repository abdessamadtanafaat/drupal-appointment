(function ($, Drupal) {
  Drupal.behaviors.agencySelection = {
    attach: function (context, settings) {
      // Ensure the behavior is attached only once.
      once('agency-selection', '.agency-card', context).forEach(function (card) {
        $(card).on('click', function () {
          var agencyId = $(this).data('agency-id');

          // Set the hidden input value.
          $('#edit-agency-id').val(agencyId);

          // Store the selected agency in Drupal's session.
          $.ajax({
            url: '/appointment/store-agency', // Define an endpoint.
            type: 'POST',
            data: { agency_id: agencyId },
            success: function (response) {
              alert("Success");

              $('#agency-selection-form').submit(); // Submit the form.
            },
            error: function () {
              console.error('Failed to store agency');
            }
          });

          alert(agencyId);
          // Debugging: Log the agencyId.
          console.log('Selected Agency ID:', agencyId);

          // Set the selected agency ID in the hidden input field.
          $('#edit-agency-id').val(agencyId);

          // Debugging: Log the value of the hidden input field.
          console.log('Hidden Input Value:', $('#edit-agency-id').val());

          // Submit the form.
          $('#agency-selection-form').submit();
        });
      });
    }
  };
})(jQuery, Drupal);
