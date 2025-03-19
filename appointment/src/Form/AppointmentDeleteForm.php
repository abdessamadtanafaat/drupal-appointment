<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting an appointment entity.
 */
class AppointmentDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the appointment %name?', [
      '%name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.appointment.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $entity->delete();

    $this->messenger()->addMessage($this->t('The appointment %name has been deleted.', [
      '%name' => $entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
