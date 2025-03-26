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
    $status_filter = $request->query->get('status', '1'); // Default to pending (status=1)

    // Add status filter dropdown
    $form['status_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter by status'),
      '#options' => [
        'all' => $this->t('All Appointments'),
        '1' => $this->t('Pending'),
        '2' => $this->t('Completed'),
        '0' => $this->t('Cancelled'),
      ],
      '#default_value' => $status_filter,
      '#ajax' => [
        'callback' => '::updateAppointmentsList',
        'wrapper' => 'appointments-list-wrapper',
        'event' => 'change',
        'method' => 'replace',
        'disable-refocus' => TRUE,
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Filtering appointments...'),
        ],
      ],
    ];

    $appointments = $this->appointmentStorage->findAllByPhone($phone, $status_filter);

    $form['appointments'] = [
      '#theme' => 'appointment_list',
      '#appointments' => $appointments,
      '#selected_status' => $status_filter,
      '#phone' => $phone,
      '#prefix' => '<div id="appointments-list-wrapper">',
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          'appointment/appointment_list',
        ],
      ],
    ];

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

    return $form;
  }

  /**
   * AJAX callback to update the appointments list when filter changes.
   */
  public function updateAppointmentsList(array &$form, FormStateInterface $form_state) {
    $phone = \Drupal::request()->query->get('phone');
    $status_filter = $form_state->getValue('status_filter');

    // Update the form with the new filter value
    $form['appointments']['#appointments'] = $this->appointmentStorage->findAllByPhone($phone, $status_filter);
    $form['appointments']['#selected_status'] = $status_filter;

    return $form['appointments'];
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $form_state->setRedirect('appointment.view_appointments', [], [
      'query' => ['phone' => $phone]
    ]);
  }

}
