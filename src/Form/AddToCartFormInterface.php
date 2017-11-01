<?php

namespace Drupal\commerce_product_options\Form;

use Drupal\Core\Entity\ContentEntityFormInterface;

/**
 * Provides an add to cart form interface with product options.
 */
interface AddToCartFormInterface extends ContentEntityFormInterface {

  /**
   * Sets the form ID.
   *
   * @param string $form_id
   *   The form ID.
   *
   * @return $this
   */
  public function setFormId($form_id);

}
