(function ($) {
  $(document).ready(function () {
    $('.advisor-card').click(function () {
      // Get the selected advisor ID from the clicked card.
      var advisorId = $(this).data('advisor-id');

      // Log the advisorId to the console for debugging.
      console.log('Selected advisor ID:', advisorId);

      // Store the advisor ID in the hidden input field.
      $('input[name="advisor_id"]').val(advisorId);

      // Highlight the selected card (optional, for UI feedback).
      $('.advisor-card').removeClass('selected'); // Remove selection from all cards.
      $(this).addClass('selected'); // Add selection to the clicked card.
    });

    // Optionally, highlight the card on hover for better interactivity.
    $('.advisor-card').hover(function () {
      $(this).css('cursor', 'pointer');
    }, function () {
      $(this).css('cursor', 'default');
    });
  });
})(jQuery);
