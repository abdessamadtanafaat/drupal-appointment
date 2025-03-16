<?php

namespace Drupal\appointment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\appointment\Entity\AgencyEntityInterface;

/**
 * Defines the Agency entity.
 *
 * @ConfigEntityType(
 *   id = "agency",
 *   label = @Translation("Agency"),
 *   handlers = {
 *     "list_builder" = "Drupal\appointment\AgencyListBuilder",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AgencyForm",
 *       "edit" = "Drupal\appointment\Form\AgencyForm",
 *       "delete" = "Drupal\appointment\Form\AgencyDeleteForm"
 *     }
 *   },
 *   config_prefix = "agency",
 *   admin_permission = "administer agency",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/appointment/agency/{agency}/edit",
 *     "delete-form" = "/admin/config/appointment/agency/{agency}/delete"
 *   }
 * )
 */
class AgencyEntity extends ConfigEntityBase implements AgencyEntityInterface {
  /**
   * The Agency ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Agency name.
   *
   * @var string
   */
  protected $name;

  /**
   * The Agency location.
   *
   * @var string
   */
  protected $location;

  /**
   * The Agency contact email.
   *
   * @var string
   */
  protected $email;
}
