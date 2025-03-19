<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\appointment\Entity\AgencyEntity;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding and editing Agency entities.
 */
class AgencyForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agency_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $agency = NULL) {
    // If $agency is just an ID, load the entity
    if (is_string($agency) || is_numeric($agency)) {
      $agency = \Drupal::entityTypeManager()->getStorage('agency')->load($agency);
    }

    // Create form elements for the Agency fields.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Agency Name'),
      '#default_value' => ($agency instanceof AgencyEntity) ? $agency->label() : '',
      '#required' => TRUE,
    ];

    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#default_value' => ($agency instanceof AgencyEntity) ? $agency->get('location')->value : '',
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => ($agency instanceof AgencyEntity) ? $agency->get('email')->value : '',
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => ($agency instanceof AgencyEntity) ? $this->t('Update Agency') : $this->t('Add Agency'),
    ];

    // Pass agency ID to form state for reference during submission
    if ($agency instanceof AgencyEntity) {
      $form_state->set('agency_id', $agency->id());
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Check if we're editing or creating a new agency
    $agency_id = $form_state->get('agency_id');

    if ($agency_id) {
      // Load the existing agency entity
      $agency = AgencyEntity::load($agency_id);
    } else {
      // Create a new agency entity
      $agency = AgencyEntity::create();
    }

    // Set the agency fields
    $agency->set('name', $form_state->getValue('name'));
    $agency->set('location', $form_state->getValue('location'));
    $agency->set('email', $form_state->getValue('email'));

    // Save the agency entity
    $agency->save();

    // Provide a success message
    $this->messenger()->addMessage($this->t('Agency has been saved.'));
  }
}
