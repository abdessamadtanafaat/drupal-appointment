<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppointmentController extends ControllerBase {

  /**
   * List function for appointments.
   */
  public function list() {
    // You can add logic here to retrieve and render the appointments list.
    return [
      '#markup' => $this->t('This is where the list of appointments will appear.'),
    ];
  }

}
