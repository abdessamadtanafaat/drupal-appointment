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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

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


    $values['agency_id'] = $data['agency_id'];
    $values['appointment_type_id'] = $data['appointment_type_id'];
    $values['appointment_type_name'] = $data['appointment_type_name'];
    $values['advisor_id'] = $data['advisor_id'];

    // Save the updated appointment data to tempstore.
    $this->tempStore->set('values', $values);

    // Debug: Log the tempstore value to verify it was saved correctly.
    $tempstoreValue = $this->tempStore->get('values');
    \Drupal::logger('appointment')
      ->debug('Tempstore value after saving: ' . print_r($values, TRUE));

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
