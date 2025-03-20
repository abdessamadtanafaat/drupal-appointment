<?php

declare(strict_types=1);

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the agency entity type.
 */
final class AgencyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Name');
    $header['description'] = $this->t('Description');
    $header['status'] = $this->t('Status');
    $header['uid'] = $this->t('Author');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');
    $header['operations'] = $this->t('Operations');
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\appointment\AgencyInterface $entity */

    // Name.
    $row['name'] = $entity->get('name')->value;

    // Description.
    $row['description'] = $entity->get('description')->value;

    // Status.
    $row['status'] = $entity->get('status')->value ? $this->t('Enabled') : $this->t('Disabled');

    // Author.
    $username_options = [
      'label' => 'hidden',
      'settings' => ['link' => $entity->get('uid')->entity->isAuthenticated()],
    ];
    $row['uid']['data'] = $entity->get('uid')->view($username_options);

    // Created date.
    $row['created']['data'] = $entity->get('created')->view(['label' => 'hidden']);

    // Changed date.
    $row['changed']['data'] = $entity->get('changed')->view(['label' => 'hidden']);

    // Operations (edit/delete links).
    $row['operations']['data'] = $this->buildOperations($entity);

    return $row;
  }
}
