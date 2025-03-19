<?php

namespace Drupal\appointment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Appointment entity.
 *
 * @ContentEntityType(
 *   id = "appointment",
 *   label = @Translation("Appointment"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\appointment\AppointmentListBuilder",
 *     "form" = {
 *       "default" = "Drupal\appointment\Form\AppointmentForm",
 *       "add" = "Drupal\appointment\Form\AppointmentForm",
 *       "edit" = "Drupal\appointment\Form\AppointmentForm",
 *       "delete" = "Drupal\appointment\Form\AppointmentDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "appointment",
 *   admin_permission = "administer appointment entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *   },
 *   links = {
 *     "canonical" = "/appointment/{appointment}",
 *     "add-form" = "/admin/content/appointment/add",
 *     "edit-form" = "/admin/content/appointment/{appointment}/edit",
 *     "delete-form" = "/admin/content/appointment/{appointment}/delete",
 *     "collection" = "/admin/content/appointment",
 *   },
 *   field_ui_base_route = "entity.appointment.collection",
 * )
 */
class AppointmentEntity extends ContentEntityBase implements AppointmentEntityInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Name field.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Appointment Name'))
      ->setDescription(t('The name of the appointment.'))
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Appointment date field.
    $fields['appointment_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Appointment Date'))
      ->setDescription(t('The date of the appointment.'))
      ->setSettings([
        'datetime_type' => 'datetime',
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Status field.
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('The status of the appointment (active/archived).'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => -2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // User field.
//    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
//      ->setLabel(t('User'))
//      ->setDescription(t('The user who booked the appointment.'))
//      ->setSetting('target_type', 'user')
//      ->setSetting('handler', 'default')
//      ->setDisplayOptions('view', [
//        'label' => 'above',
//        'type' => 'entity_reference_label',
//        'weight' => -1,
//      ])
//      ->setDisplayOptions('form', [
//        'type' => 'entity_reference_autocomplete',
//        'weight' => -1,
//        'settings' => [
//          'match_operator' => 'CONTAINS',
//          'size' => '60',
//          'placeholder' => '',
//        ],
//      ])
//      ->setDisplayConfigurable('form', TRUE)
//      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  public function getName() {
    // TODO: Implement getName() method.
  }

  public function setName($name) {
    // TODO: Implement setName() method.
  }

  public function getDate() {
    // TODO: Implement getDate() method.
  }

  public function setDate($date) {
    // TODO: Implement setDate() method.
  }

  public function getStatus() {
    // TODO: Implement getStatus() method.
  }

  public function setStatus($status) {
    // TODO: Implement setStatus() method.
  }

  public function getUser() {
    // TODO: Implement getUser() method.
  }

  public function setUser(UserInterface $user) {
    // TODO: Implement setUser() method.
  }

}
