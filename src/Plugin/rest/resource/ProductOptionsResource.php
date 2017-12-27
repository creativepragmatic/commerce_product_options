<?php

namespace Drupal\commerce_product_options\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
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
      $container->get('current_user')
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
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response containing product options.
   * 
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Throws access denied exception if user does not have required permissions.
   */
  public function get($product_id) {

    if (!$this->currentUser->hasPermission('administer commerce_product')) {
      throw new AccessDeniedHttpException();
    }

    $options = [];
    $response = new JsonResponse();

    $product = Product::load($product_id);
    if (!$product->get('options')->isEmpty()) {
      $options = $product->get('options')->first()->getValue();
    }

    $base_sku = !empty($options['base_sku']) ? $options['base_sku'] : '';
    $base_price = !empty($options['base_price']) ? $options['base_price'] : '';

    if (!empty($options['fields'])) {
      $fields = $options['fields'];
    } else {
      $fields = [];
    }

    $response->setData([
      'base_sku' => $base_sku,
      'base_price' => $base_price,
      'fields' => $fields
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
   * @param $data
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
        $product->set('options', $options);
        $product->save();
        $response->setData([
          'base_sku' => $data['base_sku'],
          'base_price' => $data['base_price']
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
        $field['options'] = $data['options'];
        $options['fields'][] = $field;
        $product->set('options', $options);
        $product->save();
        $fields = $product->get('options')->first()->getValue()['fields'];
        $response->setData($fields);
        return $response;
    }
  }
}
