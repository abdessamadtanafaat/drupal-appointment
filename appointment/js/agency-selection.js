(function (Drupal) {
  Drupal.behaviors.agencyCardSelection = {
    attach: function (context, settings) {
      // Use a data attribute to track initialization.
      const agencyCards = context.querySelectorAll('.agency-card:not([data-initialized])');
      agencyCards.forEach(card => {
        card.setAttribute('data-initialized', 'true');

        card.addEventListener('click', function () {
          // Get the selected agency ID from the clicked card.
          const agencyId = this.getAttribute('data-agency-id');

          // Log the agencyId to the console for debugging.
          console.log('Selected Agency ID:', agencyId);

          // Store the agency ID in the hidden input field.
          document.querySelector('input[name="agency_id"]').value = agencyId;

          // Highlight the selected card.
          document.querySelectorAll('.agency-card').forEach(c => c.classList.remove('selected'));
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
