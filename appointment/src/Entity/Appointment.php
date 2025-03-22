<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\appointment\AppointmentInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the appointment entity class.
 *
 * @ContentEntityType(
 *   id = "appointment",
 *   label = @Translation("Appointment"),
 *   label_collection = @Translation("Appointments"),
 *   label_singular = @Translation("appointment"),
 *   label_plural = @Translation("appointments"),
 *   label_count = @PluralTranslation(
 *     singular = "@count appointments",
 *     plural = "@count appointments",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\appointment\AppointmentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AppointmentForm",
 *       "edit" = "Drupal\appointment\Form\AppointmentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "appointment",
 *   admin_permission = "administer appointment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/appointment",
 *     "add-form" = "/appointment/add",
 *     "canonical" = "/appointment/{appointment}",
 *     "edit-form" = "/appointment/{appointment}/edit",
 *     "delete-form" = "/appointment/{appointment}/delete",
 *     "delete-multiple-form" = "/admin/content/appointment/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.appointment.settings",
 * )
 */
final class Appointment extends ContentEntityBase implements AppointmentInterface {

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

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the appointment.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -11,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the appointment.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Appointment Type Name.
    $fields['appointment_type_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Appointment Type Name'))
      ->setDescription(t('The name of the appointment type.'))
      ->setRequired(FALSE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Advisor Name.
    $fields['advisor'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Advisor Name'))
      ->setDescription(t('The name of the advisor.'))
      ->setRequired(FALSE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Agency Name.
    $fields['agency'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Agency Name'))
      ->setDescription(t('The name of the agency.'))
      ->setRequired(FALSE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['agency_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Agency'))
      ->setDescription(t('The agency for the appointment.'))
      ->setSetting('target_type', 'appointment_agency') // Reference the Agency entity.
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete', // Use an autocomplete widget.
        'weight' => -9,
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
        'weight' => -9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Appointment Type (entity reference to a taxonomy term).
    $fields['appointment_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Appointment Type'))
      ->setDescription(t('The type of appointment.'))
      ->setSetting('target_type', 'taxonomy_term') // Reference taxonomy terms.
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting('handler_settings', [
        'target_bundles' => ['appointment_types'], // Replace with your vocabulary machine name.
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select', // Use a select dropdown.
        'weight' => -9,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => -9,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Advisor ID (entity reference to a user or custom entity).
    $fields['advisor_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Advisor'))
      ->setDescription(t('The advisor for the appointment.'))
      ->setSetting('target_type', 'user') // Change to your target entity type.
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -8,
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
        'weight' => -8,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Appointment Start Date and Time.
    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Appointment Start Date'))
      ->setDescription(t('The Start date and time of the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Appointment Date and Time.
    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Appointment End Date'))
      ->setDescription(t('The End date and time of the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('view', TRUE);


    // First Name.
    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('The first name of the user who create the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Last Name.
    $fields['last_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('The last name of the user who create the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Email.
    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email of the user who create the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'email_default',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'email_mailto',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    // Phone.
    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phone'))
      ->setDescription(t('The phone number of the user who create the appointment.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['appointment_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the appointment (e.g., available, booked).'))
      ->setDefaultValue('available')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
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

    //    $fields['description'] = BaseFieldDefinition::create('text_long')
    //      ->setLabel(t('Description'))
    //      ->setDisplayOptions('form', [
    //        'type' => 'text_textarea',
    //        'weight' => 10,
    //      ])
    //      ->setDisplayConfigurable('form', TRUE)
    //      ->setDisplayOptions('view', [
    //        'type' => 'text_default',
    //        'label' => 'above',
    //        'weight' => 10,
    //      ])
    //      ->setDisplayConfigurable('view', TRUE);

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

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => '',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
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


