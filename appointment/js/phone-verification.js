// (function($, Drupal) {
//   'use strict';
//
//   Drupal.behaviors.appointmentPhoneVerification = {
//     attach: function(context, settings) {
//       // Handle change appointment button click
//       $('#change-appointment-button', context).once('changeAppointment').on('click', function() {
//         // Get the verification form via AJAX
//         $.ajax({
//           url: Drupal.url('appointment/load-phone-verification'),
//           type: 'GET',
//           dataType: 'json',
//           success: function(response) {
//             $('#booking-form-wrapper').html(response.form);
//           }
//         });
//       });
//
//       // Handle phone verification form submission
//       $('#phone-verification-form', context).once('phoneVerification').on('submit', function(e) {
//         e.preventDefault();
//         var phoneNumber = $('#phone-number').val();
//
//         // Verify the phone number
//         $.ajax({
//           url: Drupal.url('appointment/verify-phone'),
//           type: 'POST',
//           data: {phone_number: phoneNumber},
//           dataType: 'json',
//           success: function(response) {
//             if (response.success) {
//               window.location.href = response.redirect;
//             } else {
//               alert(response.message);
//             }
//           }
//         });
//       });
//     }
//   };
// })(jQuery, Drupal);
