<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Multi-step Booking Form.
 */
class BookingForm extends FormBase {

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
    // Get the agencies.
    $form['#attached']['library'][] = 'appointment/appointment_styles';
    $agencies = $this->getAgencies();

    // Create a wrapper for the agency cards.
    $form['agency_cards'] = [
      '#markup' => '<div class="agency-card-list"></div>',
    ];

    // Loop through the agencies and create a card for each one.
    $agency_cards_markup = '';
    foreach ($agencies as $id => $name) {
      $agency_cards_markup .= '<div class="agency-card">';
      $agency_cards_markup .= '<h3>' . $name . '</h3>';
      $agency_cards_markup .= '<p>' . $this->t('Description for ') . $name . '</p>';
      $agency_cards_markup .= '<button class="select-agency-button" data-agency-id="' . $id . '">' . $this->t('Select') . '</button>';
      $agency_cards_markup .= '</div>';
    }

    // Add the agency cards to the form.
    $form['agency_cards']['#markup'] = $agency_cards_markup;

    // Add a hidden field to store the selected agency.
    $form['agency_selected'] = [
      '#type' => 'hidden',
      '#id' => 'agency-selected',
    ];

    // Add a next step button.
    $form['actions']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
    ];

    return $form;
  }



  /**
   * Step 2: Select Appointment Type.
   */
  public function step2($form, FormStateInterface $form_state) {
    $form['appointment_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Appointment Type'),
      '#options' => $this->getAppointmentTypes(),
    ];

    $form['actions']['prev'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
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

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * Retrieves available agencies.
   *
   * @return array
   *   An associative array of agency IDs and names.
   */
  protected function getAgencies(): array {
    $agencies = \Drupal::entityTypeManager()->getStorage('agency')->loadMultiple();
    $options = [];

    foreach ($agencies as $agency) {
      $options[$agency->id()] = $agency->label();
    }

    return $options;
  }



}
