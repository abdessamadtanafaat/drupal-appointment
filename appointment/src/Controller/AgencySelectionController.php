<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AgencySelectionController extends ControllerBase {

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStore = $temp_store_factory->get('appointment');
  }

  /**
   * Store the selected agency.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response indicating success or failure.
   */
  public function storeAgency() {
    $agency_id = \Drupal::request()->get('agency_id');
    if ($agency_id) {
      // Save the agency ID to tempstore.
      $this->tempStore->set('selected_agency', $agency_id);
      return new JsonResponse(['status' => 'success']);
    }

    return new JsonResponse(['status' => 'error'], 400);
  }

  /**
   * Factory method for creating the controller.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container interface.
   *
   * @return static
   *   A new instance of the controller.
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('tempstore.private'));
  }

}
