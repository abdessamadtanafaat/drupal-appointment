<?php

namespace Drupal\appointment\Service;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Handles multi-step form navigation logic.
 */
class FormNavigation {

  use StringTranslationTrait;

  /**
   * The tempstore service.
   */
  protected PrivateTempStoreFactory $tempStoreFactory;

  /**
   * The current form step.
   */
  protected int $currentStep;

  /**
   * The total number of steps.
   */
  protected int $totalSteps = 6;

  protected LoggerChannelInterface $logger;

  /**
   * Constructs a new FormNavigation service.
   */

  public function __construct(PrivateTempStoreFactory $tempStoreFactory,
                              LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->tempStore = $tempStoreFactory->get('appointment');
    $this->logger = $logger_factory->get('appointment');

  }

  /**
   * Initializes form state with step information.
   */
  public function initializeFormState(FormStateInterface $form_state): void {
    if (!$form_state->get('step')) {
      $form_state->set('step', 1);
      $this->clearTempStore();
    }
    $this->currentStep = $form_state->get('step');
  }

  /**
   * Gets the current step.
   */
  public function getCurrentStep(): int {
    return $this->currentStep;
  }

  /**
   * Gets the total number of steps.
   */
  public function getTotalSteps(): int {
    return $this->totalSteps;
  }

  /**
   * Clears the tempstore.
   */
  public function clearTempStore(): void {
    $this->tempStore->delete('values');
  }

  /**
   * Gets the navigation buttons for a step.
   */
  public function getNavigationButtons(int $step, bool $is_last_step = FALSE): array {
    $buttons = [];

    if ($step > 1) {
      $buttons['prev'] = [
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
    }

    $buttons['next'] = [
      '#type' => 'submit',
      '#value' => $is_last_step ? $this->t('Confirm') : $this->t('Next'),
      '#submit' => $is_last_step ? ['::submitForm'] : ['::nextStep'],
      '#ajax' => [
        'callback' => $is_last_step ? '::submitForm' : '::updateFormStep',
        'wrapper' => 'booking-form-wrapper',
        'effect' => 'fade',
      ],
    ];

    return $buttons;
  }

  /**
   * Gets the form wrapper attributes.
   */
  public function getFormWrapper(): array {
    return [
      '#prefix' => '<div id="booking-form-wrapper">',
      '#suffix' => '</div>'
    ];
  }

  public function renderAppointmentDetails($values): array {
    if (isset($values['selected_slot'])) {
      $start = $values['selected_slot']['start'];
      $end = $values['selected_slot']['end'];
      $title = $values['selected_slot']['title'];

      \Drupal::logger('selectedslot')->notice('slot: ' . print_r($start, TRUE));


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
   * Builds the appointment details section.
   */
  public function buildAppointmentDetailsSection(array $appointment_details, string $image_path): array {
    return [
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
    ];
  }

  /**
   * Builds the personal information form section.
   */
  public function buildPersonalInformationForm(array $values): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['personal-information-form']],
      'first_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('First Name'),
        '#required' => TRUE,
        '#default_value' => $values['first_name'] ?? '',
        '#attributes' => ['placeholder' => $this->t('Enter your first name')],
      ],
      'last_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Last Name'),
        '#required' => TRUE,
        '#default_value' => $values['last_name'] ?? '',
        '#attributes' => ['placeholder' => $this->t('Enter your last name')],
      ],
      'phone' => [
        '#type' => 'tel',
        '#title' => $this->t('Phone'),
        '#required' => TRUE,
        '#default_value' => $values['phone'] ?? '',
        '#attributes' => ['placeholder' => $this->t('Enter your phone number')],
      ],
      'email' => [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#required' => TRUE,
        '#default_value' => $values['email'] ?? '',
        '#attributes' => ['placeholder' => $this->t('Enter your email address')],
      ],
      'terms' => [
        '#type' => 'checkbox',
        '#title' => $this->t('I agree to the terms and conditions'),
        '#required' => TRUE,
        '#default_value' => $values['terms'] ?? FALSE,
      ],
    ];
  }

  /**
   * Updates the form step via AJAX.
   */
  public function updateFormStep(array $form, FormStateInterface $form_state): array {
    $this->logger->notice('Form step updated via AJAX');
    return $form;
  }

  /**
   * Handles moving to the next step.
   */
  public function nextStep(array &$form, FormStateInterface $form_state): void {

    $current_step = $form_state->get('step') ?? 1;
    $values = $this->getFormValues($form_state);

    $this->saveFormValues($values);
    $this->logger->notice('Moving to next step from @current', ['@current' => $current_step]);

    $form_state->set('step', min($current_step + 1, $this->getTotalSteps()));
    $form_state->setRebuild(TRUE);
  }

  /**
   * Handles moving to the previous step.
   */
  public function prevStep(array &$form, FormStateInterface $form_state): void {
    $current_step = $form_state->get('step') ?? 1;
    $this->logger->notice('Moving to previous step from @current', ['@current' => $current_step]);

    $form_state->set('step', max($current_step - 1, 1));
    $form_state->setRebuild(TRUE);
  }

  /**
   * Gets form values from form state and tempstore.
   */
  protected function getFormValues(FormStateInterface $form_state): array {
    $values = $this->tempStore->get('values') ?? [];
    \Drupal::logger('appointment')->notice('f get form values meth: ' . print_r($values, TRUE));

    return [
      'agency_id' => $form_state->getValue('agency_id') ?? $values['agency_id'] ?? NULL,
      'appointment_type_id' => $form_state->getValue('appointment_type_id') ?? $values['appointment_type_id'] ?? NULL,
      'appointment_type_name' => $form_state->getValue('appointment_type_name') ?? $values['appointment_type_name'] ?? NULL,
      'selected_datetime' => $form_state->getValue('selected_datetime') ?? $values['selected_datetime'] ?? NULL,
      'selected_slot' => $values['selected_slot'] ?? NULL,
      'advisor_id' => $form_state->getValue('advisor_id') ?? $values['advisor_id'] ?? NULL,
      'first_name' => $form_state->getValue('first_name') ?? $values['first_name'] ?? NULL,
      'last_name' => $form_state->getValue('last_name') ?? $values['last_name'] ?? NULL,
      'phone' => $form_state->getValue('phone') ?? $values['phone'] ?? NULL,
      'email' => $form_state->getValue('email') ?? $values['email'] ?? NULL,
    ];
  }

  /**
   * Saves form values to tempstore.
   */
  protected function saveFormValues(array $values): void {
    $this->tempStore->set('values', $values);
    \Drupal::logger('appointment')->notice('f save form values meth: ' . print_r($values, TRUE));

    $this->logger->notice('Form values saved to tempstore', ['values' => $values]);
  }

}
