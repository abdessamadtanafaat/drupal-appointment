(function (Drupal) {
  Drupal.behaviors.personalInformationForm = {
    attach: function (context, settings) {

      // Log the personal information from drupalSettings.
      if (settings.appointment?.personal_information) {
        console.log('Personal information from drupalSettings:', settings.appointment.personal_information);
      }

      // Ensure the DOM is fully loaded.
      document.addEventListener('DOMContentLoaded', function () {
        // Get the form wrapper element.
        var formWrapper = document.getElementById('booking-form-wrapper');

        // Ensure the form wrapper exists and the behavior is only attached once.
        if (formWrapper && !formWrapper.dataset.personalInformationFormAttached) {
          // Mark the form wrapper as processed.
          formWrapper.dataset.personalInformationFormAttached = true;

          // Add a submit event listener to the form.
          formWrapper.addEventListener('submit', function (e) {
            // Prevent the default form submission.
            e.preventDefault();

            // Capture form values.
            var firstName = document.querySelector('input[name="first_name"]').value;
            var lastName = document.querySelector('input[name="last_name"]').value;
            var phone = document.querySelector('input[name="phone"]').value;
            var email = document.querySelector('input[name="email"]').value;
            var terms = document.querySelector('input[name="terms"]').checked;

            // Log the captured values to the console.
            console.log('Captured Form Values:', {
              firstName: firstName,
              lastName: lastName,
              phone: phone,
              email: email,
              terms: terms,
            });

            // Send the captured values to the server via AJAX.
            fetch('/appointment/save-personal-information', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                phone: phone,
                email: email,
                terms: terms,
              }),
            })
              .then(function (response) {
                if (response.ok) {
                  return response.json();
                }
                throw new Error('Network response was not ok.');
              })
              .then(function (data) {
                console.log('Personal information saved to tempstore:', data);
              })
              .catch(function (error) {
                console.error('Failed to save personal information:', error);
              });
          });
        }
      });
    }
  };
})(Drupal);
