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

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: hello with parameter(s): $product_id'),
    ];

    //return $build;
  }

}
