<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class AppointmentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['name'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

}
