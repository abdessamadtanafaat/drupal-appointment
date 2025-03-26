<?php

namespace Drupal\appointment\Service;

use Drupal\appointment\Entity\Appointment;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles appointment database operations.
 */
class AppointmentStorage {

  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  protected $entityTypeManager;


  /**
   * Constructs a new AppointmentStorage.
   */
  public function __construct(
    Connection $database,
    UuidInterface $uuid_service,
    DateFormatterInterface $date_formatter,
    AccountProxyInterface $current_user,
    LoggerChannelFactoryInterface $logger_factory,
    EntityTypeManagerInterface $entityTypeManager,

  ) {
    $this->database = $database;
    $this->uuidService = $uuid_service;
    $this->dateFormatter = $date_formatter;
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('appointment');
    $this->entityTypeManager = $entityTypeManager;

  }

  /**
   * Saves an appointment to the database.
   */
  public function saveAppointment(array $values): ?int {
    try {
      $fields = $this->prepareAppointmentFields($values);
      $appointment_id = $this->database->insert('appointment')
        ->fields($fields)
        ->execute();

      $this->logger->notice('Appointment saved successfully', [
        'appointment_id' => $appointment_id,
        'user_email' => $fields['email'] ?? 'unknown'
      ]);

      return $appointment_id;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to save appointment', [
        'error' => $e->getMessage(),
        'values' => $values,
        'trace' => $e->getTraceAsString()
      ]);
      return NULL;
    }
  }

  /**
   * Prepares appointment fields for database storage.
   */
  public function prepareAppointmentFields(array $values): array {
    $uuid = $this->uuidService->generate();
    $uid = $this->currentUser->id();
    $start_date = $values['selected_slot']['start'] ?? '';
    $end_date = $values['selected_slot']['end'] ?? '';

    return [
      'uuid' => $uuid,
      'agency_id' => $values['agency_id'] ?? NULL,
      'agency' => $this->getAgencyName($values['agency_id'] ?? NULL),
      'appointment_type' => $values['appointment_type_id'] ?? NULL,
      'appointment_type_name' => $this->getAppointmentTypeName($values['appointment_type_id'] ?? NULL),
      'description' => $this->generateDescription($start_date, $end_date),
      'advisor_id' => $values['advisor_id'] ?? NULL,
      'advisor' => $this->getAdvisorName($values['advisor_id'] ?? NULL),
      'start_date' => $start_date,
      'end_date' => $end_date,
      'first_name' => $values['first_name'] ?? NULL,
      'last_name' => $values['last_name'] ?? NULL,
      'email' => $values['email'] ?? NULL,
      'phone' => $values['phone'] ?? NULL,
      'appointment_status' => 'pending',
      'label' => $values['selected_slot']['title'] ?? NULL,
      'status' => 1,
      'uid' => $uid,
      'created' => \Drupal::time()->getRequestTime(),
      'changed' => \Drupal::time()->getRequestTime(),
    ];
  }

  /**
   * Gets advisor name by ID.
   */
  public function getAdvisorName(?int $advisor_id): string {
    if (!$advisor_id) {
      $this->logger->error('Advisor ID is not set.');
      return '';
    }

    $name = $this->database->select('users_field_data', 'u')
      ->fields('u', ['name'])
      ->condition('u.uid', $advisor_id)
      ->execute()
      ->fetchField();

    if ($name) {
      $this->logger->notice('Loaded advisor name: @name', ['@name' => $name]);
      return $name;
    }

    $this->logger->error('Failed to load advisor with ID: @id', ['@id' => $advisor_id]);
    return '';
  }

  /**
   * Gets agency name by ID.
   */
  public function getAgencyName(?int $agency_id): string {
    if (!$agency_id) {
      $this->logger->error('Agency ID is not set.');
      return '';
    }

    $name = $this->database->select('appointment_agency', 'a')
      ->fields('a', ['name'])
      ->condition('a.id', $agency_id)
      ->execute()
      ->fetchField();

    if ($name) {
      $this->logger->notice('Loaded agency name: @name', ['@name' => $name]);
      return $name;
    }

    $this->logger->error('Failed to load agency with ID: @id', ['@id' => $agency_id]);
    return '';
  }

  /**
   * Gets appointment type name by ID.
   */
  public function getAppointmentTypeName(?int $type_id): string {
    if (!$type_id) {
      $this->logger->error('Appointment Type ID is not set.');
      return '';
    }

    $name = $this->database->select('taxonomy_term_field_data', 't')
      ->fields('t', ['name'])
      ->condition('t.tid', $type_id)
      ->execute()
      ->fetchField();

    if ($name) {
      $this->logger->notice('Loaded appointment type: @name', ['@name' => $name]);
      return $name;
    }

    $this->logger->error('Failed to load appointment type with ID: @id', ['@id' => $type_id]);
    return '';
  }

  /**
   * Generates appointment description.
   */
  public function generateDescription(string $start_date, string $end_date): string {
    if (!$start_date || !$end_date) {
      return '';
    }

    $start_formatted = $this->dateFormatter->format(strtotime($start_date), 'custom', 'Y-m-d');
    $start_time = $this->dateFormatter->format(strtotime($start_date), 'custom', 'H:i');
    $end_time = $this->dateFormatter->format(strtotime($end_date), 'custom', 'H:i');

    return $this->t('Appointment - @date - @start_time to @end_time', [
      '@date' => $start_formatted,
      '@start_time' => $start_time,
      '@end_time' => $end_time,
    ])->render();
  }

  /**
   * Retrieves available agencies.
   *
   * @return array
   *   An associative array of agency IDs and names.
   */
  public function getAgencies(): array {
    // Query for agency entities.
    $agency_storage = \Drupal::entityTypeManager()
      ->getStorage('appointment_agency');
    return $agency_storage->loadMultiple();
  }

  /**
   * Fetches appointment types from the 'appointment_types' taxonomy vocabulary.
   *
   * @return array
   *   An associative array of appointment types keyed by term ID.
   */
  public function getAppointmentTypes() {
    $appointmentTypes = [];

    // Load terms from the 'appointment_types' vocabulary.
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('appointment_types');

    // Build an options array from the terms.
    foreach ($terms as $term) {
      $appointmentTypes[$term->tid] = $term->name;
      $appointmentTypes[$term->tid] = $term->name;
    }

    return $appointmentTypes;
  }

  /**
   * Retrieves the list of advisors.
   *
   * @return array
   *   An associative array of advisor IDs and names.
   */
  public function getAdvisors(): array {
    $advisors = [];

    // Load the user storage service.
    $user_storage = \Drupal::entityTypeManager()->getStorage('user');

    // Query users with a specific role (e.g., 'advisor').
    $query = $user_storage->getQuery()
      ->condition('status', 1) // Only active users.
      ->condition('roles', 'advisor') // Replace 'advisor' with the correct role machine name.
      ->sort('name', 'ASC') // Sort by name.
      ->accessCheck(TRUE); // Explicitly enable access checking.

    // Execute the query and get the user IDs.
    $uids = $query->execute();

    if (!empty($uids)) {
      // Load the user entities.
      $users = $user_storage->loadMultiple($uids);

      // Build the list of advisors.
      foreach ($users as $user) {
        $advisors[$user->id()] = $user; // Store the full user object.
      }
    }

    return $advisors;
  }

  /**
   * Gets appointments filtered by parameters.
   */
  public function getAppointments(array $filters = []): array {
    $query = $this->database->select('appointment', 'a')
      ->fields('a', [
        'id',
        'title',
        'start_date',
        'end_date',
        'appointment_status',
        'first_name',
        'last_name',
        'email',
        'phone'
      ]);


    // Add conditions based on filters
    if (!empty($filters['agency_id'])) {
      $query->condition('agency_id', $filters['agency_id']);
    }
    if (!empty($filters['appointment_type_id'])) {
      $query->condition('appointment_type', $filters['appointment_type_id']);
    }
    if (!empty($filters['advisor_id'])) {
      $query->condition('advisor_id', $filters['advisor_id']);
    }

    $results = $query->execute()->fetchAll();

    return $this->formatAppointmentsForCalendar($results);
  }

  /**
   * Formats appointments for FullCalendar.
   */
  protected function formatAppointmentsForCalendar(array $appointments): array {
    $events = [];

    foreach ($appointments as $appointment) {
      $events[] = [
        'start' => $appointment->start_date,
        'end' => $appointment->end_date,
        'status' => $appointment->appointment_status,
        'editable' => FALSE,
        'extendedProps' => [
          'source' => 'server',
        ]
      ];
    }

    return $events;
  }

  public function findByPhone(string $phone): ?Appointment {
    $query = $this->database->select('appointment', 'a')
      ->fields('a')
      ->condition('phone', $phone)
      ->orderBy('created', 'DESC')
      ->range(0, 1);

    $result = $query->execute()->fetchAssoc();

    if ($result) {
      return $this->entityTypeManager
        ->getStorage('appointment')
        ->load($result['id']);
    }

    return NULL;
  }
  public function findAllByPhone(string $phone, string $status_filter = '1'): array {
    $query = $this->database->select('appointment', 'a')
      ->fields('a')
      ->condition('phone', $phone)
      ->orderBy('start_date', 'DESC');

    // Apply status filter
    if ($status_filter !== 'all') {
      $query->condition('status', (int)$status_filter);
    }

    $results = $query->execute()->fetchAllAssoc('id');
    $appointments = [];

    foreach ($results as $id => $result) {
      $appointments[$id] = $this->entityTypeManager
        ->getStorage('appointment')
        ->load($id);
    }

    return $appointments;
  }


  /**
   * Soft deletes an appointment.
   */
  public function softDelete(int $appointment_id): bool {
    try {
      $this->database->update('appointment')
        ->fields([
          'appointment_status' => 'cancelled',
          'status' => 0, // 0 for inactive/deleted
          'changed' => \Drupal::time()->getRequestTime(),
        ])
        ->condition('id', $appointment_id)
        ->execute();

      return true;
    } catch (\Exception $e) {
      $this->logger->error('Failed to soft delete appointment: @error', [
        '@error' => $e->getMessage()
      ]);
      return false;
    }
  }

  /**
   * Checks if a time slot conflicts with existing appointments.
   *
   * @param string $start_date
   *   The start date in 'Y-m-d\TH:i:s' format.
   * @param string $end_date
   *   The end date in 'Y-m-d\TH:i:s' format.
   * @param int $exclude_id
   *   The appointment ID to exclude from the check.
   *
   * @return array
   *   Array of conflicting appointments.
   */
  public function checkTimeConflict($start_date, $end_date, $exclude_id = NULL) {
    $query = $this->database->select('appointment', 'a')
      ->fields('a', ['id', 'start_date', 'end_date'])
      ->condition('a.start_date', $end_date, '<')
      ->condition('a.end_date', $start_date, '>')
      ->condition('a.status', 1); // Only check active appointments

    if ($exclude_id) {
      $query->condition('a.id', $exclude_id, '<>');
    }

    return $query->execute()->fetchAll();
  }

}
