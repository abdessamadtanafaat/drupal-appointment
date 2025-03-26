<?php

namespace Drupal\appointment\Form;

use Drupal\appointment\Service\AppointmentStorage;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\appointment\Entity\Appointment;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModifyAppointmentForm extends FormBase {

  protected $appointmentStorage;
  protected $appointment;

  public function __construct(AppointmentStorage $appointment_storage) {
    $this->appointmentStorage = $appointment_storage;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('appointment.storage')
    );
  }

  public function getFormId() {
    return 'appointment_modify_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->appointment = Appointment::load($id);

    if (!$this->appointment) {
      $form['error'] = [
        '#markup' => $this->t('Appointment not found.'),
      ];
      return $form;
    }

    // Agency selection
//    $form['agency_id'] = [
//      '#type' => 'select',
//      '#title' => $this->t('Agency'),
//      '#options' => $this->getAgencyOptions(),
//      '#default_value' => $this->appointment->get('agency_id')->value,
//      '#required' => TRUE,
//      '#disabled' => TRUE,
//    ];

    // Appointment type selection
//    $form['appointment_type_id'] = [
//      '#type' => 'select',
//      '#title' => $this->t('Appointment Type'),
//      '#options' => $this->appointmentStorage->getAppointmentTypes(),
//      '#default_value' => $this->appointment->get('appointment_type')->value,
//      '#required' => TRUE,
//      '#disabled' => TRUE,
//    ];

    // Bloc 1: Appointment Details (read-only)
    $form['appointment_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Appointment Details'),
      '#open' => FALSE,
      '#attributes' => ['class' => ['appointment-details']],
    ];

    $form['appointment_details']['agency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Agency'),
      '#default_value' => $this->appointment->get('agency')->value,
      '#disabled' => TRUE,
    ];

    $form['appointment_details']['appointment_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Appointment Type'),
      '#default_value' => $this->appointment->get('appointment_type_name')->value,
      '#disabled' => TRUE,
    ];

    $form['appointment_details']['advisor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Advisor'),
      '#default_value' => $this->appointment->get('advisor')->value,
      '#disabled' => TRUE,
    ];

    $form['appointment_details']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        '0' => $this->t('Cancelled'),
        '1' => $this->t('Pending'),
        '2' => $this->t('Completed'),
      ],
      '#default_value' => $this->appointment->get('status')->value ?? '1',
      '#disabled' => TRUE,
    ];

    $form['appointment_details']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->appointment->get('label')->value,
      '#required' => FALSE,
    ];

    // Bloc 2: Personal Details (editable)
    $form['personal_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Personal Details'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['personal-details']],
    ];

    $form['personal_details']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => $this->appointment->get('first_name')->value,
      '#required' => TRUE,
    ];

    $form['personal_details']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => $this->appointment->get('last_name')->value,
      '#required' => TRUE,
    ];

    $form['personal_details']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $this->appointment->get('email')->value,
      '#required' => TRUE,
    ];

    $form['personal_details']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone'),
      '#default_value' => $this->appointment->get('phone')->value,
      '#required' => TRUE,
    ];

    // Bloc 3: Date and Time (editable)
    $form['date_time'] = [
      '#type' => 'details',
      '#title' => $this->t('Date & Time'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['date-time']],
    ];

    $form['date_time']['start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start Date & Time'),
      '#default_value' => DrupalDateTime::createFromTimestamp(strtotime($this->appointment->get('start_date')->value)),
      '#required' => TRUE,
    ];

    $form['date_time']['end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End Date & Time'),
      '#default_value' => DrupalDateTime::createFromTimestamp(strtotime($this->appointment->get('end_date')->value)),
      '#required' => TRUE,
    ];




    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save Changes'),
      ],
      'cancel' => [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => \Drupal\Core\Url::fromRoute('appointment.view_appointments'),
        '#attributes' => ['class' => ['button']],
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Get the submitted dates
    $start_date = $form_state->getValue('start_date')->format('Y-m-d\TH:i:s');
    $end_date = $form_state->getValue('end_date')->format('Y-m-d\TH:i:s');

    // Check if end date is after start date
    if (strtotime($end_date) <= strtotime($start_date)) {
      $form_state->setErrorByName('end_date', $this->t('End date must be after start date.'));
      return;
    }

    // Check for conflicting appointments (excluding current appointment)
    $conflicts = $this->appointmentStorage->checkTimeConflict(
      $start_date,
      $end_date,
      $this->appointment->id() // Exclude current appointment from conflict check
    );

    if (!empty($conflicts)) {
      $form_state->setErrorByName('start_date', $this->t('The selected time slot is already taken. Please choose another time.'));
      $form_state->setErrorByName('end_date');
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Update all fields from the form
//    $this->appointment->set('agency_id', $form_state->getValue('agency_id'));
//    $this->appointment->set('appointment_type', $form_state->getValue('appointment_type_id'));
//    $this->appointment->set('advisor_id', $form_state->getValue('advisor_id'));
    $this->appointment->set('first_name', $form_state->getValue('first_name'));
    $this->appointment->set('last_name', $form_state->getValue('last_name'));
    $this->appointment->set('email', $form_state->getValue('email'));
    $this->appointment->set('phone', $form_state->getValue('phone'));
    $this->appointment->set('start_date', $form_state->getValue('start_date')->format('Y-m-d\TH:i:s'));
    $this->appointment->set('end_date', $form_state->getValue('end_date')->format('Y-m-d\TH:i:s'));
    $this->appointment->set('title', $form_state->getValue('title'));

    // Update derived fields
//    $this->appointment->set('agency', $this->appointmentStorage->getAgencyName($form_state->getValue('agency_id')));
//    $this->appointment->set('appointment_type_name', $this->appointmentStorage->getAppointmentTypeName($form_state->getValue('appointment_type_id')));
//    $this->appointment->set('advisor', $this->appointmentStorage->getAdvisorName($form_state->getValue('advisor_id')));
    $this->appointment->set('description', $this->appointmentStorage->generateDescription(
      $form_state->getValue('start_date')->format('Y-m-d\TH:i:s'),
      $form_state->getValue('end_date')->format('Y-m-d\TH:i:s')
    ));

    $this->appointment->save();

    $this->messenger()->addStatus($this->t('Appointment updated successfully.'));
    $form_state->setRedirect('appointment.view_appointments');
  }

  protected function getAgencyOptions(): array {
    $agencies = $this->appointmentStorage->getAgencies();
    $options = [];

    foreach ($agencies as $agency) {
      $options[$agency->id()] = $agency->label();
    }

    return $options;
  }

  protected function getAdvisorOptions(): array {
    $advisors = $this->appointmentStorage->getAdvisors();
    $options = [];

    foreach ($advisors as $id => $advisor) {
      $options[$id] = $advisor->getDisplayName();
    }

    return $options;
  }
}
