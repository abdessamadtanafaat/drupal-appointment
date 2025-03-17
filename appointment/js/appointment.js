// (function ($, Drupal) {
//   Drupal.behaviors.agencySelection = {
//     attach: function (context, settings) {
//       // When the user clicks an agency card, store the selected agency in tempstore.
//       $('.agency-card', context).once('agencySelection').click(function () {
//         var agencyId = $(this).data('agency-id');
//
//         // Send AJAX to update the next step (appointment types).
//         $.ajax({
//           url: '/prendre-un-rendez-vous',
//           method: 'POST',
//           data: {
//             agency_id: agencyId
//           },
//           success: function (response) {
//             // Dynamically replace the content of the appointment types container.
//             $('#appointment-types-container').html(response);
//           }
//         });
//       });
//     }
//   };
// })(jQuery, Drupal);
