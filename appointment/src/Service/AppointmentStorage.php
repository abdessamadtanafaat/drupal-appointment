<?php

namespace Drupal\appointment\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Session\AccountProxyInterface;

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

  /**
   * Constructs a new AppointmentStorage.
   */
  public function __construct(
    Connection $database,
    UuidInterface $uuid_service,
    DateFormatterInterface $date_formatter,
    AccountProxyInterface $current_user,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->database = $database;
    $this->uuidService = $uuid_service;
    $this->dateFormatter = $date_formatter;
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('appointment');
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
  protected function getAdvisorName(?int $advisor_id): string {
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
  protected function getAgencyName(?int $agency_id): string {
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
  protected function getAppointmentTypeName(?int $type_id): string {
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
  protected function generateDescription(string $start_date, string $end_date): string {
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
    $agency_storage = \Drupal::entityTypeManager()->getStorage('appointment_agency');
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


}
