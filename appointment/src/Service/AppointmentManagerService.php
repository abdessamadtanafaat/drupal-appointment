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
        'title' => 'Unavailable',
        'start' => $appointment,
        'color' => '#ff0000', // Red color for unavailable times.
      ];
    }

    // Add working hours as available slots.
    if (!empty($working_hours)) {
      foreach ($working_hours as $day => $slots) {
        foreach ($slots as $slot) {
          $events[] = [
            'title' => 'Available',
            'start' => $this->getDayOfWeek($day) . 'T' . $slot['start'],
            'end' => $this->getDayOfWeek($day) . 'T' . $slot['end'],
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
   * Helper function to map day number to FullCalendar day of the week.
   */
  private function getDayOfWeek($day) {
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return $days[$day] ?? 'Sunday';
  }

  /**
   * Helper function to convert time values (e.g., "1500") to a valid date-time format.
   */
  private function formatTimeToDatetime($time) {
    if (empty($time)) {
      return null;
    }

    // Convert time value (e.g., "1500") to "HH:MM" format.
    $time_str = substr($time, 0, 2) . ':' . substr($time, 2, 2);

    // Use the current date for demonstration purposes.
    // Replace this with the actual date if needed.
    $date = date('Y-m-d'); // Today's date.
    return $date . 'T' . $time_str . ':00'; // ISO 8601 format.
  }
}
