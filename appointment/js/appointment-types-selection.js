(function (Drupal) {
  Drupal.behaviors.appointmentTypeCardSelection = {
    attach: function (context, settings) {
      // Use a data attribute to track initialization.
      const appointmentTypeCards = context.querySelectorAll('.appointment_types-card:not([data-initialized])');
      appointmentTypeCards.forEach(card => {
        card.setAttribute('data-initialized', 'true');

        card.addEventListener('click', function () {
          // Get the selected appointment type ID from the clicked card.
          const appointmentTypeId = this.getAttribute('data-appointment-types-id');

          // Get the selected appointment type ID and name from data attributes.
          const appointmentTypeName = (this).getAttribute('data-appointment-types-name');

          // Log the appointmentTypeId to the console for debugging.
          console.log('Selected Appointment Type Name:', appointmentTypeName);

          // Log the appointmentTypeId to the console for debugging.
          console.log('Selected Appointment Type ID:', appointmentTypeId);

          // Store the appointment type ID in the hidden input field.
          document.querySelector('input[name="appointment_type_id"]').value = appointmentTypeId;

          // Store the appointment type ID in the hidden input field.
          document.querySelector('input[name="appointment_type_name"]').value = appointmentTypeName;

          // Highlight the selected card.
          document.querySelectorAll('.appointment_types-card').forEach(c => c.classList.remove('selected'));
          this.classList.add('selected');
        });

        // Optionally, highlight the card on hover for better interactivity.
        card.addEventListener('mouseenter', function () {
          this.style.cursor = 'pointer';
        });
        card.addEventListener('mouseleave', function () {
          this.style.cursor = 'default';
        });
      });
    }
  };
})(Drupal);
