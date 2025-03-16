<?php

namespace Drupal\appointment\Entity;

use Drupal\user\Entity\User;

/**
 * Defines the Adviser entity (extending User).
 */
class AdviserEntity extends User {
  /**
   * The associated Agency ID.
   *
   * @var int
   */
  protected $agency_id;

  /**
   * The adviser's working hours.
   *
   * @var array
   */
  protected $working_hours;

  /**
   * Gets the Agency ID.
   */
  public function getAgencyId() {
    return $this->agency_id;
  }

  /**
   * Sets the Agency ID.
   */
  public function setAgencyId($agency_id) {
    $this->agency_id = $agency_id;
    return $this;
  }

  /**
   * Gets working hours.
   */
  public function getWorkingHours() {
    return $this->working_hours;
  }

  /**
   * Sets working hours.
   */
  public function setWorkingHours(array $hours) {
    $this->working_hours = $hours;
    return $this;
  }
}
