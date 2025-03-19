<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

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

    $row = [];
    $row['name'] = $agency->label();
    $row['location'] = $agency->location;
    $row['email'] = $agency->email;

    // Ensure that the agency ID is in the correct format: agency_<numeric ID>
    $agency_id = $agency->id();

    // Check if the ID is correctly formatted, if not return a default value
    if (preg_match('/^agency_\d+$/', $agency_id)) {
      $edit_url = Url::fromRoute('appointment.agency_edit', ['agency' => $agency_id]);
      $delete_url = Url::fromRoute('appointment.agency_delete', ['agency' => $agency_id]);
    } else {
      // Handle the case where the ID is not in the expected format
      // This shouldn't happen in a properly functioning system, but you may need a fallback or logging
      \Drupal::logger('appointment')->error('Invalid agency ID format: @id', ['@id' => $agency_id]);
      return [];
    }

    // Create the Edit and Delete links
    $edit_link = Link::fromTextAndUrl(t('Edit'), $edit_url);
    $delete_link = Link::fromTextAndUrl(t('Delete'), $delete_url);

    // Add the links to the operations column
    $row['operations'] = [
      'data' => [
        'edit' => $edit_link->toRenderable(),
        'delete' => $delete_link->toRenderable(),
      ],
      'class' => ['operations'],
    ];

    return $row;
  }
}

