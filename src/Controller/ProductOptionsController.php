<?php

namespace Drupal\commerce_product_options\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hosts the product options management controller.
 */
class ProductOptionsController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ProductOptionsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Displays the product option management page.
   *
   * @return array
   *   A render array.
   */
  public function optionsPage($commerce_product) {

    $product = $this->entityTypeManager
      ->getStorage('commerce_product')
      ->load($commerce_product);
    $product_type = $this->entityTypeManager
      ->getStorage('commerce_product_type')
      ->load($product->bundle());

    $page['#attached']['library'][] = 'commerce_product_options/admin';

    $page['product-id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'product-id',
      ],
      '#value' => $commerce_product,
    ];

    $page['variation-type'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'variation-type',
      ],
      '#value' => $product_type->getVariationTypeId(),
    ];

    $page['product-title'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'product-title',
      ],
      '#value' => $product->getTitle(),
    ];

    $page['options-container'] = [
      '#type' => 'markup',
      '#markup' => '<div id="product-options-admin"></div>',
    ];

    return $page;
  }

}
