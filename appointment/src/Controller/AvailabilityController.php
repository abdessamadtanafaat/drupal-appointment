<?php
//namespace Drupal\appointment\Controller;
//
//use Drupal\Core\Controller\ControllerBase;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\Request;
//
//class AvailabilityController extends ControllerBase {
//
//  public function getAvailability(Request $request) {
//    // Log the AJAX request for debugging.
//    \Drupal::logger('appointment')->debug('Received AJAX request for advisor availability.');
//
//
//    $advisor_id = $request->query->get('advisor_id');
//    $appointment_manager = \Drupal::service('appointment.manager');
//
//    // Log the advisor ID for debugging.
//    \Drupal::logger('appointment')->debug('Fetching availability for advisor ID: ' . $advisor_id);
//
//    // Fetch events for FullCalendar.
//    $events = $appointment_manager->getCalendarEvents($advisor_id);
//
//    // Log the events being returned for debugging.
//    \Drupal::logger('appointment')->debug('Returning events for advisor ID ' . $advisor_id . ': ' . print_r($events, TRUE));
//
//    return new JsonResponse(['events' => $events]);
//  }
//}
