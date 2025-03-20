(function ($) {
  $(document).ready(function () {
    $('.agency-card').click(function () {
      // Get the selected agency ID from the clicked card.
      var agencyId = $(this).data('agency-id');

      // Log the agencyId to the console for debugging.
      console.log('Selected Agency ID:', agencyId);

      // Store the agency ID in the hidden input field.
      $('input[name="agency_id"]').val(agencyId);

      // Highlight the selected card (optional, for UI feedback).
      $('.agency-card').removeClass('selected'); // Remove selection from all cards.
      $(this).addClass('selected'); // Add selection to the clicked card.
    });

    // Optionally, highlight the card on hover for better interactivity.
    $('.agency-card').hover(function () {
      $(this).css('cursor', 'pointer');
    }, function () {
      $(this).css('cursor', 'default');
    });
  });
})(jQuery);
