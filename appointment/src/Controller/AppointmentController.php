<?php

namespace Drupal\appointment\Controller;

use Drupal\appointment\Entity\Appointment;
use Drupal\appointment\Service\AppointmentMailerService;
use Drupal\appointment\Service\AppointmentStorage;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
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
   * The appointment storage service.
   */
  protected AppointmentStorage $appointmentStorage;

  protected $formBuilder;


  /**
   * The appointment mailer service.
   *
   * @var \Drupal\appointment\Service\AppointmentMailerService
   */
  protected $appointmentMailer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('appointment.storage'),
      $container->get('form_builder'),
      $container->get('appointment.mailer'),

    );
  }


  /**
   * Constructs a new AppointmentController.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory,
    AppointmentStorage $appointment_storage,
    FormBuilderInterface $form_builder,
    AppointmentMailerService $appointment_mailer,

  ) {
    $this->tempStore = $tempStoreFactory->get('appointment');
    $this->appointmentStorage = $appointment_storage;
    $this->formBuilder = $form_builder;
    $this->appointmentMailer = $appointment_mailer;
  }

  /**
   * {@inheritdoc}
   */

  public function getAppointments(Request $request) {

    $filters = [
      'agency_id' => $request->query->get('agency_id'),
      'appointment_type_id' => $request->query->get('appointment_type_id'),
      'advisor_id' => $request->query->get('advisor_id')
    ];

    $events = $this->appointmentStorage->getAppointments($filters);
    return new JsonResponse($events);

//        // Query the database for appointments
//        $database = \Drupal::database();
//        $query = $database->select('appointment', 'a')
//          ->fields('a', [
//            'id',
//            'title',
//            'start_date',
//            'end_date',
//            'appointment_status',
//            'first_name',
//            'last_name',
//            'email',
//            'phone'
//          ]);
//
//        // Add conditions based on the parameters
//        if ($agency_id) {
//          $query->condition('agency_id', $agency_id);
//        }
//        if ($appointment_type_id) {
//          $query->condition('appointment_type', $appointment_type_id);
//        }
//        if ($advisor_id) {
//          $query->condition('advisor_id', $advisor_id);
//        }
//
//        $appointments = $query->execute()->fetchAll();
//
//        // Format the results for FullCalendar
//        $events = [];
//        foreach ($appointments as $appointment) {
//          $events[] = [
//            'id' => $appointment->id,
//            'title' => $appointment->title ?: ($appointment->first_name . ' ' . $appointment->last_name),
//            'start' => $appointment->start_date,
//            'end' => $appointment->end_date,
//            'status' => $appointment->appointment_status,
//            'editable' => false,
//            'extendedProps' => [
//              'source' => 'server', // flag to market it's comming from the server == to be not editable in JS
//              'firstName' => $appointment->first_name,
//              'lastName' => $appointment->last_name,
//              'email' => $appointment->email,
//              'phone' => $appointment->phone
//            ]
//          ];
//        }
//
//        return new JsonResponse($events);
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
   * Returns working hours for the specified agency.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response containing working hours.
   */
  public function getWorkingHoursAgency(Request $request) {
    // Get agency_id from request parameters
    $agency_id = $request->query->get('agency_id');

    // Validate agency_id
    if (empty($agency_id)) {
      \Drupal::logger('appointment')->error('Missing agency_id parameter in getWorkingHoursAgency');
      return new JsonResponse([
        'status' => 'error',
        'message' => 'agency_id parameter is required'
      ], 400);
    }

    try {
      // Load the agency entity
      $agency = \Drupal::entityTypeManager()
        ->getStorage('appointment_agency')
        ->load($agency_id);

      if (!$agency) {
        \Drupal::logger('appointment')->error('Agency not found with ID: @id', ['@id' => $agency_id]);
        return new JsonResponse([
          'status' => 'error',
          'message' => 'Agency not found'
        ], 404);
      }

      // Get working hours field values
      $working_hours = [];
      foreach ($agency->get('working_hours') as $item) {
        if ($item->day !== NULL && $item->starthours !== NULL && $item->endhours !== NULL) {
          $working_hours[] = [
            'day' => (int)$item->day,
            'starthours' => (int)$item->starthours,
            'endhours' => (int)$item->endhours,
            'comment' => $item->comment ?? ''
          ];
        }
      }

      // Return structured response
      return new JsonResponse([
        'status' => 'success',
        'data' => [
          'agency_name' => $agency->label(),
          'working_hours' => $working_hours
        ]
      ]);

    } catch (\Exception $e) {
      \Drupal::logger('appointment')->error('Error fetching working hours: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse([
        'status' => 'error',
        'message' => 'An error occurred while fetching working hours'
      ], 500);
    }
  }

//  public function loadVerificationForm() {
//    $form = [
//      '#theme' => 'phone_verification_form',
//    ];
//
//    return new JsonResponse([
//      'form' => \Drupal::service('renderer')->render($form)
//    ]);
//  }
//
//  public function verifyPhone(Request $request) {
//    $phone = $request->request->get('phone');
//    $storage = \Drupal::service('appointment.storage');
//    $appointment = $storage->findByPhone($phone);
//
//    if ($appointment) {
//      return new JsonResponse([
//        'success' => true,
//        'message' => $this->t('Phone verified successfully')
//      ]);
//    }
//
//    return new JsonResponse([
//      'success' => false,
//      'message' => $this->t('No appointment found with this phone number')
//    ], 400);
//  }


  public function delete($id) {
    try {
      $appointment = Appointment::load($id);

      if (!$appointment) {
        $this->messenger()->addError($this->t('Appointment not found.'));
        return $this->redirect('appointment.view_appointments');
      }

      // Get appointment data for email
      $appointment_data = [
        'email' => $appointment->get('email')->value,
        'start_date' => $appointment->get('start_date')->value,
        'end_date' => $appointment->get('end_date')->value,
        'advisor' => $appointment->get('advisor')->value,
        'agency' => $appointment->get('agency')->value,
        'appointment_type_name' => $appointment->get('appointment_type_name')->value,
        'first_name' => $appointment->get('first_name')->value,
        'last_name' => $appointment->get('last_name')->value,
      ];

      // Soft delete the appointment
      $this->appointmentStorage->softDelete($id);

      // Update advisor availability
//      \Drupal::service('appointment.storage')->updateAdvisorAvailability(
//        $appointment->get('advisor_id')->value,
//        $appointment->get('start_date')->value,
//        $appointment->get('end_date')->value
//      );

      // Send cancellation email
      $email_sent = $this->appointmentMailer->sendCancellationEmail($appointment_data, $id);

      if (!$email_sent) {
        \Drupal::logger('appointment')->error('Failed to send cancellation email for appointment ID: @id', ['@id' => $id]);
        // You might want to decide whether to continue with deletion if email fails
      }

      // Soft delete the appointment
      $this->appointmentStorage->softDelete($id);

      $this->messenger()->addStatus($this->t('Appointment cancelled successfully.'));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('Failed to cancel appointment.'));
      \Drupal::logger('appointment')->error($e->getMessage());
    }

    return $this->redirect('appointment.view_appointments');
  }




}
