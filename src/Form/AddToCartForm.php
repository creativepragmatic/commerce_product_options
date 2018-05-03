<?php

namespace Drupal\commerce_product_options\Form;

use Drupal\commerce\AvailabilityManagerInterface;
use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an order item add to cart form with product options.
 */
class AddToCartForm extends ContentEntityForm implements AddToCartFormInterface {

  /**
   * The availability manager.
   *
   * @var \Drupal\commerce_cart\Form\AvailabilityManagerInterface
   */
  protected $availabilityManager;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The order type resolver.
   *
   * @var \Drupal\commerce_order\Resolver\OrderTypeResolverInterface
   */
  protected $orderTypeResolver;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * The chain base price resolver.
   *
   * @var \Drupal\commerce_price\Resolver\ChainPriceResolverInterface
   */
  protected $chainPriceResolver;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The form ID.
   *
   * @var string
   */
  protected $formId;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AddToCartForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce\AvailabilityManagerInterface $availability_manager
   *   The availability manager.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_order\Resolver\OrderTypeResolverInterface $order_type_resolver
   *   The order type resolver.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   * @param \Drupal\commerce_price\Resolver\ChainPriceResolverInterface $chain_price_resolver
   *   The chain base price resolver.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, AvailabilityManagerInterface $availability_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store, ChainPriceResolverInterface $chain_price_resolver, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    $this->availabilityManager = $availability_manager;
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->orderTypeResolver = $order_type_resolver;
    $this->currentStore = $current_store;
    $this->chainPriceResolver = $chain_price_resolver;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('commerce.availability_manager'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_price.chain_price_resolver'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Retrieves the current user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The current user.
   */
  public function getCurrentUser() {
    return $this->currentUser;
  }

  /**
   * Retrieves the availability manager.
   *
   * @return \Drupal\commerce\AvailabilityManagerInterface
   *   The availability manager.
   */
  public function getAvailabilityManager() {
    return $this->availabilityManager;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return $this->entity->getEntityTypeId() . '_' . $this->operation . '_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (empty($this->formId)) {
      $this->formId = $this->getBaseFormId();
      /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
      $order_item = $this->entity;
      if ($purchased_entity = $order_item->getPurchasedEntity()) {
        $this->formId .= '_' . $purchased_entity->getEntityTypeId() . '_' . $purchased_entity->id();
      }
    }

    return $this->formId;
  }

