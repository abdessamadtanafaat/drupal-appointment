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
  public function saveSelection(Request $request) {
    // Log the incoming request data for debugging.
    \Drupal::logger('appointment')->debug('Incoming request data: ' . $request->getContent());

    // Decode the JSON payload.
    $data = json_decode($request->getContent(), TRUE);

    // Log the decoded data for debugging.
    \Drupal::logger('appointment')->debug('Decoded data: ' . print_r($data, TRUE));

    // Check if the decoded data is valid.
    if (empty($data) || !is_array($data)) {
      \Drupal::logger('appointment')->error('Invalid or empty JSON payload received.');
      return new JsonResponse(['status' => 'error', 'message' => 'Invalid or empty JSON payload.'], 400);
    }

//    // Validate the required fields.
//    if (empty($data['advisor_id']) || empty($data['start']) || empty($data['end']) || empty($data['title'])) {
//      \Drupal::logger('appointment')->error('Invalid data received: Missing required fields.');
//      return new JsonResponse(['status' => 'error', 'message' => 'Invalid data: Missing required fields.'], 400);
//    }

    // Debug: Log the data before saving to tempstore.
    \Drupal::logger('appointment')->debug('Data to be saved to tempstore: ' . print_r($data, TRUE));

    // Save the selected time slot to the tempstore.
    $this->tempStore->set('selected_slot', [
//      'advisor_id' => $data['advisor_id'],
      'start' => $data['start'],
      'end' => $data['end'],
      'title' => $data['title'],
    ]);

    // Debug: Log the tempstore value to verify it was saved correctly.
    $tempstoreValue = $this->tempStore->get('selected_slot');
    \Drupal::logger('appointment')->debug('Tempstore value after saving: ' . print_r($tempstoreValue, TRUE));

    // Log a success message.
    \Drupal::logger('appointment')->debug('Selection saved to tempstore successfully.');

    // Return a success response.
    return new JsonResponse(['status' => 'success']);
  }}
