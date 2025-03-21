(function (Drupal) {
  Drupal.behaviors.advisorCardSelection = {
    attach: function (context, settings) {
      // Use a data attribute to track initialization.
      const advisorCards = context.querySelectorAll('.advisor-card:not([data-initialized])');
      advisorCards.forEach(card => {
        card.setAttribute('data-initialized', 'true');

        card.addEventListener('click', function () {
          // Get the selected advisor ID from the clicked card.
          const advisorId = this.getAttribute('data-advisor-id');

          // Log the advisorId to the console for debugging.
          console.log('Selected Advisor ID:', advisorId);

          // Store the advisor ID in the hidden input field.
          document.querySelector('input[name="advisor_id"]').value = advisorId;

          // Highlight the selected card.
          document.querySelectorAll('.advisor-card').forEach(c => c.classList.remove('selected'));
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