  /**
   * {@inheritdoc}
   */
  public function setFormId($form_id) {
    $this->formId = $form_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $product_id = $this->entity->getPurchasedEntity()->getProductId();
    $storage  = $this->entityManager->getStorage('commerce_product');
    $product = $storage ->load($product_id);

    if (!$product->get('options')->isEmpty()) {
      $options = $product->get('options')->getValue()[0]['fields'];
      $base_sku = $product->get('options')->getValue()[0]['base_sku'];
      $sku_generation = $product->get('options')->getValue()[0]['sku_generation'];

      $form['base-sku'] = [
        '#type' => 'hidden',
        '#value' => $base_sku,
      ];

      $form['sku-generation'] = [
        '#type' => 'hidden',
        '#value' => $sku_generation,
      ];

      foreach ($options as $option) {
        $machine_name_title = preg_replace('@[^a-z0-9-]+@', '_', strtolower($option['title']));

        $form['options'][$machine_name_title] = [
          '#type' => $option['type'],
          '#title' => t($option['title']),
          '#required' => $option['required'] ? TRUE : FALSE,
        ];

        if ($option['type'] === 'checkbox') {
          if ($option['mandatoryOption']) {
            $form['options'][$machine_name_title]['#default_value'] = TRUE;
            $form['options'][$machine_name_title]['#disabled'] = TRUE;
          }

          if (!empty($option['priceModifier'])) {
            $title = $option['title'] . ' +$' . number_format($option['priceModifier'], 2);
            $form['options'][$machine_name_title]['#title'] = t($title);
          }

          if ($sku_generation === 'byOption' && !empty($option['skuGeneration'])) {
            $form['options'][$machine_name_title]['#attributes'] = [
              'data-sku-generation' => 1,
              'data-sku' => $base_sku . '-' . $option['skuSegment'],
            ];
          }
        }

        if ($option['type'] === 'select') {
          $sku_generation = !empty($option['skuGeneration']) ? 'Yes' : 'No';
          $form['options'][$machine_name_title]['#attributes'] = [
            'data-sku-generation' => [$sku_generation],
          ];
        }

        if (!empty($option['helpText'])) {
          $form['options'][$machine_name_title]['#description'] = t($option['helpText']);
        }

        if (!empty($option['size'])) {
          $form['options'][$machine_name_title]['#size'] = $option['size'];
        }

        if (!empty($option['options'])) {
          foreach ($option['options'] as $select_option) {
            if ($select_option['isDefault']) {
              $default = $select_option['skuSegment'];
            }
            if (!empty($select_option['priceModifier'])) {
              $modifier = ', +$' . number_format($select_option['priceModifier'], 2);
              $title = $select_option['optionTitle'] . $modifier;
            }
            else {
              $title = $select_option['optionTitle'];
            }
            $select_options[$select_option['skuSegment']] = $title;
          }

          $form['options'][$machine_name_title]['#options'] = $select_options;
          if (!empty($default)) {
            $form['options'][$machine_name_title]['#default_value'] = $default;
            unset($default);
          }
          unset($select_options);
        }
      }
    }

    // The widgets are allowed to signal that the form should be hidden
    // (because there's no purchasable entity to select, for example).
    if ($form_state->get('hide_form')) {
      $form['#access'] = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {

    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to cart'),
      '#submit' => ['::submitForm'],
    ];

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $this->entity;

    $options = $this->buildOptions($form_state);
    $order_item->setData('product_option', $options);

    if ($form_state->getValue('sku-generation') === 'bySegment') {

      /** @var \Drupal\commerce\PurchasableEntityInterface $purchased_entity */
      $purchased_entity = $order_item->getPurchasedEntity();

      $order_type_id = $this->orderTypeResolver->resolve($order_item);
      $store = $this->selectStore($purchased_entity);
      $cart = $this->cartProvider->getCart($order_type_id, $store);
      if (!$cart) {
        $cart = $this->cartProvider->createCart($order_type_id, $store);
      }

      $this->cartManager->addOrderItem($cart, $order_item, FALSE, TRUE);
    }
    else if ($form_state->getValue('sku-generation') === 'byOption') {

      $all_fields = $form_state->getCompleteForm();
      foreach ($all_fields['options'] as $field) {
        if (is_array($field) && $field['#attributes']['data-sku-generation'] && $field['#checked']) {
          $storage = $this->entityTypeManager
            ->getStorage('commerce_product_variation');
          $entity_id = $storage
            ->getQuery()
            ->condition('sku', $field['#attributes']['data-sku'])
            ->execute();
          $purchased_entity = $storage->load(reset($entity_id));

          $store = $this->selectStore($purchased_entity);
          $order_item = $this->cartManager->createOrderItem($purchased_entity);
          $options['Option'] = $field['#title']->getUntranslatedString();
          $order_item->setData('product_option', $options);
          $order_type_id = $this->orderTypeResolver->resolve($order_item);
          $cart = $this->cartProvider->getCart($order_type_id, $store);
          if (!$cart) {
            $cart = $this->cartProvider->createCart($order_type_id, $store);
          }

          $this->cartManager->addOrderItem($cart, $order_item, FALSE, TRUE);
        }
      }
    }
    else {
      /** @var \Drupal\commerce\PurchasableEntityInterface $purchased_entity */
      $purchased_entity = $order_item->getPurchasedEntity();

      $order_type_id = $this->orderTypeResolver->resolve($order_item);
      $store = $this->selectStore($purchased_entity);
      $cart = $this->cartProvider->getCart($order_type_id, $store);
      if (!$cart) {
        $cart = $this->cartProvider->createCart($order_type_id, $store);
      }
      $this->entity = $this->cartManager->addOrderItem($cart, $order_item, $form_state->get(['settings', 'combine']));
    }

    // Other submit handlers might need the cart ID.
    $form_state->set('cart_id', $cart->id());
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    if ($form_state->getValue('sku-generation') === 'bySegment') {

      $select_fields = [];
      $selected_options = $form_state->getValues();
      $purchased_entity_sku = $form_state->getValue('base-sku');
      $all_fields = $form_state->getCompleteForm();
      foreach ($all_fields['options'] as $field) {
        if ($field['#type'] === 'select' && $field['#attributes']['data-sku-generation'][0] === 'Yes') {
          $select_fields[] = $field['#name'];
          $purchased_entity_sku .= '-' . $selected_options[$field['#name']];
        }
      }

      if (!empty($select_fields) > 0) {
        $storage = $this->entityTypeManager
          ->getStorage('commerce_product_variation');
        $entity_id = $storage
          ->getQuery()
          ->condition('sku', $purchased_entity_sku)
          ->execute();
        $purchased_entity = $storage->load(reset($entity_id));
        $entity->set('purchased_entity', $purchased_entity);
      }
      else {
        $purchased_entity = $entity->getPurchasedEntity();
      }

      // Now that the purchased entity is set, populate the title and price.
      $entity->setTitle($purchased_entity->getOrderItemTitle());
      if (!$entity->isUnitPriceOverridden()) {
        $store = $this->selectStore($purchased_entity);
        $context = new Context($this->currentUser, $store);
        $resolved_price = $this->chainPriceResolver->resolve($purchased_entity, $entity->getQuantity(), $context);
        $entity->setUnitPrice($resolved_price);
      }
    }

    return $entity;
  }

  /**
   * Selects the store for the given purchasable entity.
   *
   * If the entity is sold from one store, then that store is selected.
   * If the entity is sold from multiple stores, and the current store is
   * one of them, then that store is selected.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $entity
   *   The entity being added to cart.
   *
   * @throws \Exception
   *   When the entity can't be purchased from the current store.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface
   *   The selected store.
   */
  protected function selectStore(PurchasableEntityInterface $entity) {
    $stores = $entity->getStores();
    if (count($stores) === 1) {
      $store = reset($stores);
    }
    elseif (count($stores) === 0) {
      // Malformed entity.
      throw new \Exception('The given entity is not assigned to any store.');
    }
    else {
      $store = $this->currentStore->getStore();
      if (!in_array($store, $stores)) {
        // Indicates that the site listings are not filtered properly.
        throw new \Exception("The given entity can't be purchased from the current store.");
      }
    }

    return $store;
  }

  private function buildOptions(FormStateInterface $form_state) {

    $options = [];
    $all_fields = $form_state->getCompleteForm();
    $field_keys = array_intersect_key($form_state->getValues(), $all_fields['options']);

    foreach ($all_fields['options'] as $field) {
      if (array_key_exists($field['#name'], $field_keys)) {
        $title = $field['#title']->getUntranslatedString();
        switch ($field['#type']) {
          case 'select':
            $options[$title] = $field['#options'][$field['#value']];
            break;
          case 'textfield':
            $options[$title] = $field['#value'];
            break;
          case 'checkbox':
            if ($form_state->getValue('sku-generation') === 'bySegment' ||
                 ($form_state->getValue('sku-generation') === 'byOption' && empty($field['#attributes']['data-sku-generation']))) {
              $options[$title] = $field['#value'] ? 'Yes': 'No';
            }
            break;
        }
      }
    }

    return $options;
  }

}
