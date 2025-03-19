<?php
//
//namespace Drupal\appointment\Service;
//
//use Drupal\Core\Config\ConfigFactoryInterface;
//use Drupal\Core\Logger\LoggerChannelFactoryInterface;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//
//class AutoIncrementIdService {
//
//  /**
//   * The configuration factory.
//   *
//   * @var \Drupal\Core\Config\ConfigFactoryInterface
//   */
//  protected $configFactory;
//
//  /**
//   * The logger channel.
//   *
//   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
//   */
//  protected $logger;
//
//  /**
//   * Constructs a new AutoIncrementIdService.
//   *
//   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
//   *   The config factory.
//   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
//   *   The logger service.
//   */
//  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger) {
//    $this->configFactory = $config_factory;
//    $this->logger = $logger;
//  }
//
//  /**
//   * Gets the next auto-increment ID.
//   *
//   * @return string
//   *   The next auto-incremented ID.
//   */
//  public function getNextId() {
//    // Get the configuration storing the last ID value.
//    $config = $this->configFactory->getEditable('appointment.agency_ids');
//    $last_id = $config->get('last_id') ?: 0;
//
//    // Increment the ID.
//    $next_id = $last_id + 1;
//
//    // Save the new ID back to the configuration.
//    $config->set('last_id', $next_id)->save();
//
//    return 'agency_' . $next_id;
//  }
//}
//
