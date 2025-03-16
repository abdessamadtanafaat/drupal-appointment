<?php

namespace Drupal\appointment\Service;

use Drupal\appointment\Entity\AppointmentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Uuid\UuidInterface;

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
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Constructs a new AppointmentManagerService.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   The UUID service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UuidInterface $uuidService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->uuidService = $uuidService;

  }

  /**
   * Creates a new appointment.
   */
  public function createAppointment($name, $date, $status, $user_id) {

    // Generate a UUID if not provided
    $uuid = $this->uuidService->generate();  // Using the UUID service to generate the UUID

    $appointment = $this->entityTypeManager->getStorage('appointment')->create([
      'uuid' => $uuid,  // Manually set the UUID
      'name' => $name,
      'appointment_date' => $date,
      'status' => $status,
      'user_id' => $user_id,
    ]);
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
