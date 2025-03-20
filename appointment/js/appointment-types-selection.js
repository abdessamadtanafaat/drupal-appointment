(function ($) {
  $(document).ready(function () {
    $('.appointment_types-card').click(function () {
      // Get the selected appointment type ID from the clicked card.
      var appointmentTypeId = $(this).data('appointment-types-id');

      // Log the appointmentTypeId to the console for debugging.
      console.log('Selected Appointment Type ID:', appointmentTypeId);

      // Store the appointment type ID in the hidden input field.
      $('input[name="appointment_type_id"]').val(appointmentTypeId);

      // Highlight the selected card (optional, for UI feedback).
      $('.appointment_types-card').removeClass('selected'); // Remove selection from all cards.
      $(this).addClass('selected'); // Add selection to the clicked card.
    });

    // Optionally, highlight the card on hover for better interactivity.
    $('.appointment_types-card').hover(function () {
      $(this).css('cursor', 'pointer');
    }, function () {
      $(this).css('cursor', 'default');
    });
  });
})(jQuery);
