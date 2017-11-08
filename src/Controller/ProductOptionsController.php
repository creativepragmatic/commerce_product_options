<?php

namespace Drupal\commerce_product_options\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Hosts the product options management controller.
 */
class ProductOptionsController extends ControllerBase {

  /**
   * Displays the product option management page.
   *
   * @return array
   *   A render array.
   */
  public function optionsPage($commerce_product) {

    $page['#attached']['library'][] = 'commerce_product_options/admin';

    $page['product-id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'product-id',
      ],
      '#value' => $commerce_product,
    ];

    $page['options-container'] = [
      '#type' => 'markup',
      '#markup' => '<div id="option-set-admin"></div>',
    ];

    return $page;
  }

}
