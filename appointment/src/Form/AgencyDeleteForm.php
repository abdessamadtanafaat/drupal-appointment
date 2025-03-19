<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting (archiving) an Agency entity.
 */
class AgencyDeleteForm extends EntityDeleteForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an AgencyDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to archive the agency %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.agency.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Ensure the entity is correctly loaded before modifying it.
    if ($this->entity) {
      // Archive the agency instead of deleting it.
      $this->entity->set('status', 0); // Assuming 0 = Archived, 1 = Active
      $this->entity->save();

      $this->messenger()->addMessage($this->t('The agency %name has been archived.', ['%name' => $this->entity->label()]));
    } else {
      $this->messenger()->addError($this->t('Failed to archive the agency. Entity not found.'));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
