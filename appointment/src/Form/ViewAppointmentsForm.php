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
    $appointments = $this->appointmentStorage->findAllByPhone($phone);

    $form['appointments'] = [
      '#theme' => 'appointment_list',
      '#appointments' => $appointments,
      '#phone' => $phone,
      '#attached' => [
        'library' => [
          'appointment/appointment_list',
        ],
      ],
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $form_state->setRedirect('appointment.view_appointments', [], [
      'query' => ['phone' => $phone]
    ]);
  }

}
