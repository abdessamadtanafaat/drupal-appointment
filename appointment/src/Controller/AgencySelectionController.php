<?php
namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\TempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AgencySelectionController extends ControllerBase {

  /**
   * @var \Drupal\Core\TempStore\TempStore
   */
  protected $tempStore;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\TempStore\TempStoreFactory $temp_store_factory
   */
  public function __construct(TempStoreFactory $temp_store_factory) {
    $this->tempStore = $temp_store_factory->get('appointment');
  }

  /**
   * Store the selected agency.
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
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('tempstore.private'));
  }
}
