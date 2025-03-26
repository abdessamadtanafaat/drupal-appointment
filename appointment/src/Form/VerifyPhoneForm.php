<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\appointment\Service\AppointmentStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VerifyPhoneForm extends FormBase {

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
    return 'appointment_verify_phone_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Enter your booking phone number') . '</h3></div>',
    ];

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

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $cleaned_phone = preg_replace('/[^0-9]/', '', $phone);


    // Check if phone contains only numbers
    if (!ctype_digit($cleaned_phone)) {
      $form_state->setErrorByName('phone', $this->t('Phone number must contain only numbers.'));
      return;
    }

    if (empty($cleaned_phone)) {
      $form_state->setErrorByName('phone', $this->t('Phone number is required.'));
      return;
    }

    if (strlen($cleaned_phone) !== 10) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid 10-digit phone number.'));
      return;
    }

    $appointments = $this->appointmentStorage->findAllByPhone($cleaned_phone);
    if (empty($appointments)) {
      $form_state->setErrorByName('phone', $this->t('No appointments found for this phone number.'));
    }

    $form_state->setValue('phone', $cleaned_phone);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $form_state->setRedirect('appointment.view_appointments', [], [
      'query' => ['phone' => $phone]
    ]);
  }
}
