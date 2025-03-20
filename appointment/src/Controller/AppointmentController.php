<?php
//
//namespace Drupal\appointment\Controller;
//
//use Drupal\appointment\Entity\AgencyEntity;
//use Drupal\appointment\Form\AgencySelectionForm;
//use Drupal\Core\Controller\ControllerBase;
//use Drupal\Core\TempStore\PrivateTempStoreFactory;
//use Drupal\node\Entity\Node;
//use http\Client\Response;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\appointment\Entity\Appointment;
//use Symfony\Component\HttpFoundation\JsonResponse;
//
//// Make sure your custom entity namespace is correct
//
//class AppointmentController extends ControllerBase {
//
//
//  /**
//   * The tempstore service.
//   *
//   * @var \Drupal\Core\TempStore\PrivateTempStoreInterface
//   */
//  protected $tempStore;
//
//  /**
//   * Constructs a new AgencyController object.
//   *
//   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
//   *   The private tempstore factory service.
//   */
//  public function __construct(PrivateTempStoreFactory $temp_store) {
//    $this->tempStore = $temp_store->get('appointment');
//  }
//
//  /**
//   * List function for appointments.
//   */
//  public function list() {
//    // You can add logic here to retrieve and render the appointments list.
//    return [
//      '#markup' => $this->t('This is where the list of appointments will appear.'),
//    ];
//  }
//
//  /**
//   * Displays the agency selection form.
//   */
//  public function chooseAgency() {
//    // Get the agency ID from the AJAX request
//    $agency_id = \Drupal::request()->request->get('agency_id');
//
//    // Store the agency ID in the tempstore
//    $this->tempStore->set('selected_agency', $agency_id);
//
//    // Return a JSON response indicating success
//    return new JsonResponse(['status' => 'success']);
//  }
//  /**
//   * Handle the AJAX request to store the selected agency.
//   */
//  public function selectAgency() {
//    // Get the agency ID from the AJAX request
//    $agency_id = \Drupal::request()->request->get('agency_id');
//
//    // Store the agency ID in the tempstore
//    $this->tempStore->set('selected_agency', $agency_id);
//
//    // Optionally, send a response if needed
//    return new \Drupal\Core\Ajax\AjaxResponse();
//  }
//
//  /**
//   * Create a new appointment.
//   */
//  public function createAppointment() {
//    // Create a new appointment entity
//    $storage = \Drupal::entityTypeManager()->getStorage('appointment');
//
//    $appointment = $storage->create([
//      'uuid' => \Drupal::service('uuid')->generate(),
//      'name' => ('Test Appointment'), // Provide name for the appointment
//      'appointment_date' => strtotime('2024-08-20'), // Appointment date as a Unix timestamp
//      'status' => 'scheduled', // Appointment status (could be scheduled, completed, etc.)
//      'user_id' => 1, // User ID for the appointment (you can change it as needed)
//    ]);
//
//    // Save the appointment entity
//    $appointment->save();
//
//    // Return a success message
//    return [
//      '#markup' => $this->t('Appointment created successfully.'),
//    ];
//  }
//
//  /**
//   * Lists all appointments for admin.
//   */
//  public function adminList() {
//    $query = \Drupal::entityQuery('node')
//      ->condition('type', 'appointment')
//      ->execute();
//
//    $appointments = Node::loadMultiple($query);
//
//    $output = "<h2>All Appointments</h2><ul>";
//    foreach ($appointments as $appointment) {
//      $output .= "<li>" . $appointment->label() . "</li>";
//    }
//    $output .= "</ul>";
//
//    return new Response($output);
//  }
//
//  /**
//   * Lists the current user's appointments.
//   */
//  public function userList() {
//    $uid = \Drupal::currentUser()->id();
//
//    $query = \Drupal::entityQuery('node')
//      ->condition('type', 'appointment')
//      ->condition('uid', $uid)
//      ->execute();
//
//    $appointments = Node::loadMultiple($query);
//
//    $output = "<h2>My Appointments</h2><ul>";
//    foreach ($appointments as $appointment) {
//      $output .= "<li>" . $appointment->label() . "</li>";
//    }
//    $output .= "</ul>";
//
//    return new Response($output);
//  }
//
//  /**
//   * Returns a page with all agencies.
//   */
//  /**
//   * Returns a page with all agencies.
//   */
//  public function agenciesPage() {
//    $agencies = AgencyEntity::loadMultiple();
//    $output = '';
//
//    foreach ($agencies as $agency) {
//      $output .= '<p>' . $agency->label() . ' - ' . $agency->location . ' - ' . $agency->email . '</p>';
//    }
//
//    return [
//      '#markup' => $output,
//    ];
//  }
//}
