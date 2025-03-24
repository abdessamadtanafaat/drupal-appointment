<?php

namespace Drupal\appointment\Form;

use Drupal\appointment\Service\AppointmentMailerService;
use Drupal\appointment\Service\AppointmentStorage;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;


/**
 * Multi-step Booking Form.
 */
class BookingForm extends FormBase {

  /**
   * The appointment mailer service.
   *
   * @var \Drupal\appointment\Service\AppointmentMailerService
   */
  protected $appointmentMailer;

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStoreFactory;

  /**
   * The appointment storage service.
   */
  protected AppointmentStorage $appointmentStorage;

  /**
   * Constructs a new BookingForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\appointment\Service\AppointmentMailerService $appointment_mailer
   *   The appointment mailer service.
   */

  public function __construct(PrivateTempStoreFactory $tempStoreFactory,
                              AppointmentMailerService $appointment_mailer,
                              AppointmentStorage $appointment_storage
  ) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->tempStore = $this->tempStoreFactory->get('appointment');
    $this->appointmentMailer = $appointment_mailer;
    $this->appointmentStorage = $appointment_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface|\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('appointment.mailer'),
      $container->get('appointment.storage')
    );
  }

  /**
   * Returns the form ID.
   */
  public function getFormId() {
    return 'appointment_booking_form';
  }

  /**
   * Builds the form dynamically based on the current step.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get current step, defaulting to step 1.
    $step = $form_state->get('step') ?? 1;

    \Drupal::logger('appointment')->notice('Building form for step: ' . $step); // Log the current step.

    // Clear the tempStore when starting a new appointment.
    if (!$form_state->get('step')) {
      $this->clearTempStore();
    }

    // Wrapper for AJAX updates.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    switch ($step) {
      case 1:
        $form = $this->step1($form, $form_state);
        break;
      case 2:
        $form = $this->step2($form, $form_state);
        break;
      case 3:
        $form = $this->step3($form, $form_state);
        break;
      case 4:
        $form = $this->step4($form, $form_state);
        break;
      case 5:
        $form = $this->step5($form, $form_state);
        break;
      case 6:
        $form = $this->step6($form, $form_state);
        break;
      default:
        throw new \InvalidArgumentException('Invalid step');
    }

    return $form;
  }

  /**
   * Step 1: Choose Agency.
   */
  public function step1($form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Attach the library.
    $form['#attached']['library'][] = 'appointment/appointment_styles';
    $form['#attached']['library'][] = 'appointment/appointment_scripts';

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Choose an agency') . '</h3></div>',
    ];

    // Hidden input field to store the selected agency ID.
    $form['agency_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'name' => 'agency_id',
      ],
    ];

    // Retrieve the list of agencies.
    $agencies = $this->getAgencies();

    // Loop through the agencies and prepare them for rendering as cards.
    $agency_cards = [];
    foreach ($agencies as $agency) {
      // Prepare the card HTML for each agency.
      $agency_cards[] = [
        '#theme' => 'agency_card',
        '#agency' => $agency,
        '#agency_id' => $agency->id(),
      ];
    }

    // Add the agency cards to the form render array.
    $form['agency_cards'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['agency-cards-container']],
      'cards' => $agency_cards,
    ];

    // Add a submit button.
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];


    return $form;
  }
  /**
   * Step 2: Select Appointment Type.
   */
  public function step2($form, FormStateInterface $form_state) {

    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Attach the library.
    $form['#attached']['library'][] = 'appointment/appointment_types_styles';
    $form['#attached']['library'][] = 'appointment/appointment_types_scripts';

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Start now, book your appointment') . '</h3></div>',
    ];


    // Retrieve the list of appointment types.
    $appointment_types = $this->getAppointmentTypes();

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/file.png';

    // Loop through the appointment types and prepare them for rendering as cards.
    $appointment_type_cards = [];
    foreach ($appointment_types as $id => $label) {
      $appointment_type_cards[] = [
        '#theme' => 'appointment_type_card',
        '#appointment_type' => $label,
        '#appointment_type_id' => $id,
        '#image_path' => $image_path,
      ];
    }

    // Add the appointment type cards to the form render array.
    $form['appointment_types'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['appointment_types-cards-container']],
      'cards' => $appointment_type_cards,
    ];

    // Add a hidden field to store the selected appointment type ID.
    $form['appointment_type_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'name' => 'appointment_type_id',
      ],
    ];

    // Add a hidden field to store the selected appointment type name.
    $form['appointment_type_name'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'name' => 'appointment_type_name',
        ],
    ];

    // Navigation buttons.
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',

      ],
    ];

    return $form;
  }

  /**
   * Step 3: Select Advisor.
   */
  public function step3($form, FormStateInterface $form_state) {

    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Attach the library.
    $form['#attached']['library'][] = 'appointment/advisor_selection_styles';
    $form['#attached']['library'][] = 'appointment/advisor_selection_scripts';

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Select your advisor') . '</h3></div>',
    ];

    // Retrieve the list of advisors.
    $advisors= $this->getAdvisors();

    \Drupal::logger('advisors')->notice('Advisors Values: ' . print_r($advisors, TRUE));

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/user.png';

    // Loop through the appointment types and prepare them for rendering as cards.
    $advisors_cards = [];
    foreach ($advisors as $id => $user) {
      $advisors_cards[] = [
        '#theme' => 'advisor_card',
        '#advisor' => $user,
        '#advisor_id' => $id,
        '#image_path'=>$image_path,
      ];
    }

    // Add the appointment type cards to the form render array.
    $form['advisors'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['advisors-cards-container']],
      'cards' => $advisors_cards,
    ];

    // Add a hidden field to store the selected appointment type ID.
    $form['advisor_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'name' => 'advisor_id',
      ],
    ];

    // Navigation buttons.
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',

      ],
    ];

    return $form;
  }

  /**
   * Step 4:  Date and Time Selection .
   */

  public function step4($form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Retrieve the appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];


    // Attach the updated values to drupalSettings.
    $form['#attached']['drupalSettings']['appointment'] = [
      'agency_id' => $values['agency_id'] ?? NULL,
      'appointment_type_id' => $values['appointment_type_id'] ?? NULL,
      'appointment_type_name' => $values['appointment_type_name'] ?? NULL,
      'advisor_id' => $values['advisor_id'] ?? NULL,
    ];

    // Attach the FullCalendar library.
    $form['#attached']['library'][] = 'appointment/calendar_scripts';


    \Drupal::logger('advisors')->notice('updated Values f step 4: ' . print_r($values, TRUE));


    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Select a date and time for your appointment') . '</h3></div>',
    ];


    // Add a container for the calendar.
    $form['calendar'] = [
      '#markup' => '<div id="calendar"></div>',
    ];

    // Hidden field to store the selected date and time.
    $form['selected_datetime'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'edit-selected-datetime', // Add an ID for JavaScript targeting
        'name' => 'selected_datetime',
      ],
    ];

    // Navigation buttons.
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * Step 5: Personal Information.
   */

  public function step5(array &$form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Attach the necessary libraries.
    $form['#attached']['library'][] = 'appointment/personal_information_style';

    // Retrieve the appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('Tempstore data in step5: ' . print_r($values, TRUE));

    // Prepare appointment details for the Twig template.
    $appointment_details = $this->renderAppointmentDetails($values);

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/calendar-success.png';

    // Container for the appointment details and personal information form.
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['personal-information-container']],
      'appointment_details' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['appointment-details']],
        'image' => [
          '#theme' => 'image',
          '#uri' => $image_path,
          '#alt' => $this->t('Calendar Icon'),
          '#attributes' => ['class' => ['calendar-icon']],
        ],
        'title' => [
          '#markup' => '<h4 class="appointment-title">' . $this->t('Your appointment') . '</h4>',
        ],
        'date' => [
          '#markup' => '<p class="appointment-label"><strong>' . $this->t('Day:') . '</strong></p>' .
            '<p class="appointment-value"><strong>' . $appointment_details['date'] . '</strong></p>',
        ],
        'time' => [
          '#markup' => '<p class="appointment-label"><strong>' . $this->t('Time:') . '</strong></p>' .
            '<p class="appointment-value"><strong>' . $appointment_details['time'] . '</strong></p>',
        ],
      ],
      'personal_information_form' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['personal-information-form']],
        'first_name' => [
          '#type' => 'textfield',
          '#title' => $this->t('First Name'),
          '#required' => TRUE,
          '#default_value' => $values['first_name'] ?? '',
          '#attributes' => [
            'placeholder' => $this->t('Enter your first name'),
          ],
        ],
        'last_name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Last Name'),
          '#required' => TRUE,
          '#default_value' => $values['last_name'] ?? '',
          '#attributes' => [
            'placeholder' => $this->t('Enter your last name'),
          ],
        ],
        'phone' => [
          '#type' => 'tel',
          '#title' => $this->t('Phone'),
          '#required' => TRUE,
          '#default_value' => $values['phone'] ?? '',
          '#attributes' => [
            'placeholder' => $this->t('Enter your phone number'),
          ],
        ],
        'email' => [
          '#type' => 'email',
          '#title' => $this->t('Email'),
          '#required' => TRUE,
          '#default_value' => $values['email'] ?? '',
          '#attributes' => [
            'placeholder' => $this->t('Enter your email address'),
          ],
        ],
        'terms' => [
          '#type' => 'checkbox',
          '#title' => $this->t('I agree to the terms and conditions'),
          '#required' => TRUE,
          '#default_value' => $values['terms'] ?? FALSE,
        ],
      ],
    ];

    // Navigation buttons.
    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['form-actions']],
      'prev' => [
        '#type' => 'submit',
        '#value' => $this->t('Previous'),
        '#submit' => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::updateFormStep',
          'wrapper' => 'booking-form-wrapper',
          'effect' => 'fade',
        ],
      ],
      'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::nextStep'],
        '#ajax' => [
          'callback' => '::updateFormStep',
          'wrapper' => 'booking-form-wrapper',
          'effect' => 'fade',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Step 6: Confirmation.
   */
  public function step6(array &$form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
    $form['#prefix'] = '<div id="booking-form-wrapper">';
    $form['#suffix'] = '</div>';

    // Attach the necessary libraries.
    $form['#attached']['library'][] = 'appointment/confirm_information_style';

    // Retrieve the appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('Tempstore data in step6: ' . print_r($values, TRUE));

    // Prepare appointment details for the Twig template.
    $appointment_details = $this->renderAppointmentDetails($values);

    // Prepare appointment details for the Twig template.
    $appointment_details_confirmation = [
      'appointment_type_name' => $values['appointment_type_name'] ?? '',
      'date' => $appointment_details['date'] ?? 'N/A',
      'time' => $appointment_details['time'] ?? 'N/A',
      'first_name' => $values['first_name'] ?? '',
      'last_name' => $values['last_name'] ?? '',
      'phone' => $values['phone'] ?? '',
      'email' => $values['email'] ?? '',
    ];


    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('appointment_details: ' . print_r($appointment_details_confirmation, TRUE));



    // Use the #theme property to render the Twig template.
    $form['container'] = [
      '#theme' => 'confirm_information',
      '#appointment_details_confirmation' => $appointment_details_confirmation,
    ];

    // Navigation buttons.
    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Confirm'),
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::submitForm',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * Updates the form dynamically using AJAX.
   */
  public function updateFormStep(array $form, FormStateInterface $form_state) {
    \Drupal::logger('appointment')->notice('updateFormStep method triggered.'); // Log to confirm the method is called.

    return $form;
  }

  /**
   * Moves to the next step.
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    $currentStep = $form_state->get('step') ?? 1;

    // Retrieve existing values from tempStore.
    $values = $this->tempStore->get('values') ?? [];

    // Save form values to tempStore.
    $values['agency_id'] = $form_state->getValue('agency_id') ?? $values['agency_id'];
    $values['appointment_type_id'] = $form_state->getValue('appointment_type_id') ?? $values['appointment_type_id'];
    $values['appointment_type_name'] = $form_state->getValue('appointment_type_name') ?? $values['appointment_type_name'];
    $values['selected_datetime'] = $form_state->getValue('selected_datetime') ?? $values['selected_datetime'];
    $values['advisor_id'] = $form_state->getValue('advisor_id') ?? $values['advisor_id'];
    $values['first_name'] = $form_state->getValue('first_name') ?? $values['first_name'];
    $values['last_name'] = $form_state->getValue('last_name') ?? $values['last_name'];
    $values['phone'] = $form_state->getValue('phone') ?? $values['phone'];
    $values['email'] = $form_state->getValue('email') ?? $values['email'];
    $values['terms'] = $form_state->getValue('terms') ?? $values['terms'];

    // Save updated values to tempStore.
    $this->tempStore->set('values', $values);

    // Log the updated values for debugging.
    \Drupal::logger('appointment')->notice('Updated TempStore Values: ' . print_r($values, TRUE));

    // Move to the next step.
    $nextStep = $currentStep + 1;
    $form_state->set('step', $nextStep);

    // Log the next step for debugging.
    \Drupal::logger('appointment')->notice('Next Step: ' . $nextStep);

    // Rebuild the form.
    $form_state->setRebuild(TRUE);
  }
  /**
   * Moves to the previous step.
   */
  public function prevStep(array &$form, FormStateInterface $form_state) {
    // Retrieve existing values from tempStore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempStore values before decrementing the step.
    \Drupal::logger('appointment')->notice('TempStore values before decrementing step: ' . print_r($values, TRUE));

    // Decrement the step.
    $currentStep = $form_state->get('step') ?? 1;
    $previousStep = $currentStep - 1;
    $previousStep = max(1, $previousStep);

    // Log the previous step.
    \Drupal::logger('appointment')->notice('Moving to previous step: ' . $previousStep);

    // Set the new step in the form state.
    $form_state->set('step', $previousStep);

    // Rebuild the form.
    $form_state->setRebuild(TRUE);

    // Log the tempStore values after decrementing the step.
    \Drupal::logger('appointment')->notice('TempStore values after decrementing step: ' . print_r($this->tempStore->get('values'), TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Get the current step.
    $step = $form_state->get('step') ?? 1;

    // Retrieve the appointment data from tempStore.
    $values = $this->tempStore->get('values') ?? [];

    // Validate Step 1: Ensure a card is selected.
    if ($step === 1) {
      $agency_id = $form_state->getValue('agency_id');

      // Check if agency_id is empty.
      if (empty($agency_id)) {
        // Set an error message if no card is selected.
        $form_state->setErrorByName('agency_id', $this->t('Please select an agency to proceed.'));
      // Rebuild the form to ensure it remains interactive.
      $form_state->setRebuild(TRUE);
      }

    }
    // Validate Step 2: Ensure a appointment type is selected.
    if ($step === 2) {
      $appointment_type_id = $form_state->getValue('appointment_type_id');

      // Check if agency_id is empty.
      if (empty($appointment_type_id)) {
        // Set an error message if no card is selected.
        $form_state->setErrorByName('appointment_type_id', $this->t('Please select an appointment type to proceed.'));
        // Rebuild the form to ensure it remains interactive.
        $form_state->setRebuild(TRUE);
      }
    }
    // Validate Step 3: Ensure an advisor is selected.
    if ($step === 3) {
      $advisor_id = $form_state->getValue('advisor_id');

      // Check if agency_id is empty.
      if (empty($advisor_id)) {
        // Set an error message if no card is selected.
        $form_state->setErrorByName('advisor_id', $this->t('Please select an advisor to proceed.'));
        // Rebuild the form to ensure it remains interactive.
        $form_state->setRebuild(TRUE);
      }
    }

    // Validate Step 4: Ensure a time slot is selected.
    if ($step === 4) {
      // Retrieve the selected datetime from the form state.
      $selected_datetime = $form_state->getValue('selected_datetime');

      // If the form state doesn't have the value, check the tempstore.
      if (empty($selected_datetime)) {
        $selected_datetime = $values['selected_datetime'] ?? NULL;
      }

      // Log the selected time slot for debugging.
      \Drupal::logger('appointment')->notice('Selected time slot: ' . $selected_datetime);

      // Check if selected_datetime is empty.
      if (empty($selected_datetime)) {
        // Set an error message if no time slot is selected.
        $form_state->setErrorByName('selected_datetime', $this->t('Please select a time slot to proceed.'));
      }
    }
  }


  // not knowing the css !
  public function submitForm(array &$form, FormStateInterface $form_state) {

//    // Check if the form has already been submitted.
//    if ($form_state->get('submitted')) {
//      \Drupal::logger('appointment')->notice('Form already submitted, skipping.');
//      return;
//    }
//
//    // Mark the form as submitted.
//    $form_state->set('submitted', TRUE);

    // Retrieve the appointment data from tempStore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('TempStore Values: ' . print_r($values, TRUE));

    // Save the appointment data (you need to implement this method).
     $this->saveAppointment($values);

    // Display a success message.
    \Drupal::messenger()->addMessage($this->t('The appointment is saved.'));

    // Clear the tempstore after saving.
    $this->clearTempStore();

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/calendar-success.png';

    // Attach the CSS library.
    $form['#attached']['library'][] = 'appointment/confirmation_style';

    // Prepare the confirmation message.
    $confirmation_message = [
      '#theme' => 'appointment_confirmation_message',
      '#image_path' => $image_path,
      '#title' => $this->t('Your appointment has been successfully booked.'),
      '#message' => $this->t('You can modify your appointment by entering your phone number.'),
      '#change_button' => [
        '#type' => 'link',
        '#title' => $this->t('Change Appointment'),
        '#url' => \Drupal\Core\Url::fromRoute('<front>'), // Replace with the correct route for changing the appointment.
        '#attributes' => [
          'class' => ['button', 'button--primary'],
        ],
      ],
    ];

    // Render the confirmation message.
    $confirmation_message_rendered = \Drupal::service('renderer')->render($confirmation_message);

    // Return an AJAX response to replace the form with the confirmation message.
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand('#booking-form-wrapper', $confirmation_message_rendered));

    return $response;
  }

  /**
   * Retrieves available agencies.
   *
   * @return array
   *   An associative array of agency IDs and names.
   */
  protected function getAgencies(): array {
    // Query for agency entities.
    $agency_storage = \Drupal::entityTypeManager()->getStorage('appointment_agency');
    return $agency_storage->loadMultiple();
  }

  /**
   * Fetches appointment types from the 'appointment_types' taxonomy vocabulary.
   *
   * @return array
   *   An associative array of appointment types keyed by term ID.
   */
  protected function getAppointmentTypes() {
    $appointmentTypes = [];

    // Load terms from the 'appointment_types' vocabulary.
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('appointment_types');

    // Build an options array from the terms.
    foreach ($terms as $term) {
      $appointmentTypes[$term->tid] = $term->name;
      $appointmentTypes[$term->tid] = $term->name;

    }

    return $appointmentTypes;
  }

  /**
   * Retrieves the list of advisors.
   *
   * @return array
   *   An associative array of advisor IDs and names.
   */
  protected function getAdvisors(): array {
    $advisors = [];

    // Load the user storage service.
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');

    // Query users with a specific role (e.g., 'advisor').
    $query = $user_storage->getQuery()
      ->condition('status', 1) // Only active users.
      ->condition('roles', 'advisor') // Replace 'advisor' with the correct role machine name.
      ->sort('name', 'ASC') // Sort by name.
      ->accessCheck(TRUE); // Explicitly enable access checking.

    // Execute the query and get the user IDs.
    $uids = $query->execute();

    if (!empty($uids)) {
      // Load the user entities.
      $users = $user_storage->loadMultiple($uids);

      // Build the list of advisors.
      foreach ($users as $user) {
        $advisors[$user->id()] = $user; // Store the full user object.
      }
    }

    return $advisors;
  }

  protected function renderAppointmentDetails($values) {
    if (isset($values['selected_slot'])) {
      $start = $values['selected_slot']['start'];
      $end = $values['selected_slot']['end'];
      $title = $values['selected_slot']['title'];

      // Extract date, start hour, and end hour.
      $date = date('Y-m-d', strtotime($start)); // Format: YYYY-MM-DD
      $start_time = date('H:i', strtotime($start)); // Format: HH:MM (24-hour format)
      $end_time = date('H:i', strtotime($end)); // Format: HH:MM (24-hour format)

      return [
        'date' => $date,
        'time' => $start_time . ' - ' . $end_time,
        'title' => $title,
      ];
    }
    return [
      'date' => 'N/A',
      'time' => 'N/A',
      'title' => 'N/A',
    ];
  }

  /**
   * Saves the appointment data.
   */
  protected function saveAppointment(array $values): ?int {
    $appointment_id = $this->appointmentStorage->saveAppointment($values);

    if ($appointment_id) {
      $fields = $this->appointmentStorage->prepareAppointmentFields($values);
      $this->appointmentMailer->sendConfirmationEmail($fields, $appointment_id);
    }

    return $appointment_id;
  }

  protected function clearTempStore() {
    $this->tempStore->delete('values');
  }

}
