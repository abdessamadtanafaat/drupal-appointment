<?php

declare(strict_types=1);

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the appointment entity type.
 */
final class AppointmentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['description'] = $this->t('Description');
    $header['date'] = $this->t('Date');
    $header['agency'] = $this->t('Agency');
    $header['advisor'] = $this->t('Advisor');
    $header['appointment_type'] = $this->t('Appointment Type');
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\appointment\AppointmentInterface $entity */

    // Description.
    $row['description'] = $entity->get('description')->value;

    // Date.
    $row['date'] = $entity->get('appointment_date')->value;

    // Agency (name).
    $agency = $entity->get('agency_id')->entity;
    $row['agency'] = $agency ? $agency->label() : $this->t('N/A');

    // Advisor.
    $advisor = $entity->get('advisor_id')->entity;
    $row['advisor'] = $advisor ? $advisor->label() : $this->t('N/A');

    // Appointment Type.
    $appointment_type = $entity->get('appointment_type')->entity;
    $row['appointment_type'] = $appointment_type ? $appointment_type->label() : $this->t('N/A');

    // Operations (edit/delete links).
    $row['operations']['data'] = $this->buildOperations($entity);

    return $row;
  }
}
