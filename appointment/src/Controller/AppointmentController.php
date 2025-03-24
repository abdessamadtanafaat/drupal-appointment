<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AppointmentController extends ControllerBase {

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Constructs a new AppointmentController.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStore = $tempStoreFactory->get('appointment');
  }

  /**
   * {@inheritdoc}
   */

  public function getAvailability(Request $request) {

        // Log the AJAX request for debugging.
        \Drupal::logger('appointment')
          ->debug('Received AJAX request for advisor availability.');

        // Get parameters from the request
        $agency_id = $request->query->get('agency_id');
        $appointment_type_id = $request->query->get('appointment_type_id');
        $advisor_id = $request->query->get('advisor_id');

        // Query the database for appointments
        $database = \Drupal::database();
        $query = $database->select('appointment', 'a')
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

        // Add conditions based on the parameters
        if ($agency_id) {
          $query->condition('agency_id', $agency_id);
        }
        if ($appointment_type_id) {
          $query->condition('appointment_type', $appointment_type_id);
        }
        if ($advisor_id) {
          $query->condition('advisor_id', $advisor_id);
        }

        $appointments = $query->execute()->fetchAll();

        // Format the results for FullCalendar
        $events = [];
        foreach ($appointments as $appointment) {
          $events[] = [
            'id' => $appointment->id,
            'title' => $appointment->title ?: ($appointment->first_name . ' ' . $appointment->last_name),
            'start' => $appointment->start_date,
            'end' => $appointment->end_date,
            'status' => $appointment->appointment_status,
            'editable' => false,
            'extendedProps' => [
              'source' => 'server', // flag to market it's comming from the server == to be not editable in JS
              'firstName' => $appointment->first_name,
              'lastName' => $appointment->last_name,
              'email' => $appointment->email,
              'phone' => $appointment->phone
            ]
          ];
        }

        return new JsonResponse($events);
  }
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('tempstore.private')
//    );
//  }

  /**
   * Saves the selected time slot to the tempstore.
   */
  public function saveSelectionTime(Request $request) {
    // Log the incoming request data for debugging.
    \Drupal::logger('appointment')
      ->debug('Incoming request data: ' . $request->getContent());

    // Decode the JSON payload.
    $data = json_decode($request->getContent(), TRUE);

    // Log the decoded data for debugging.
    \Drupal::logger('appointment')
      ->debug('Decoded data: ' . print_r($data, TRUE));

    // Check if the decoded data is valid.
    if (empty($data) || !is_array($data)) {
      \Drupal::logger('appointment')
        ->error('Invalid or empty JSON payload received.');
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Invalid or empty JSON payload.'
      ], 400);
    }

    // Debug: Log the data before saving to tempstore.
    \Drupal::logger('appointment')
      ->debug('Data to be saved to tempstore: ' . print_r($data, TRUE));

    // Retrieve the existing appointment data from tempstore.
    $values = $this->tempStore->get('values') ?? [];

    // Add the selected time slot to values.
    $values['selected_slot'] = [
      'start' => $data['start'],
      'end' => $data['end'],
      'title' => $data['title'],
    ];

    // Add the selected datetime to the values array.
    $values['selected_datetime'] = $data['start'] . ' to ' . $data['end'];

    $values['agency_id'] = $data['agency_id'];
    $values['appointment_type_id'] = $data['appointment_type_id'];
    $values['appointment_type_name'] = $data['appointment_type_name'];
    $values['advisor_id'] = $data['advisor_id'];

    // Save the updated appointment data to tempstore.
    $this->tempStore->set('values', $values);

    // Debug: Log the tempstore value to verify it was saved correctly.
    $tempstoreValue = $this->tempStore->get('values');
    \Drupal::logger('appointment')
      ->debug('Tempstore value after saving: ' . print_r($tempstoreValue, TRUE));

    // Log a success message.
    \Drupal::logger('appointment')
      ->debug('Selection saved to tempstore successfully.');

    // Return a success response.
    return new JsonResponse(['status' => 'success']);
  }

  /**
   * Saves the personal information  to the tempstore.
   */

//  public function savePersonalInformation(Request $request) {
//    // Log the incoming request data for debugging.
//    \Drupal::logger('appointment')
//      ->debug('Incoming request data: ' . $request->getContent());
//
//    // Decode the JSON payload.
//    $data = json_decode($request->getContent(), TRUE);
//
//    // Log the decoded data for debugging.
//    \Drupal::logger('appointment')
//      ->debug('Decoded data: ' . print_r($data, TRUE));
//
//    // Check if the decoded data is valid.
//    if (empty($data) || !is_array($data)) {
//      \Drupal::logger('appointment')
//        ->error('Invalid or empty JSON payload received.');
//      return new JsonResponse([
//        'status' => 'error',
//        'message' => 'Invalid or empty JSON payload.'
//      ], 400);
//    }
//
//    // Retrieve the existing appointment data from tempstore.
//    $appointment_data = $this->tempStore->get('appointment_data') ?? [];
//
//    // Ensure $appointment_data is an array.
//    if (!is_array($appointment_data)) {
//      \Drupal::logger('appointment')
//        ->warning('Tempstore data is not an array. Initializing as empty array.');
//      $appointment_data = [];
//    }
//
//    // Add the personal information to the appointment data.
//    $appointment_data['personal_information'] = [
//      'first_name' => $data['first_name'],
//      'last_name' => $data['last_name'],
//      'phone' => $data['phone'],
//      'email' => $data['email'],
//      'terms' => $data['terms'],
//    ];
//
//    // Save the updated appointment data to tempstore.
//    $this->tempStore->set('appointment_data', $appointment_data);
//
//    // Log the updated tempstore data for debugging.
//    \Drupal::logger('appointment')
//      ->notice('Tempstore value after saving personal information: ' . print_r($appointment_data, TRUE));
//
//    // Return a success response.
//    return new JsonResponse(['status' => 'success']);
//  }

}
