<?php

namespace Drupal\appointment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\appointment\Entity\AppointmentEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Appointment entity.
 *
 * @ContentEntityType(
 *   id = "appointment",
 *   label = @Translation("Appointment"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\appointment\AppointmentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AppointmentForm",
 *       "edit" = "Drupal\appointment\Form\AppointmentForm",
 *       "delete" = "Drupal\appointment\Form\AppointmentDeleteForm"
 *     }
 *   },
 *   base_table = "appointment",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "name",
 *     "user" = "user_id"
 *   },
 *   links = {
 *     "canonical" = "/appointment/{appointment}",
 *     "edit-form" = "/appointment/{appointment}/edit",
 *     "delete-form" = "/appointment/{appointment}/delete"
 *   }
 * )
 */
class AppointmentEntity extends ContentEntityBase implements AppointmentEntityInterface {
  use EntityChangedTrait;


  /**
   * Gets the appointment name.
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Sets the appointment name.
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Gets the appointment date.
   */
  public function getDate() {
    return $this->get('appointment_date')->value;
  }

  /**
   * Sets the appointment date.
   */
  public function setDate($date) {
    $this->set('appointment_date', $date);
    return $this;
  }

  /**
   * Gets the appointment status.
   */
  public function getStatus() {
    return $this->get('status')->value;
  }

  /**
   * Sets the appointment status.
   */
  public function setStatus($status) {
    $this->set('status', $status);
    return $this;
  }

  /**
   * Gets the user who booked the appointment.
   */
  public function getUser() {
    return $this->get('user_id')->entity;
  }

  /**
   * Sets the user for the appointment.
   */
  public function setUser(UserInterface $user) {
    $this->set('user_id', $user->id());
    return $this;
  }
}
