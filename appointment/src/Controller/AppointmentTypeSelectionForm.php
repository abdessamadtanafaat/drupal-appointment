<?php
//namespace Drupal\appointment\Form;
//
//use Drupal\Core\Form\FormBase;
//use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\TempStore\PrivateTempStoreFactory;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
//
//class AppointmentTypeSelectionForm extends FormBase implements ContainerInjectionInterface {
//
//  protected $tempStore;
//
//  public function __construct(PrivateTempStoreFactory $temp_store) {
//    $this->tempStore = $temp_store->get('appointment');
//  }
//
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('tempstore.private')
//    );
//  }
//
//  public function getFormId() {
//    return 'appointment_type_selection_form';
//  }
//
//  public function buildForm(array $form, FormStateInterface $form_state) {
//    // Fetch selected agency from session.
//    $selected_agency = $this->tempStore->get('selected_agency');
//
//    // Display the appointment types for the selected agency.
//    $form['appointment_types'] = [
//      '#markup' => '<h3>Select Appointment Type for ' . $selected_agency . '</h3>',
//    ];
//
//    // You can add appointment types and logic here.
//    // Simulate appointment types as options.
//    $form['appointment_types']['#markup'] .= '<ul>
//      <li>Consultation</li>
//      <li>Follow-up</li>
//      <li>New Patient</li>
//    </ul>';
//
//    return $form;
//  }
//
//  public function submitForm(array &$form, FormStateInterface $form_state) {
//    // Handle the appointment type selection.
//  }
//}
