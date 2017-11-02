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

    $build['product-id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'product-id',
      ],
      '#value' => $commerce_product,
    ];

    $build['options-container'] = [
      '#type' => 'markup',
      '#markup' => '<div id="options-container"></div>',
    ];

    return $build;
  }

}
