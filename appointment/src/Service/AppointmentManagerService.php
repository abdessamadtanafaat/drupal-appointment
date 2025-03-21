<?php


namespace Drupal\appointment\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

class AppointmentManagerService {

  protected $entityTypeManager;

  protected $database;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, Connection $database) {
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
  }

  /**
   * Get the advisor's working hours.
   */
  public function getWorkingHours($advisor_id) {
    // Log the advisor ID for debugging.
    \Drupal::logger('appointment')->debug('Fetching working hours for advisor ID: ' . $advisor_id);

    // Query the user__field_working_hours table.
    $query = $this->database->select('user__field_working_hours', 'uwh')
      ->fields('uwh', [
        'field_working_hours_day',
        'field_working_hours_starthours',
        'field_working_hours_endhours',
        'field_working_hours_comment'
      ])
      ->condition('uwh.entity_id', $advisor_id)
      ->condition('uwh.deleted', 0)
      ->orderBy('uwh.delta');

    $results = $query->execute()->fetchAll();

    // Log the fetched working hours for debugging.
    \Drupal::logger('appointment')->debug('Working hours for advisor ID ' . $advisor_id . ': ' . print_r($results, TRUE));

    // Format the working hours into a structured array.
    $working_hours = [];
    foreach ($results as $row) {
      $day = $row->field_working_hours_day;
      $start = $row->field_working_hours_starthours;
      $end = $row->field_working_hours_endhours;
      $comment = $row->field_working_hours_comment;

      if (!empty($start) && !empty($end)) {
        $working_hours[$day][] = [
          'start' => $this->formatTime($start),
          'end' => $this->formatTime($end),
          'comment' => $comment,
        ];
      }
    }

    return $working_hours;
  }

  /**
   * Helper function to format time from integer to "H:i" format.
   */
  private function formatTime($time) {
    $hours = intval($time / 100);
    $minutes = $time % 100;
    return sprintf('%02d:%02d', $hours, $minutes);
  }
  /**
   * Get the advisor's existing appointments.
   */
  public function getExistingAppointments($advisor_id) {
    // Log the advisor ID for debugging.
    \Drupal::logger('appointment')
      ->debug('Fetching existing appointments for advisor ID: ' . $advisor_id);

    $query = $this->database->select('appointment', 'a')
      ->fields('a', ['appointment_date'])
      ->condition('a.advisor_id', $advisor_id)
      ->execute();
    $appointments = $query->fetchCol();

    // Log the fetched appointments for debugging.
    \Drupal::logger('appointment')
      ->debug('Existing appointments for advisor ID ' . $advisor_id . ': ' . print_r($appointments, TRUE));

    return $query->fetchCol();
  }


  /**
   * Get events for FullCalendar.
   */
  public function getCalendarEvents($advisor_id) {
    // Log the advisor ID for debugging.
    \Drupal::logger('appointment')->debug('Generating calendar events for advisor ID: ' . $advisor_id);

    $working_hours = $this->getWorkingHours($advisor_id);
    $existing_appointments = $this->getExistingAppointments($advisor_id);

    $events = [];

    // Add existing appointments as "unavailable".
    foreach ($existing_appointments as $appointment) {
      $events[] = [
        'id' => 'unavailable_' . uniqid(), // Unique ID for each event.
        'title' => 'Unavailable',
        'start' => $this->formatDateTime($appointment), // Convert to ISO 8601 format.
        'end' => $this->formatDateTime($appointment, '+1 hour'), // Assume 1-hour appointments.
        'color' => '#ff0000', // Red color for unavailable times.
      ];
    }

    // Add working hours as available slots.
    if (!empty($working_hours)) {
      foreach ($working_hours as $day => $slots) {
        foreach ($slots as $slot) {
          $start_time = $slot['start']; // e.g., "15:00"
          $end_time = $slot['end']; // e.g., "16:00"

          // Generate the start and end dates for the current week.
          $start_date = $this->getDateForDayOfWeek($day, $start_time);
          $end_date = $this->getDateForDayOfWeek($day, $end_time);

          $events[] = [
            'id' => 'available_' . uniqid(), // Unique ID for each event.
            'title' => 'Available',
            'start' => $start_date->format('Y-m-d\TH:i:s\Z'), // ISO 8601 format.
            'end' => $end_date->format('Y-m-d\TH:i:s\Z'), // ISO 8601 format.
            'color' => '#00ff00', // Green color for available times.
          ];
        }
      }
    }

    // Log the generated events for debugging.
    \Drupal::logger('appointment')->debug('Generated calendar events for advisor ID ' . $advisor_id . ': ' . print_r($events, TRUE));

    return $events;
  }

  /**
   * Helper function to get the date for a specific day of the week and time.
   */
  private function getDateForDayOfWeek($day, $time) {
    // Get the current date.
    $date = new \DateTime();

    // Calculate the difference between the current day and the target day.
    $current_day = $date->format('w'); // 0 (Sunday) to 6 (Saturday).
    $target_day = $day; // 0 (Sunday) to 6 (Saturday).

    // Adjust the date to the target day of the week.
    $date->modify('+' . ($target_day - $current_day) . ' days');

    // Set the time.
    $date->setTime(substr($time, 0, 2), substr($time, 3, 2)); // e.g., "15:00" -> 15:00.

    return $date;
  }

  /**
   * Helper function to format date and time to ISO 8601 format.
   */
  private function formatDateTime($date, $modify = '') {
    $date = new \DateTime($date);
    if ($modify) {
      $date->modify($modify);
    }
    return $date->format('Y-m-d\TH:i:s\Z');
  }

}
