<?php

namespace Drupal\appointment\Service;

use Drupal\appointment\Entity\AppointmentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Handles appointment operations.
 */
class AppointmentManagerService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AppointmentManagerService.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;

  }

  /**
   * Creates a new appointment.
   */
  public function createAppointment(array $data) : EntityInterface
  {
    $appointment = $this->entityTypeManager
      ->getStorage('appointment')
      ->create($data);
    $appointment->save();
    return $appointment;
  }

  /**
   * Loads an appointment by ID.
   */
  public function loadAppointment($id) {
    return $this->entityTypeManager->getStorage('appointment')->load($id);
  }

  /**
   * Updates an appointment.
   */
  public function updateAppointment(AppointmentEntityInterface $appointment, $name, $date, $status) {
    $appointment->setName($name);
    $appointment->setDate($date);
    $appointment->setStatus($status);
    $appointment->save();
    return $appointment;
  }

  /**
   * Deletes an appointment.
   */
  public function deleteAppointment(AppointmentEntityInterface $appointment) {
    $appointment->delete();
  }
}
