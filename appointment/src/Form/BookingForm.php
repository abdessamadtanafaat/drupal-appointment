<?php

namespace Drupal\appointment\Form;

use Drupal\appointment\Service\AppointmentMailerService;
use Drupal\appointment\Service\AppointmentStorage;
use Drupal\appointment\Service\FormNavigation;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
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

  protected FormNavigation $formNavigation;
  protected LoggerChannelInterface $logger;


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
                              AppointmentStorage $appointment_storage,
                               FormNavigation $form_navigation,
                                LoggerChannelFactoryInterface $logger_factory

  ) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->tempStore = $this->tempStoreFactory->get('appointment');
    $this->appointmentMailer = $appointment_mailer;
    $this->appointmentStorage = $appointment_storage;
    $this->formNavigation = $form_navigation;
    $this->logger = $logger_factory->get('appointment');

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface|\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('appointment.mailer'),
      $container->get('appointment.storage'),
      $container->get('appointment.form_navigation'),
      $container->get('logger.factory')
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

    $this->formNavigation->initializeFormState($form_state);
    $step = $this->formNavigation->getCurrentStep();

    $this->logger->notice('Building form for step: @step', ['@step' => $step]);

    // Add form wrapper
    $form += $this->formNavigation->getFormWrapper();

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
      case 7:
        $form = $this->step7($form, $form_state);
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
    $form += $this->formNavigation->getFormWrapper();

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
    $agencies = $this->appointmentStorage->getAgencies();

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

    // Add navigation buttons
    $form['actions'] = $this->formNavigation->getNavigationButtons(1);

    return $form;
  }
  /**
   * Step 2: Select Appointment Type.
   */
  public function step2($form, FormStateInterface $form_state) {

    // Enable AJAX for the form.
 $form += $this->formNavigation->getFormWrapper();

    // Attach the library.
    $form['#attached']['library'][] = 'appointment/appointment_types_styles';
    $form['#attached']['library'][] = 'appointment/appointment_types_scripts';

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Start now, book your appointment') . '</h3></div>',
    ];


    // Retrieve the list of appointment types.
    $appointmentTypes = $this->appointmentStorage->getAppointmentTypes();

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/file.png';

    // Loop through the appointment types and prepare them for rendering as cards.
    $appointment_type_cards = [];
    foreach ($appointmentTypes as $id => $label) {
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

    // Add navigation buttons
    $form['actions'] = $this->formNavigation->getNavigationButtons(2);

    return $form;
  }

  /**
   * Step 3: Select Advisor.
   */
  public function step3($form, FormStateInterface $form_state) {

    // Enable AJAX for the form.
 $form += $this->formNavigation->getFormWrapper();

    // Attach the library.
    $form['#attached']['library'][] = 'appointment/advisor_selection_styles';
    $form['#attached']['library'][] = 'appointment/advisor_selection_scripts';

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Select your advisor') . '</h3></div>',
    ];

    // Retrieve the list of advisors.
    $advisors = $this->appointmentStorage->getAdvisors();

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

    // Add navigation buttons
    $form['actions'] = $this->formNavigation->getNavigationButtons(3);

    return $form;
  }

  /**
   * Step 4:  Date and Time Selection .
   */

  public function step4($form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
 $form += $this->formNavigation->getFormWrapper();

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

    // Add navigation buttons
    $form['actions'] = $this->formNavigation->getNavigationButtons(4);

    return $form;
  }

  /**
   * Step 5: Personal Information.
   */

  public function step5(array &$form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
  $form += $this->formNavigation->getFormWrapper();

    // Attach the necessary libraries.
    $form['#attached']['library'][] = 'appointment/personal_information_style';

    // Retrieve the appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('Tempstore data in step5: ' . print_r($values, TRUE));

    // Prepare appointment details for the Twig template.
    $appointment_details = $this->formNavigation->renderAppointmentDetails($values);

    \Drupal::logger('appointment')->notice('details: ' . print_r($appointment_details, TRUE));


    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/calendar-success.png';

    // Container for the appointment details and personal information form.
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['personal-information-container']],
      'appointment_details' => $this->formNavigation->buildAppointmentDetailsSection($appointment_details, $image_path),
      'personal_information_form' => $this->formNavigation->buildPersonalInformationForm($values),
    ];

    // Add navigation buttons using FormNavigation service
    $form['actions'] = $this->formNavigation->getNavigationButtons(5);

    return $form;
  }

  /**
   * Step 6: Confirmation.
   */
  public function step6(array &$form, FormStateInterface $form_state) {
    // Enable AJAX for the form.
 $form += $this->formNavigation->getFormWrapper();

    // Attach the necessary libraries.
    $form['#attached']['library'][] = 'appointment/confirm_information_style';

    // Retrieve the appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('Tempstore data in step6: ' . print_r($values, TRUE));

    // Prepare appointment details for the Twig template.
    $appointment_details = $this->formNavigation->renderAppointmentDetails($values);

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

    $form['actions'] = $this->formNavigation->getNavigationButtons(6, TRUE);

    return $form;
  }


  // the step7 method
  public function step7(array &$form, FormStateInterface $form_state) {
    $form += $this->formNavigation->getFormWrapper();

    // Attach the CSS library
    $form['#attached']['library'][] = 'appointment/phone_verification_style';

    // Get values from tempstore
    $values = $this->tempStore->get('values') ?? [];

    // Define the path to the image
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/verification-icon.png';

    // Render the phone verification form
    $form['container'] = [
      '#theme' => 'phone_verification',
      '#image_path' => $image_path,
    ];

    // Add navigation buttons
    $form['actions'] = $this->formNavigation->getNavigationButtons(7);

    return $form;
  }

  // Add this new method for phone verification
  public function verifyPhoneNumber(array &$form, FormStateInterface $form_state) {
  $response = new AjaxResponse();
  $phone_number = $form_state->getValue('phone');

  // Look up appointment by phone number
  $appointment = $this->appointmentStorage->findByPhone($phone_number);

    \Drupal::logger('appointment')->notice('appointment from db by phone: ' . $appointment);


    if ($appointment) {
    // If found, load the appointment data into tempstore for modification
    $values = [
      'agency_id' => $appointment->get('agency_id')->value,
      'appointment_type_id' => $appointment->get('appointment_type_id')->value,
      'appointment_type_name' => $appointment->get('appointment_type_name')->value,
      'advisor_id' => $appointment->get('advisor_id')->value,
      'selected_datetime' => $appointment->get('appointment_date')->value,
      'first_name' => $appointment->get('first_name')->value,
      'last_name' => $appointment->get('last_name')->value,
      'phone' => $appointment->get('phone')->value,
      'email' => $appointment->get('email')->value,
      'existing_appointment_id' => $appointment->id(),
    ];

    $this->tempStore->set('values', $values);

    // Redirect to step 1 to start modification
    $form_state->set('step', 1);
    $form_state->setRebuild(TRUE);
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand('#booking-form-wrapper', $form));
  } else {
    // Show error if not found
    $error = [
      '#type' => 'container',
      '#attributes' => ['class' => ['messages', 'messages--error']],
      '#markup' => $this->t('No appointment found with that phone number. Please try again.'),
    ];
    $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('#booking-form-wrapper', $error));
  }

  return $response;
}

  /**
   * AJAX callback for phone verification.
   */
  public function ajaxSubmitPhone(array &$form, FormStateInterface $form_state) {
    $phone_number = $form_state->getValue('phone_number');

    if (!preg_match('/^\+?[0-9]{10,15}$/', $phone_number)) {
      $response = '<p class="error">Invalid phone number. Please enter a valid number.</p>';
    } else {
      $response = '<p class="success">Phone number verified successfully!</p>';
      // Store the phone number (you can extend this to send OTP).
      $this->tempStore->set('phone_number', $phone_number);
    }

    $form['verification_message']['#markup'] = '<div id="phone-verification-message">' . $response . '</div>';

    return $form['verification_message'];
  }


  /**
   * Handles moving to the next step.
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    $this->formNavigation->nextStep($form, $form_state);
  }

  /**
   * Handles moving to the previous step.
   */
  public function prevStep(array &$form, FormStateInterface $form_state) {
    $this->formNavigation->prevStep($form, $form_state);
  }

  /**
   * AJAX callback to update the form step.
   */
  public function updateFormStep(array &$form, FormStateInterface $form_state) {
    return $this->formNavigation->updateFormStep($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): AjaxResponse {

    // Mark the form as submitted.
    $form_state->set('submitted', False);

    // Retrieve the appointment data from tempStore.
    $values = $this->tempStore->get('values') ?? [];

    // Log the tempstore data for debugging.
    \Drupal::logger('appointment')->notice('TempStore Values: ' . print_r($values, TRUE));

    // Save the appointment using the storage service
    $appointment_id = $this->appointmentStorage->saveAppointment($values);

    if ($appointment_id) {
      // Get the prepared fields for email
      $fields = $this->appointmentStorage->prepareAppointmentFields($values);

      // Send confirmation email
      $this->appointmentMailer->sendConfirmationEmail($fields, $appointment_id);
    }

      // Display a success message.
    \Drupal::messenger()->addMessage($this->t('The appointment is saved.'));

    // Clear the tempstore after saving.
    $this->formNavigation->clearTempStore();

    // Define the path to the image.
    $image_path = base_path() . \Drupal::service('extension.list.module')->getPath('appointment') . '/assets/calendar-success.png';

    // Attach the CSS library.
    $form['#attached']['library'][] = 'appointment/confirmation_style';
    // Attach necessary libraries
    $form['#attached']['library'][] = 'appointment/confirmation';

    $confirmation_message = [
      '#theme' => 'appointment_confirmation_message',
      '#image_path' => $image_path,
      '#title' => $this->t('Your appointment has been successfully booked.'),
      '#message' => $this->t('You can modify your appointment by clicking below.'),
      '#change_button' => [
        '#type' => 'button',
        '#value' => $this->t('Change Appointment'),
        '#attributes' => [
          'class' => ['button', 'button--primary'],
          'id' => 'change-appointment-button',
        ],
        '#ajax' => [
          'callback' => '::loadPhoneVerificationForm',
          'wrapper' => 'booking-form-wrapper',
        ]
      ],
    ];

    // Render the confirmation message.
    $confirmation_message_rendered = \Drupal::service('renderer')->render($confirmation_message);

    // Return an AJAX response to replace the form with the confirmation message.
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand('#booking-form-wrapper', $confirmation_message_rendered));

    return $response;
  }


}
