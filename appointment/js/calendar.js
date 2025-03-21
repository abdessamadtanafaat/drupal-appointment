// (function ($, Drupal) {
//   Drupal.behaviors.initializeFullCalendar = {
//     attach: function (context, settings) {
//       // Ensure the DOM is fully loaded.
//       $(document).ready(function () {
//         var calendarEl = document.getElementById('calendar');
//
//         // Get the advisor ID from Drupal settings.
//         var advisor_id = settings.appointment.advisor_id;
//
//         // Log the advisor ID for debugging.
//         console.log('Fetching availability for advisor ID:', advisor_id);
//
//         // Fetch advisor availability via AJAX.
//         $.ajax({
//           url: '/appointment/availability',
//           type: 'GET',
//           data: { advisor_id: advisor_id },
//           success: function (response) {
//             // Log the AJAX response for debugging.
//             console.log('Received availability data:', response);
//
//             // Extract available time ranges for selectConstraint.
//             var availableSlots = response.events
//               .filter(event => event.color === '#00ff00') // Filter available slots.
//               .map(event => ({
//                 start: event.start,
//                 end: event.end,
//               }));
//
//             // Initialize FullCalendar.
//             var calendar = new FullCalendar.Calendar(calendarEl, {
//               initialView: 'timeGridWeek', // Default view
//               headerToolbar: {
//                 left: 'prev,next today',
//                 center: 'title',
//                 right: 'dayGridMonth,timeGridWeek,timeGridDay'
//               },
//               selectable: true, // Enable date selection
//               editable: false,  // Disable event dragging and resizing
//               events: response.events, // Pass fetched events to FullCalendar.
//               selectConstraint: availableSlots, // Restrict selection to available slots.
//               selectAllow: function (info) {
//                 // Check if the selected time is entirely within any available slot.
//                 var selectedStart = info.start;
//                 var selectedEnd = info.end;
//                 var isAvailable = false;
//
//                 availableSlots.forEach(function (slot) {
//                   var slotStart = new Date(slot.start);
//                   var slotEnd = new Date(slot.end);
//
//                   // Check if the selected time is entirely within the working hours.
//                   if (
//                     selectedStart >= slotStart &&
//                     selectedEnd <= slotEnd
//                   ) {
//                     isAvailable = true;
//                   }
//                 });
//
//                 return isAvailable;
//               },
//               select: function (info) {
//                 // Handle date selection.
//                 var selectedDate = info.startStr;
//                 var selectedTime = info.endStr;
//                 console.log('Selected Date and Time:', selectedDate, selectedTime);
//
//                 // Set the selected date and time in the hidden field.
//                 $('#edit-selected-datetime').val(selectedDate + ' ' + selectedTime);
//               },
//               dateClick: function (info) {
//                 // Handle date click
//                 console.log('Date clicked: ' + info.dateStr);
//               },
//               eventClick: function (info) {
//                 // Handle event click
//                 console.log('Event clicked: ' + info.event.title);
//               }
//             });
//
//             // Render the calendar.
//             calendar.render();
//           },
//           error: function (xhr, status, error) {
//             // Log any AJAX errors for debugging.
//             console.error('AJAX request failed:', status, error);
//           }
//         });
//       });
//     }
//   };
// })(jQuery, Drupal);
//

(function ($, Drupal) {
  Drupal.behaviors.initializeFullCalendar = {
    attach: function (context, settings) {
      // Ensure the DOM is fully loaded.
      $(document).ready(function () {
        var calendarEl = document.getElementById('calendar');

        // Initialize the calendar using the browser global method.
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'timeGridWeek', // Default view
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          selectable: true, // Enable date selection
          editable: true,   // Enable event dragging and resizing
          validRange: {
            start: new Date(), // Disable dates/times before the current time.
          },
          events: [
            {
              id: '1',
              title: 'Available',
              start: new Date('2025-03-20T20:30:00Z'),
              end: new Date('2025-03-20T21:30:00Z'),
            }
          ],
          select: function (info) {
            // Handle date selection (creating a new event).
            var title = prompt('Enter a title for the event:'); // Prompt the user for an event title.
            if (title) {
              // Create a new event object.
              var newEvent = {
                title: title,
                start: info.startStr, // Use the selected start time.
                end: info.endStr,     // Use the selected end time.
              };

              // Add the new event to the calendar.
              calendar.addEvent(newEvent);

              // Log the new event for debugging.
              console.log('New event created:', newEvent);

              // Debug: Log the data being sent in the AJAX request.
              console.log('Data being sent:', {
                //advisor_id: advisor_id,
                start: info.startStr,
                end: info.endStr,
                title: title,
              });

              // Save the selected time slot to the tempstore.
              $.ajax({
                url: '/appointment/save-selection',
                type: 'POST',
                data: JSON.stringify({ // Ensure the data is sent as JSON.
                  //advisor_id: advisor_id,
                  start: info.startStr,
                  end: info.endStr,
                  title: title,
                }),
                contentType: 'application/json', // Set the content type to JSON.
                success: function (response) {
                  console.log('Selection saved to tempstore:', response);
                },
                error: function (xhr, status, error) {
                  console.error('Failed to save selection:', error);
                }
              });
            }
          },

          dateClick: function (info) {
            // Handle date click
            console.log('Date clicked: ' + info.dateStr);
          },
          eventClick: function (info) {
            // Handle event click
            console.log('Event clicked: ' + info.event.title);

            // Optionally, allow users to edit or delete the event.
            var action = prompt('Do you want to (1) Edit or (2) Delete this event? Enter 1 or 2:');
            if (action === '1') {
              var newTitle = prompt('Enter a new title for the event:');
              if (newTitle) {
                info.event.setProp('title', newTitle); // Update the event title.
                console.log('Event updated:', info.event);
              }
            } else if (action === '2') {
              if (confirm('Are you sure you want to delete this event?')) {
                info.event.remove(); // Remove the event.
                console.log('Event deleted:', info.event);
              }
            }
          }
        });

        // Render the calendar.
        calendar.render();
      });
    }
  };
})(jQuery, Drupal);
