<?php

namespace Drupal\commerce_product_options\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('commerce_cart.page')) {
      $route->setDefault('_controller', '\Drupal\commerce_product_options\Controller\CartController::cartPage');
    }
  }

}
