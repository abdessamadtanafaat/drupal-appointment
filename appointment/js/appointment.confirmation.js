// js/appointment.confirmation.js
(function($, Drupal) {
  Drupal.behaviors.appointmentConfirmation = {
    attach: function(context, settings) {
      $('#change-appointment-button', context).once('changeAppointment').click(function() {
        // Load the phone verification form via AJAX
        $.ajax({
          url: '/appointment/load-verification-form',
          method: 'GET',
          success: function(response) {
            $('#booking-form-wrapper').html(response.form);

            // Attach verification handler
            $('#verify-button').click(function() {
              const phone = $('#phone-number').val();
              $('#verification-message').html('<div class="messages messages--status">Verifying...</div>');

              $.ajax({
                url: '/appointment/verify-phone',
                method: 'POST',
                data: { phone: phone },
                dataType: 'json',
                success: function(response) {
                  if (response.success) {
                    window.location.href = '/book-appointment?phone=' + phone;
                  } else {
                    $('#verification-message').html('<div class="messages messages--error">' + response.message + '</div>');
                  }
                }
              });
            });
          }
        });
      });
    }
  };
})(jQuery, Drupal);
