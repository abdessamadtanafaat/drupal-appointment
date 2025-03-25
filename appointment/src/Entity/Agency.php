<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\appointment\AgencyInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\office_hours\Plugin\Field\FieldType\OfficeHoursItem;

/**
 * Defines the agency entity class.
 *
 * @ContentEntityType(
 *   id = "appointment_agency",
 *   label = @Translation("Agency"),
 *   label_collection = @Translation("Agencies"),
 *   label_singular = @Translation("agency"),
 *   label_plural = @Translation("agencies"),
 *   label_count = @PluralTranslation(
 *     singular = "@count agencies",
 *     plural = "@count agencies",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\appointment\AgencyListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AgencyForm",
 *       "edit" = "Drupal\appointment\Form\AgencyForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "appointment_agency",
 *   admin_permission = "administer appointment_agency",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *     "changed" = "changed",
 *   },
 *   links = {
 *     "collection" = "/admin/content/agency",
 *     "add-form" = "/agency/add",
 *     "canonical" = "/agency/{appointment_agency}",
 *     "edit-form" = "/agency/{appointment_agency}/edit",
 *     "delete-form" = "/agency/{appointment_agency}/delete",
 *     "delete-multiple-form" = "/admin/content/agency/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.appointment_agency.settings",
 * )
 */
final class Agency extends ContentEntityBase implements AgencyInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Name of the agency.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the agency.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Description of the agency.
    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the agency.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Advisers (entity reference to User entities).
    $fields['advisers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Advisers'))
      ->setDescription(t('The advisers associated with this agency.'))
      ->setSetting('target_type', 'user') // Reference the User entity.
      ->setCardinality(-1) // Allow unlimited references.
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete_tags',
        'weight' => -5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['working_hours'] = BaseFieldDefinition::create('office_hours')
      ->setLabel(t('Working Hours'))
      ->setDescription(t('The working hours of the agency.'))
      ->setCardinality(7) // Allow 7 values (one for each day of the week)
      ->setDefaultValue([
        [
          'day' => 0, // Sunday
          'starthours' => 800,  // 8:00 in 24-hour format without colon
          'endhours' => 1200,    // 12:00
        ],
        [
          'day' => 1, // Monday
          'starthours' => 800,
          'endhours' => 1200,
        ],
        [
          'day' => 2, // Tuesday
          'starthours' => 800,
          'endhours' => 1200,
        ],
        [
          'day' => 3, // Wednesday
          'starthours' => 800,
          'endhours' => 1200,
        ],
        [
          'day' => 4, // Thursday
          'starthours' => 800,
          'endhours' => 1200,
        ],
        [
          'day' => 5, // Friday
          'starthours' => 800,
          'endhours' => 1200,
        ],
        [
          'day' => 6, // Saturday
          'starthours' => 800,
          'endhours' => 1200,
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'office_hours_default',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'office_hours',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'hidden',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Add the uid field (reference to the User entity).
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the agency author.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the appointment was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the appointment was last edited.'));

    return $fields;
  }
}
