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
          events: [
            // Example events
            { id: '1', title: 'Meeting', start: new Date() }
          ],
          dateClick: function (info) {
            // Handle date click
            console.log('Date clicked: ' + info.dateStr);
          },
          eventClick: function (info) {
            // Handle event click
            console.log('Event clicked: ' + info.event.title);
          }
        });

        // Render the calendar.
        calendar.render();
      });
    }
  };
})(jQuery, Drupal);
