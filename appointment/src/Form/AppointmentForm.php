<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for creating and editing Appointment entities.
 */
class AppointmentForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save the entity.
    $status = parent::save($form, $form_state);

    // Display a message based on the operation.
    if ($status == SAVED_NEW) {
      $this->messenger()->addMessage($this->t('Created the %label appointment.', [
        '%label' => $entity->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('Saved the %label appointment.', [
        '%label' => $entity->label(),
      ]));
    }

    // Redirect to the entity collection page.
    $form_state->setRedirect('entity.appointment.collection');
  }
}
