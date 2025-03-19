<?php
//
//namespace Drupal\appointment\Controller;
//
//use Drupal\Core\Controller\ControllerBase;
//use Drupal\appointment\Entity\AgencyEntity;
//use Drupal\Core\Render\RendererInterface;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//
///**
// * Controller for displaying the agency list.
// */
//class AgencyController extends ControllerBase {
//
//  /**
//   * @var \Drupal\Core\Render\RendererInterface
//   */
//  protected $renderer;
//
//  /**
//   * Constructs a new AgencyController object.
//   *
//   * @param \Drupal\Core\Render\RendererInterface $renderer
//   */
//  public function __construct(RendererInterface $renderer) {
//    $this->renderer = $renderer;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function create(ContainerInterface $container) {
//    return new static(
//      $container->get('renderer')
//    );
//  }
//
//  /**
//   * Displays the list of agencies.
//   *
//   * @return array
//   *   A render array representing the agency list.
//   */
//  public function agencyList() {
//    // Use the AgencyListBuilder to fetch and render the agency list.
//    $agency_list_builder = \Drupal::entityTypeManager()->getListBuilder('agency');
//    $agency_list = $agency_list_builder->render();
//    return $agency_list;
//  }
//
//  public function delete($agency) {
//    // Load the agency entity.
//    $agency_entity = Agency::load($agency);
//
//    // Check if the agency exists.
//    if ($agency_entity) {
//      // Delete the agency entity.
//      $agency_entity->delete();
//      // Redirect to the agency listing page or any other page.
//      return $this->redirect('appointment.agency_list');
//    }
//
//    // If agency does not exist, redirect or display error.
//    drupal_set_message(t('Agency not found.'));
//    return $this->redirect('appointment.agency_list');
//  }
//
//}
//
