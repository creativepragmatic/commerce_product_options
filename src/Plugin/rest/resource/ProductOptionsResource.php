<?php

namespace Drupal\commerce_product_options\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "product_options_resource",
 *   label = @Translation("Product options resource"),
 *   uri_paths = {
 *     "canonical" = "/commerce_product_option/{commerce_product_option}"
 *   }
 * )
 */
class ProductOptionsResource extends ResourceBase {

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
   * Constructs a new ProductOptionsResource object.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The kill switch.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, KillSwitch $killSwitch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->killSwitch = $killSwitch;
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
      $container->get('logger.factory')->get('commerce_product_options'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns a product options for a specific product.
   *
   * @param int $product_id
   *   The entity if of the product.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON response containing product options.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Throws access denied exception if user does not have required
   *   permissions.
   */
  public function get($product_id) {

    // Prevent endpoint from being cached.
    $this->killSwitch->trigger();

    if (!$this->currentUser->hasPermission('administer commerce_product')) {
      throw new AccessDeniedHttpException();
    }

    $options = [];
    $disable_cache = new CacheableMetadata();
    $disable_cache->setCacheMaxAge(0);
    $response = new CacheableJsonResponse();
    $response->addCacheableDependency($disable_cache);

    $product = Product::load($product_id);
    if (!$product->get('options')->isEmpty()) {
      $options = $product->get('options')->first()->getValue();
    }

    $base_sku = !empty($options['base_sku']) ? $options['base_sku'] : '';
    $base_price = !empty($options['base_price']) ? $options['base_price'] : 0;
    $sku_generation = !empty($options['sku_generation']) ? $options['sku_generation'] : 'byOption';

    if (!empty($options['fields'])) {
      $fields = $options['fields'];
    }
    else {
      $fields = [];
    }

    $response->setData([
      'base_sku' => $base_sku,
      'base_price' => $base_price,
      'sku_generation' => $sku_generation,
      'fields' => $fields,
    ]);

    return $response;
  }

  /**
   * Responds to DELETE requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function delete() {

    if (!$this->currentUser->hasPermission('administer commerce_product')) {
      throw new AccessDeniedHttpException();
    }

    return new ResourceResponse("Implement REST State DELETE!");
  }

  /**
   * Responds to PATCH requests.
   *
   * Updates options for specified product entity.
   *
   * @param int $product_id
   *   The entity if of the product.
   * @param string $data
   *   JSON list of options to be updated.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing product options.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function patch($product_id, $data) {

    if (!$this->currentUser->hasPermission('administer commerce_product')) {
      throw new AccessDeniedHttpException();
    }

    $response = new JsonResponse();

    $product = Product::load($product_id);
    if (!$product->get('options')->isEmpty()) {
      $options = $product->get('options')->first()->getValue();
    }

    switch ($data['operation']) {

      case 'UPDATE_BASE_FIELDS':
        $options['base_sku'] = $data['base_sku'];
        $options['base_price'] = $data['base_price'];
        $options['sku_generation'] = $data['sku_generation'];
        $product->set('options', $options);
        $product->save();
        $response->setData([
          'base_sku' => $data['base_sku'],
          'base_price' => $data['base_price'],
          'sku_generation' => $data['sku_generation'],
        ]);
        return $response;

      case 'ADD_TEXT_FIELD':
        $field['type'] = $data['type'];
        $field['title'] = $data['title'];
        $field['helpText'] = $data['helpText'];
        $field['size'] = $data['size'];
        $field['required'] = $data['required'];
        $options['fields'][] = $field;
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'ADD_CHECKBOX':
        $field['type'] = $data['type'];
        $field['title'] = $data['title'];
        $field['skuSegment'] = $data['skuSegment'];
        $field['priceModifier'] = $data['priceModifier'];
        $field['skuGeneration'] = $data['skuGeneration'];
        $field['mandatoryOption'] = $data['mandatory'];
        $field['required'] = $data['required'];
        $options['fields'][] = $field;
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'ADD_ADD_ON':
        $field['type'] = $data['type'];
        $field['addOnId'] = $data['addOnId'];
        $field['title'] = $data['addOnTitle'];
        $field['requiredRoles'] = $data['requiredRoles'];
        $field['helpText'] = $data['helpText'];
        $field['emptyText'] = $data['emptyText'];
        $field['required'] = $data['required'];
        $options['fields'][] = $field;
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'ADD_SELECT':
        $field['type'] = $data['type'];
        $field['title'] = $data['title'];
        $field['helpText'] = $data['helpText'];
        $field['skuGeneration'] = $data['skuGeneration'];
        $field['required'] = $data['required'];
        $field['options'] = $data['options'];
        $options['fields'][] = $field;
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'MOVE_UP_FIELD':
        if ($data['index'] > 0) {
          $movedItem = array_splice($options['fields'], $data['index'], 1);
          array_splice($options['fields'], $data['index'] - 1, 0, $movedItem);
          $product->set('options', $options);
          $product->save();
        }
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'MOVE_DOWN_FIELD':
        if ($data['index'] + 1 < count($options['fields'])) {
          $movedItem = array_splice($options['fields'], $data['index'], 1);
          array_splice($options['fields'], $data['index'] + 1, 0, $movedItem);
          $product->set('options', $options);
          $product->save();
        }
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'DELETE_FIELD':
        unset($options['fields'][$data['index']]);
        // Reindex the array to squash any gaps.
        $options['fields'] = array_values($options['fields']);
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;

      case 'UPDATE_PRODUCT_VARIATIONS':
        $new_variations = [];
        $current_skus = [];
        if ($product->hasVariations()) {
          foreach ($product->getVariations() as $variation) {
            $current_skus[] = $variation->getSku();
            $variation->setUnpublished();
            $variation->save();
          }
        }
        $user = User::load($this->currentUser->id());
        foreach ($data['variations'] as $variation) {
          if (in_array($variation['SKU'], $current_skus)) {
            $storage = $this->entityTypeManager
              ->getStorage('commerce_product_variation');
            $variation_array = $storage->loadByProperties(['sku' => $variation['SKU']]);
            $variation_entity = array_pop($variation_array);
            $variation_entity->setPrice(new Price(strval($variation['price']), 'USD'));
            $variation_entity->setActive(TRUE);
            $variation_entity->setOwner($user);
            $variation_entity->save();
          }
          else {
            $new_variation = ProductVariation::create([
              'type' => $data['variation_type'],
              'product_id' => $product_id,
              'sku' => $variation['SKU'],
              'title' => $variation['title'],
              'price' => new Price(strval($variation['price']), 'USD'),
              'status' => 1,
            ]);
            $new_variation->setOwner($user);
            $product->addVariation($new_variation);
            $product->save();
          }
        }
        $response->setData(TRUE);
        return $response;
    }
  }

}
