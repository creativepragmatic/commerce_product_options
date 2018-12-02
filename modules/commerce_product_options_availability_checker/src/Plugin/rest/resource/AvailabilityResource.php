<?php

namespace Drupal\commerce_product_options_availability_checker\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($sku = NULL) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    if (!empty($sku)) {

      $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties(['sku' => $sku]);
      if (count($variation) > 0) {
        $disable_cache = new CacheableMetadata();
        $disable_cache->setCacheMaxAge(0);
        $manager = \Drupal::service('commerce_stock.service_manager');
        $level = intval($manager->getStockLevel(current($variation)));

        if ($level === 0) {
          return (new ResourceResponse('SOLD OUT', 200))->addCacheableDependency($disable_cache);
        }
        else if ($level > 0 && $level < 10) {
          return (new ResourceResponse('Less than 10 available.', 200))->addCacheableDependency($disable_cache);
        }
        else {
          return (new ResourceResponse('plentiful', 200))->addCacheableDependency($disable_cache);
        }
      }
      else {
        throw new NotFoundHttpException(t('Product varuation with SKU @sku was not found', ['@sku' => $sku]));
      }
    }

    throw new BadRequestHttpException(t('No SKU provided.'));
  }

}
