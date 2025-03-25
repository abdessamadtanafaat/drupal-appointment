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

    // Add the introductory text.
    $form['intro_text'] = [
      '#markup' => '<div class="intro-text"><h3>' . $this->t('Enter the phone number associated with your appointment') . '</h3></div>',
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('Enter the phone number used for booking'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Verify'),
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');

    // 1. Check if phone number is empty (though #required should handle this)
    if (empty($phone)) {
      $form_state->setErrorByName('phone', $this->t('Phone number is required.'));
      return;
    }

    // 2. Validate phone number format (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid 10-digit phone number (e.g., 0612345678).'));
      return;
    }

    // 3. Check if appointment exists (only if format is valid)
    $appointment = $this->appointmentStorage->findByPhone($phone);
    if (!$appointment) {
      $form_state->setErrorByName('phone', $this->t('No appointment found with this phone number. Please verify your entry or contact support.'));
    }
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $phone = $form_state->getValue('phone');
    $form_state->setRedirect('appointment.view_appointments', [], [
      'query' => ['phone' => $phone]
    ]);
  }
}
