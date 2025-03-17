<?php
//
//namespace Drupal\appointment\Form;
//
//use Drupal\Core\Form\FormBase;
//use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Ajax\AjaxResponse;
//use Drupal\Core\Ajax\ReplaceCommand;
//use Drupal\Core\TempStore\PrivateTempStoreFactory;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//
///**
// * Provides the Agency selection form for appointment booking.
// */
//class AgencySelectionForm extends FormBase implements ContainerInjectionInterface {
//
//  /**
//   * The tempstore service.
//   *
//   * @var \Drupal\Core\TempStore\PrivateTempStoreInterface
//   */
//  protected $tempStore;
//
//  /**
//   * Constructs a new AgencySelectionForm object.
//   *
//   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
//   *   The private tempstore factory service.
//   */
//  public function __construct(PrivateTempStoreFactory $temp_store) {
//    $this->tempStore = $temp_store->get('appointment');
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('tempstore.private')
//    );
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getFormId() {
//    return 'agency_selection_form';
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function buildForm(array $form, FormStateInterface $form_state) {
//    // Fake agency data for testing
//    $fake_agencies = [
//      [
//        'id' => 'agency_1',
//        'name' => 'Agency One',
//        'location' => 'New York, NY',
//        'email' => 'contact@agencyone.com',
//      ],
//      [
//        'id' => 'agency_2',
//        'name' => 'Agency Two',
//        'location' => 'Los Angeles, CA',
//        'email' => 'contact@agencytwo.com',
//      ],
//      [
//        'id' => 'agency_3',
//        'name' => 'Agency Three',
//        'location' => 'Chicago, IL',
//        'email' => 'contact@agencythree.com',
//      ],
//    ];
//
//    $agency_cards = [];
//
//    // Loop through the fake agencies and build cards.
//    foreach ($fake_agencies as $agency) {
//      $agency_cards[] = [
//        '#markup' => '<div class="agency-card" onclick="selectAgency(\'' . $agency['id'] . '\')">'
//          . '<h3>' . $agency['name'] . '</h3>' // Agency name
//          . '<p>' . $agency['location'] . '</p>' // Agency location
//          . '<p>' . $agency['email'] . '</p>' // Agency email
//          . '</div>',
//      ];
//    }
//
//    // Attach custom styles for cards
//    $form['#attached']['library'][] = 'appointment/appointment_styles';
//    $form['#attached']['drupalSettings']['agencySelectionUrl'] = \Drupal::url('appointment.agency_selection');
//
//    // Render the agency cards.
//    $form['agency_cards'] = [
//      '#theme' => 'item_list',
//      '#items' => $agency_cards,
//      '#attributes' => ['class' => ['agency-card-list']],
//    ];
//
//    // Container to dynamically load the next step.
//    $form['appointment_types'] = [
//      '#type' => 'container',
//      '#attributes' => ['id' => 'appointment-types-container'],
//    ];
//
//    return $form;
//  }
//
//  /**
//   * AJAX callback to update the next step (appointment types) based on the selected agency.
//   */
//  public function updateAppointmentTypes(array $form, FormStateInterface $form_state) {
//    $selected_agency = $form_state->getValue('agency');
//
//    // Store the selected agency in the session/tempstore.
//    $this->tempStore->set('selected_agency', $selected_agency);
//
//    // Load the next step (e.g., appointment types, filtered by selected agency).
//    $appointment_types = $this->getAppointmentTypes($selected_agency);
//
//    // Return the updated form element (appointment types).
//    $form['appointment_types']['#markup'] = $appointment_types;
//
//    // Return the AJAX response.
//    $response = new AjaxResponse();
//    $response->addCommand(new ReplaceCommand('#appointment-types-container', $form['appointment_types']));
//    return $response;
//  }
//
//  /**
//   * Fetch appointment types based on selected agency.
//   */
//  protected function getAppointmentTypes($agency_id) {
//    // Simulate fetching appointment types filtered by agency (you would implement this part based on your system).
//    return '<p>Appointment Types for Agency: ' . $agency_id . '</p>';
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function submitForm(array &$form, FormStateInterface $form_state) {
//    // Handle the final form submission logic (e.g., store the agency, proceed to the next step).
//  }
//}
