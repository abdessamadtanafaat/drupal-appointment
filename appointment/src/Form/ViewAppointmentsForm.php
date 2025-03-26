<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\appointment\Service\AppointmentStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ViewAppointmentsForm extends FormBase {

  protected $appointmentStorage;

  public function __construct(AppointmentStorage $appointment_storage) {
    $this->appointmentStorage = $appointment_storage;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('appointment.storage')
    );
  }

  public function getFormId() {
    return 'appointment_view_appointments_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $phone = \Drupal::request()->query->get('phone');

    if (empty($phone)) {
      // If no phone provided, show phone input form
      return $this->buildPhoneInputForm($form);
    }

    // If phone provided, show appointments
    return $this->buildAppointmentsList($form, $phone);
  }

  protected function buildPhoneInputForm(array $form) {
    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter your booking phone number'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Find Appointments'),
      ],
    ];

    return $form;
  }

  protected function buildAppointmentsList(array $form, string $phone) {
    // Get the requested status filter from URL parameters
    $request = \Drupal::request();

    // Get all filter values from URL parameters
//    $status_filter = $request->query->get('status', '1');
    $date_filter = $request->query->get('date', '');
    $agency_filter = $request->query->get('agency', 'all');
    $advisor_filter = $request->query->get('advisor', 'all');


    // Get available agencies and advisors for filter options
    $agencies = $this->appointmentStorage->getAgenciesByPhone($phone);
    $advisors = $this->appointmentStorage->getAdvisorsByPhone($phone);

    // Add a back button to return to phone input
    $form['back'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to search'),
      '#url' => \Drupal\Core\Url::fromRoute('appointment.view_appointments'),
      '#attributes' => [
        'class' => ['button'],
      ],
      '#weight' => -10,
    ];

    // Add filter controls
    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['appointment-filters']],
      '#weight' => -10,
    ];

    // Status filter
//    $form['filters']['status_filter'] = [
//      '#type' => 'select',
//      '#title' => $this->t('Status'),
//      '#options' => [
//        'all' => $this->t('All Statuses'),
//        '1' => $this->t('Pending'),
//        '2' => $this->t('Completed'),
//        '0' => $this->t('Cancelled'),
//      ],
//      '#default_value' => $status_filter,
//    ];

    // Date filter
    $form['filters']['date_filter'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#default_value' => $date_filter,
    ];

    // Agency filter
    $form['filters']['agency_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Agency'),
      '#options' => ['all' => $this->t('All Agencies')] + $agencies,
      '#default_value' => $agency_filter,
    ];

    // Advisor filter
    $form['filters']['advisor_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Advisor'),
      '#options' => ['all' => $this->t('All Advisors')] + $advisors,
      '#default_value' => $advisor_filter,
    ];

    // Apply button
    $form['filters']['apply'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
      '#ajax' => [
        'callback' => '::updateAppointmentsList',
        'wrapper' => 'appointments-list-wrapper',
        'method' => 'replace',
      ],
    ];

    // Reset button
    $form['filters']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => ['::resetFilters'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::updateAppointmentsList',
        'wrapper' => 'appointments-list-wrapper',
      ],
    ];

    // Get filtered appointments
    $appointments = $this->appointmentStorage->findAllByPhone($phone, [
//      'status' => $status_filter,
      'date' => $date_filter,
      'agency' => $agency_filter,
      'advisor' => $advisor_filter,
    ]);

    $form['appointments'] = [
      '#theme' => 'appointment_list',
      '#appointments' => $appointments,
      '#phone' => $phone,
      '#prefix' => '<div id="appointments-list-wrapper">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => ['appointment/appointment_list'],
      ],
    ];

    return $form;
  }

  /**
   * AJAX callback to update the appointments list when filter changes.
   */
  public function updateAppointmentsList(array &$form, FormStateInterface $form_state) {
    $phone = \Drupal::request()->query->get('phone');
    $filters = [
//      'status' => $form_state->getValue('status_filter'),
      'date' => $form_state->getValue('date_filter'),
      'agency' => $form_state->getValue('agency_filter'),
      'advisor' => $form_state->getValue('advisor_filter'),
    ];

    // Get filtered appointments
    $appointments = $this->appointmentStorage->findAllByPhone($phone, $filters);

    // Update the appointments list
    $form['appointments']['#appointments'] = $appointments;

    // Add status messages if needed
    if (empty($appointments)) {
      $this->messenger()->addWarning($this->t('No appointments found matching your filters.'));
    }

    return $form['appointments'];
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $form_state->setRedirect('appointment.view_appointments', [], [
      'query' => ['phone' => $phone]
    ]);
  }

}
