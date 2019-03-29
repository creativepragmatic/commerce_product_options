<?php

/**
 * @file
 * Provide a custom views field data that isn't tied to any other module.
 */

/**
 * Implements hook_views_data().
 */
function commerce_product_options_views_data() {

  $data['views']['table']['group'] = t('Product Options');
  $data['views']['table']['join'] = [
    '#global' => [],
  ];

  $data['views']['product_options_field'] = [
    'title' => t('Product & Options'),
    'help' => t('Displays order item and field options if available.'),
    'field' => [
      'id' => 'product_options_field',
    ],
  ];

  return $data;
}
