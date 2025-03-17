<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Agency entities.
 */
class AgencyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      ['data' => $this->t('Agency Name'), 'field' => 'name'],
      ['data' => $this->t('Location'), 'field' => 'location'],
      ['data' => $this->t('Email'), 'field' => 'email'],
      ['data' => $this->t('Operations'), 'sortable' => FALSE],
    ];
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $agency) {
    return [
      'name' => $agency->label(),
      'location' => $agency->location,
      'email' => $agency->email,
      'operations' => $this->buildOperations($agency),
    ];
  }
}

