(function ($, Drupal) {
  Drupal.behaviors.initializeFullCalendar = {
    attach: function (context, settings) {
      // Ensure the DOM is fully loaded.
      // Use 'once' to ensure this behavior is only attached once.

      $(once('initializeFullCalendar', '#calendar', context)).each(function () {
        var calendarEl = document.getElementById('calendar');

        // Get the latest values from drupalSettings.
        var agency_id = settings.appointment.agency_id;
        var appointment_type_id = settings.appointment.appointment_type_id;
        var appointment_type_name = settings.appointment.appointment_type_name;
        var advisor_id = settings.appointment.advisor_id;

        // Log the latest values for debugging.
        console.log('Latest tempstore values:', {
          agency_id: agency_id,
          appointment_type_id: appointment_type_id,
          advisor_id: advisor_id,
          appointment_type_name: appointment_type_name,
        });

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
          events: function(fetchInfo, successCallback, failureCallback) {
            // Make AJAX request to get appointments
            $.ajax({
              url: '/appointment/get-appointments',
              type: 'GET',
              dataType: 'json',
              success: function(response) {
                // Transform the response to FullCalendar event objects
                var events = response.map(function(event) {
                  return {
                    id: event.id,
                    title: 'UNAVAILABLE',
                    start: event.start,
                    end: event.end,
                    extendedProps: event.extendedProps,
                    editable: false, // Ensure non-editable
                    startEditable: false, // Cannot drag to resize start time
                    durationEditable: false, // Cannot drag to resize duration
                    backgroundColor: '#d6d6d6', // Optional: different color for non-editable events
                    borderColor: '#d6d6d6', // different border color
                    textColor: '#d6d6d6', // different text color
                  };
                });
                successCallback(events);
              },
              error: function(xhr, status, error) {
                console.error('Failed to fetch appointments:', error);
                failureCallback(error);
              }
            });
          },
          // events:
          //   [
          //   // {
          //   //   id: '1',
          //   //   title: 'Available',
          //   //   start: new Date('2025-03-20T20:30:00Z'),
          //   //   end: new Date('2025-03-20T21:30:00Z'),
          //   // }
          // ],
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
                  agency_id: agency_id,
                  appointment_type_id: appointment_type_id,
                  appointment_type_name: appointment_type_name,
                  advisor_id: advisor_id,
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

            if (info.event.extendedProps.source) {
              alert('This event cannot be modified or deleted.');
              return;
            }

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
