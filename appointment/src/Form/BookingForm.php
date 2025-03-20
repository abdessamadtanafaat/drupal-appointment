<?php

namespace Drupal\appointment\Form;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;


/**
 * Multi-step Booking Form.
 */
class BookingForm extends FormBase {

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStoreFactory;

  /**
   * Constructs a new BookingForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->tempStore = $this->tempStoreFactory->get('appointment');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface|\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
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
    ];

    // Add each card to the container.
    foreach ($agency_cards as $agency_card) {
      $form['agency_cards'][] = $agency_card;
    }

    // Add a submit button .
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

    // Store the selected agency ID in tempStore when the form is submitted.
    $agency_id = $form_state->getValue('agency_id');

    // Debugging: Log the agency_id value for troubleshooting.
    if ($agency_id) {
      \Drupal::logger('appointment')->debug('Agency ID selected: @agency_id', ['@agency_id' => $agency_id]);

      // Store the agency ID in tempStore
      $this->tempStore->set('agency_id', $agency_id);
    } else {
      \Drupal::logger('appointment')->warning('No agency ID selected.');
    }


    return $form;
  }

  /**
   * Step 2: Select Appointment Type.
   */
  public function step2($form, FormStateInterface $form_state) {

    $values = $this->tempStore->get('values') ?? [];


    // Retrieve the agency ID from tempStore.
    $agencyId = $form_state->getValue('agency_id');
    $this->tempStore->set('agency_id', $agencyId);

    \Drupal::logger('appointment')->notice('Stored Agency ID in tempstore: ' . $agencyId);

    // Add a hidden field to store the agency ID.
    $form['agency_id'] = [
      '#type' => 'hidden',
      '#value' => $agencyId,
      '#default_value' => $values['agency_id'] ?? '',
    ];

    // Appointment type selection.
    $form['appointment_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Appointment Type'),
      '#options' => $this->getAppointmentTypes(),
      '#required' => TRUE, // Ensure this field is required.
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
   * Updates the form dynamically using AJAX.
   */
  public function updateFormStep(array $form, FormStateInterface $form_state) {
    return $form;
  }


  /**
   * Moves to the next step.
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    // Retrieve the current step from the form state.

    $currentStep = $form_state->get('step') ?? 1;

    $values = $this->tempStore->get('values') ?? [];
    $values['agency_id'] = $form_state->getValue('agency_id');
    $this->tempStore->set('values', $values);

    // Log the current step for debugging.
    \Drupal::logger('appointment')->notice('Current Step: ' . $currentStep);

    // Move to the next step.
    $nextStep = $currentStep + 1;
    $form_state->set('step', $nextStep);

    // Log the next step after updating for debugging.
    \Drupal::logger('appointment')->notice('Next Step: ' . $nextStep);

    // Set the form to be rebuilt after updating the step.
    $form_state->setRebuild(TRUE);
  }


  /**
   * Moves to the previous step.
   */
  public function prevStep(array &$form, FormStateInterface $form_state) {
    $form_state->set('step', $form_state->get('step') - 1);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Get the current step.
    $step = $form_state->get('step') ?? 1;

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
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the selected agency ID from the form submission.
    $agencyId = $form_state->getValue('agency_id');

    // Store the agency ID in the tempstore.
    $this->tempStore->set('agency_id', $agencyId);

    // Move to the next step.
    $form_state->set('step', 2);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Retrieves available agencies.
   *
   * @return array
   *   An associative array of agency IDs and names.
   */
  protected function getAgencies(): array {
    // Query for agency entities.
    $agency_storage = \Drupal::entityTypeManager()->getStorage('agency');
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
    }

    return $appointmentTypes;
  }


}
