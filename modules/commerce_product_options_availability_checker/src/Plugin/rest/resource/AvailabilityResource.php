<?php

namespace Drupal\commerce_product_options_availability_checker\Plugin\rest\resource;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_stock\StockServiceManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "availability_resource",
 *   label = @Translation("Stock availability resource"),
 *   uri_paths = {
 *     "canonical" = "/commerce-product-options/availability/{sku}"
 *   }
 * )
 */
class AvailabilityResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Stock service manager.
   *
   * @var \Drupal\commerce_stock\StockServiceManager
   */
  protected $stockServiceManager;

  /**
   * Constructs a new AvailabilityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager instance.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The kill switch.
   * @param \Drupal\commerce_stock\StockServiceManagerInterface $stock_service_manager
   *   Stock service manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user, EntityTypeManager $entity_type_manager, KillSwitch $killSwitch, StockServiceManagerInterface $stock_service_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->killSwitch = $killSwitch;
    $this->stockServiceManager = $stock_service_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('commerce_product_options_availability_checker'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('page_cache_kill_switch'),
      $container->get('commerce_stock.service_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $sku
   *   SKU of variation availability is being checked for.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($sku = NULL) {

    // Prevents endpoint from being cached.
    $this->killSwitch->trigger();

    if (!empty($sku)) {

      $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties(['sku' => $sku]);
      if (count($variation) > 0) {

        $service = $this->stockServiceManager->getService(current($variation));
        $checker = $service->getStockChecker();
        $always = $checker->getIsAlwaysInStock(current($variation));

        if ($always) {
          return (new ResourceResponse('plentiful', 200));
        }

        $level = intval($this->stockServiceManager->getStockLevel(current($variation)));

        if ($level < 1) {
          return (new ResourceResponse('UNAVAILABLE', 200));
        }
        elseif ($level > 0 && $level < 10) {
          return (new ResourceResponse('Less than 10 available.', 200));
        }
        else {
          return (new ResourceResponse('plentiful', 200));
        }
      }
      else {
        throw new NotFoundHttpException(t('Product varuation with SKU @sku was not found', ['@sku' => $sku]));
      }
    }

    throw new BadRequestHttpException(t('No SKU provided.'));
  }

}
