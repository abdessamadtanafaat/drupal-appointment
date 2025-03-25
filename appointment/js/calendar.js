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
          slotMinTime: '08:00:00',
          slotMaxTime: '12:00:00',
          editable: true,   // Enable event dragging and resizing
          validRange: {
            start: new Date() },
          businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5],
            startTime: '08:00',
            endTime: '12:00',
          },

          events: function(fetchInfo, successCallback, failureCallback) {
            // First AJAX call to get appointments
            $.ajax({
              url: '/appointment/get-appointments',
              type: 'GET',
              dataType: 'json',
              success: function(appointmentsResponse) {
                // Transform appointments to FullCalendar event objects
                var appointmentEvents = appointmentsResponse.map(function(event) {
                  return {
                    id: event.id,
                    title: 'UNAVAILABLE',
                    start: event.start,
                    end: event.end,
                    extendedProps: event.extendedProps,
                    editable: false,
                    startEditable: false,
                    durationEditable: false,
                    backgroundColor: '#d6d6d6',
                    borderColor: '#d6d6d6',
                    textColor: '#d6d6d6'
                  };
                });

                // Second AJAX call to get working hours for the agency
                $.ajax({
                  url: '/appointment/get-working-hours-agency',
                  type: 'GET',
                  data: {
                    agency_id: agency_id
                  },
                  dataType: 'json',
                  success: function(workingHoursResponse) {
                    // Process working hours only if we got a successful response with data
                    if (workingHoursResponse.status === 'success' && workingHoursResponse.data.working_hours.length > 0) {
                      var nonWorkingHoursEvents = [];

                      // Process each day's working hours
                      workingHoursResponse.data.working_hours.forEach(function(day) {
                        // Convert hours to proper time format (800 -> 08:00:00)
                        var startHour = String(day.starthours).padStart(4, '0');
                        var endHour = String(day.endhours).padStart(4, '0');

                        // Create event for times before working hours
                        nonWorkingHoursEvents.push({
                          title: 'NON-WORKING HOURS',
                          daysOfWeek: [day.day],
                          startTime: '00:00:00',
                          endTime: startHour.substring(0, 2) + ':' + startHour.substring(2) + ':00',
                          display: 'background',
                          backgroundColor: 'rgba(255, 0, 0, 0.2)',
                          extendedProps: {
                            source: 'working_hours',
                            type: 'before_hours'
                          },
                          editable: false
                        });

                        // Create event for times after working hours
                        nonWorkingHoursEvents.push({
                          title: 'NON-WORKING HOURS',
                          daysOfWeek: [day.day],
                          startTime: endHour.substring(0, 2) + ':' + endHour.substring(2) + ':00',
                          endTime: '24:00:00',
                          display: 'background',
                          backgroundColor: 'rgba(255, 0, 0, 0.2)',
                          extendedProps: {
                            source: 'working_hours',
                            type: 'after_hours'
                          },
                          editable: false
                        });
                      });

                      // Combine with appointment events
                      var allEvents = appointmentEvents.concat(nonWorkingHoursEvents);
                      successCallback(allEvents);

                      // Set business hours in calendar
                      calendar.setOption('businessHours', {
                        daysOfWeek: [0, 1, 2, 3, 4, 5, 6], // All days
                        startTime: workingHoursResponse.data.working_hours[0].starthours + '',
                        endTime: workingHoursResponse.data.working_hours[0].endhours + ''
                      });
                    } else {
                      // No working hours available, just show appointments
                      successCallback(appointmentEvents);
                    }
                  },
                  error: function(xhr, status, error) {
                    console.error('Failed to fetch working hours:', error);
                    // Fall back to just showing appointments if working hours fetch fails
                    successCallback(appointmentEvents);
                  }
                });
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
