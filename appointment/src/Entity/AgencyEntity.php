<?php
//
//namespace Drupal\appointment\Entity;
//
//use Drupal\Core\Config\Entity\ConfigEntityBase;
//use Drupal\appointment\Entity\AgencyEntityInterface;
//use Drupal\appointment\Service\AutoIncrementIdService;
//use Drupal\Core\DependencyInjection\ContainerInterface;
//
///**
// * Defines the Agency entity.
// *
// * @ConfigEntityType(
// *   id = "agency",
// *   label = @Translation("Agency"),
// *   handlers = {
// *     "list_builder" = "Drupal\appointment\AgencyListBuilder",
// *     "form" = {
// *       "add" = "Drupal\appointment\Form\AgencyForm",
// *       "edit" = "Drupal\appointment\Form\AgencyForm",
// *       "delete" = "Drupal\appointment\Form\AgencyDeleteForm"
// *     }
// *   },
// *   config_prefix = "agency",
// *   admin_permission = "administer agency",
// *   entity_keys = {
// *     "id" = "id",
// *     "label" = "name"
// *   },
// *   config_export = {
// *     "id",
// *     "name",
// *     "location",
// *     "email"
// *   },
// *   links = {
// *     "edit-form" = "/admin/config/appointment/agency/{agency}/edit",
// *     "delete-form" = "/admin/config/appointment/agency/{agency}/delete"
// *   }
// * )
// */
//class AgencyEntity extends ConfigEntityBase implements AgencyEntityInterface {
//  /**
//   * The Agency ID.
//   *
//   * @var string
//   */
//  protected $id;
//
//  /**
//   * The Agency name.
//   *
//   * @var string
//   */
//  protected $name;
//
//  /**
//   * The Agency location.
//   *
//   * @var string
//   */
//  public $location;
//
//  /**
//   * The Agency contact email.
//   *
//   * @var string
//   */
//  public $email;
//
//
//  /**
//   * {@inheritdoc}
//   */
//  public function __construct(array $values = [], $id = NULL) {
//    parent::__construct($values, $id);
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function setId($id) {
//    $this->id = $id;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public function getId() {
//    return $this->id;
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function create(array $values = []) {
//    $entity = parent::create($values);
//    // Ensure ID is set correctly on creation.
//    if (empty($entity->id)) {
//      $entity->id = \Drupal::service('appointment.auto_increment_id')->getNextId();
//    }
//    return $entity;
//  }
//}
