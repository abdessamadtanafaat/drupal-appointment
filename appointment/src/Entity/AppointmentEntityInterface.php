<?php

namespace Drupal\appointment\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining Appointment entities.
 */
interface AppointmentEntityInterface extends ContentEntityInterface {

  /**
   * Gets the appointment name.
   */
  public function getName();

  /**
   * Sets the appointment name.
   */
  public function setName($name);

  /**
   * Gets the appointment date.
   */
  public function getDate();

  /**
   * Sets the appointment date.
   */
  public function setDate($date);

  /**
   * Gets the appointment status.
   */
  public function getStatus();

  /**
   * Sets the appointment status.
   */
  public function setStatus($status);

  /**
   * Gets the user who booked the appointment.
   */
  public function getUser();

  /**
   * Sets the user for the appointment.
   */
  public function setUser(UserInterface $user);
}
