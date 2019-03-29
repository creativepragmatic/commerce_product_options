<?php

namespace Drupal\commerce_product_options\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("product_options_field")
 */
class ProductOptionsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    if ($values->_entity instanceof Order) {
      $title = $values->_relationship_entities['order_items']->getTitle();
      $options = $values->_relationship_entities['order_items']->getData('product_option');
    }
    elseif ($values->_entity instanceof OrderItem) {
      $title = $values->_entity->getTitle();
      $options = $values->_entity->getData('product_option');
    }

    $options_ul = '<br/><ul>';
    if (!empty($options)) {
      foreach ($options as $name => $value) {
        $options_ul .= '<li>' . $name . ': ' . $value . '</li>';
      }
      $options_ul .= '</ul>';
      $title = $title . $options_ul;
    }

    return $this->t($title);
  }

}
