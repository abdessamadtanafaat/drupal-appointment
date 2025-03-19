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
  protected $tempStore;

  /**
   * Constructs a new BookingForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStore = $tempStoreFactory->get('appointment');
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
      '#id' => 'edit-agency-id',
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

    // Add a submit button (hidden, used for JavaScript submission).
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
      ],
      '#attributes' => [
        'style' => 'display:none;', // Hide the button.
      ],
    ];

    return $form;
  }

  /**
   * Step 2: Select Appointment Type.
   */
  public function step2($form, FormStateInterface $form_state) {

    $tempStore = $this->tempStoreFactory->get('appointment');

    $agencyId = $this->tempStore->get('agency_id');

    // Debugging: Log the agency ID.
    \Drupal::logger('appointment')->notice('Step 2 - Agency ID from tempstore: ' . $agencyId);


    if (!$agencyId) {
      drupal_set_message($this->t('Please select an agency first.'), 'error');
      return $this->step1($form, $form_state);
    }

    // Add a hidden field to store the agency ID.
    $form['agency_id'] = [
      '#type' => 'hidden',
      '#value' => $agencyId,
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
      '#limit_validation_errors' => [], // Skip validation when going back.
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
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
    $form_state->set('step', $form_state->get('step') + 1);
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the selected agency ID from the form submission.
    $agencyId = $form_state->getValue('agency_id');

    // Debugging: Log the selected agency ID.
    \Drupal::logger('appointment')->notice('Selected Agency ID: ' . $agencyId);

    // Store the agency ID in the tempstore.
    $tempStore = $this->tempStoreFactory->get('appointment');
    $tempStore->set('agency_id', $agencyId);

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
