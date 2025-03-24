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
    $form['agency_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Agency'),
      '#options' => $this->getAgencyOptions(),
      '#default_value' => $this->appointment->get('agency_id')->value,
      '#required' => TRUE,
    ];

    // Appointment type selection
    $form['appointment_type_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Appointment Type'),
      '#options' => $this->appointmentStorage->getAppointmentTypes(),
      '#default_value' => $this->appointment->get('appointment_type')->value,
      '#required' => TRUE,
    ];

    // Personal information
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => $this->appointment->get('first_name')->value,
      '#required' => TRUE,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => $this->appointment->get('last_name')->value,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $this->appointment->get('email')->value,
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone'),
      '#default_value' => $this->appointment->get('phone')->value,
      '#required' => TRUE,
    ];

    // Advisor selection
    $form['advisor_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Advisor'),
      '#options' => $this->getAdvisorOptions(),
      '#default_value' => $this->appointment->get('advisor_id')->value,
      '#required' => TRUE,
    ];

    // Date and time
    $form['start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Start Date & Time'),
      '#default_value' => DrupalDateTime::createFromTimestamp(strtotime($this->appointment->get('start_date')->value)),
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('End Date & Time'),
      '#default_value' => DrupalDateTime::createFromTimestamp(strtotime($this->appointment->get('end_date')->value)),
      '#required' => TRUE,
    ];

    // Title
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->appointment->get('title')->value,
      '#required' => FALSE,
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

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Update all fields from the form
    $this->appointment->set('agency_id', $form_state->getValue('agency_id'));
    $this->appointment->set('appointment_type', $form_state->getValue('appointment_type_id'));
    $this->appointment->set('first_name', $form_state->getValue('first_name'));
    $this->appointment->set('last_name', $form_state->getValue('last_name'));
    $this->appointment->set('email', $form_state->getValue('email'));
    $this->appointment->set('phone', $form_state->getValue('phone'));
    $this->appointment->set('advisor_id', $form_state->getValue('advisor_id'));
    $this->appointment->set('start_date', $form_state->getValue('start_date')->format('Y-m-d\TH:i:s'));
    $this->appointment->set('end_date', $form_state->getValue('end_date')->format('Y-m-d\TH:i:s'));
    $this->appointment->set('title', $form_state->getValue('title'));

    // Update derived fields
    $this->appointment->set('agency', $this->appointmentStorage->getAgencyName($form_state->getValue('agency_id')));
    $this->appointment->set('appointment_type_name', $this->appointmentStorage->getAppointmentTypeName($form_state->getValue('appointment_type_id')));
    $this->appointment->set('advisor', $this->appointmentStorage->getAdvisorName($form_state->getValue('advisor_id')));
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
