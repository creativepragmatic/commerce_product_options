<?php

namespace Drupal\commerce_product_options\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\commerce\Context;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\commerce_order\Resolver\OrderTypeResolverInterface;
use Drupal\commerce_price\Resolver\ChainPriceResolverInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an order item add to cart form with product options.
 */
class ProductOptionsAddToCartForm extends AddToCartForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new ProductOptionsAddToCartForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
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
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, OrderTypeResolverInterface $order_type_resolver, CurrentStoreInterface $current_store, ChainPriceResolverInterface $chain_price_resolver, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, CurrentPathStack $current_path, AliasManagerInterface $alias_manager, MessengerInterface $messenger) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time, $cart_manager, $cart_provider, $order_type_resolver, $current_store, $chain_price_resolver, $current_user);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_order.chain_order_type_resolver'),
      $container->get('commerce_store.current_store'),
      $container->get('commerce_price.chain_price_resolver'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('path.current'),
      $container->get('path.alias_manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $driver = User::load($this->currentUser->id());
    $product_id = $this->entity->getPurchasedEntity()->getProductId();
    $storage = $this->entityManager->getStorage('commerce_product');
    $product = $storage->load($product_id);

    if (!$product->get('options')->isEmpty()) {

      $options = $product->get('options')->getValue()[0]['fields'];
      $base_sku = $product->get('options')->getValue()[0]['base_sku'];
      $sku_generation = $product->get('options')->getValue()[0]['sku_generation'];

      $form['base-sku'] = [
        '#type' => 'hidden',
        '#value' => $base_sku,
        '#attributes' => [
          'id' => 'base-sku',
        ],
      ];

      $form['product-name'] = [
        '#type' => 'hidden',
        '#value' => $product->label(),
        '#attributes' => [
          'id' => 'product-name',
        ],
      ];

      $form['sku-generation'] = [
        '#type' => 'hidden',
        '#value' => $sku_generation,
        '#attributes' => [
          'id' => 'sku-generation',
        ],
      ];

      if ($this->currentUser->isAuthenticated()) {

        $form['form-instructions'] = [
          '#type' => 'markup',
          '#markup' => '<p id="p-fill-form">Fill out this form to register</p>',
        ];

        foreach ($options as $option) {

          $machine_name_title = preg_replace('@[^a-z0-9-]+@', '_', strtolower($option['title']));

          if (strcmp($machine_name_title, 'driver_vehicle') === 0 &&
              strcmp($option['type'], 'select') === 0) {

            $current_alias = substr($this->aliasManager->getAliasByPath($this->currentPath->getPath()), 1);

            $form['options']['driver_vehicle'] = [
              '#type' => 'container',
              '#name' => 'driver_vehicle',
              '#title' => $this->t('Driver Vehicle'),
            ];

            $form['options']['driver_vehicle'][$machine_name_title] = [
              '#type' => $option['type'],
              '#title' => $option['title'],
              '#options' => $this->getVehicles(),
              '#required' => $option['required'] ? TRUE : FALSE,
            ];

            $form['options']['driver_vehicle']['add'] = [
              '#type' => 'link',
              '#title' => $this->t('Add'),
              '#attributes' => [
                'class' => ['use-ajax'],
                'data-dialog-type' => 'dialog',
                'data-dialog-renderer' => 'off_canvas',
                'data-dialog-options' => Json::encode([
                  'title' => 'Add vehicle',
                  'width' => 240,
                ]),
                'id' => 'add-vehicle',
              ],
              '#url' => Url::fromRoute('entity.vehicle.add_form', [
                'user' => $this->currentUser->id(),
                'profile_type' => 'garage',
                'destination' => $current_alias,
              ]),
            ];
          }
          else {

            $form['options'][$machine_name_title] = [
              '#type' => $option['type'],
              '#title' => $this->t($option['title']),
              '#required' => $option['required'] ? TRUE : FALSE,
            ];

            if (strcmp($option['type'], 'textfield') === 0) {

              if (strcmp($machine_name_title, 'driver_s_first_name') === 0 &&
                  !empty($driver->get('field_first_name')->value)) {
                $form['options'][$machine_name_title]['#default_value'] = $driver->get('field_first_name')->value;
              }

              if (strcmp($machine_name_title, 'driver_s_last_name') === 0 &&
                  !empty($driver->get('field_last_name')->value)) {
                $form['options'][$machine_name_title]['#default_value'] = $driver->get('field_last_name')->value;
              }
            }
            elseif (strcmp($option['type'], 'checkbox') === 0) {

              if ($option['mandatoryOption']) {
                $form['options'][$machine_name_title]['#default_value'] = TRUE;
                $form['options'][$machine_name_title]['#disabled'] = TRUE;
              }

              if (!empty($option['priceModifier'])) {
                $title = $option['title'] . ' +$' . number_format($option['priceModifier'], 2);
                $form['options'][$machine_name_title]['#title'] = $title;
              }

              if ($sku_generation === 'byOption' && !empty($option['skuGeneration'])) {
                $form['options'][$machine_name_title]['#attributes'] = [
                  'data-sku-generation' => 1,
                  'data-sku' => $base_sku . '-' . $option['skuSegment'],
                ];
              }
            }
            elseif (strcmp($option['type'], 'select') === 0) {

              $sku_generation = !empty($option['skuGeneration']) ? 'Yes' : 'No';
              $form['options'][$machine_name_title]['#attributes'] = [
                'data-sku-generation' => [$sku_generation],
              ];
            }
            elseif (strcmp($option['type'], 'add-on') === 0) {

              $add_on_options = [];
              $sku_generation = 'No';
              $addOn = Product::load($option['addOnId']);

              $form['options'][$machine_name_title]['#type'] = 'select';

              if (!empty($option['emptyText'])) {
                $form['options'][$machine_name_title]['#empty_value'] = '';
                $form['options'][$machine_name_title]['#empty_option'] = $option['emptyText'];
              }

              $form['options'][$machine_name_title]['#attributes'] = [
                'data-sku-generation' => [$sku_generation],
                'data-add-on' => 'Yes',
              ];

              foreach ($addOn->getVariations() as $add_on_option) {
                if ($add_on_option->isPublished()) {
                  $add_on_options[$add_on_option->getSku()] = $add_on_option->label() . ', +$' . number_format($add_on_option->getPrice()->getNumber(), 2);
                }
              }

              $form['options'][$machine_name_title]['#options'] = $add_on_options;

              if (empty(array_intersect($option['requiredRoles'], $driver->getRoles()))) {
                $form['options'][$machine_name_title]['#disabled'] = TRUE;
              }
            }

            if (!empty($option['helpText'])) {
              $form['options'][$machine_name_title]['#description'] = $option['helpText'];
            }

            if (!empty($option['size'])) {
              $form['options'][$machine_name_title]['#size'] = $option['size'];
            }

            if (!empty($option['options'])) {

              if (strcmp($machine_name_title, 'driver_class') === 0 || strcmp($machine_name_title, 'run_group') === 0) {
                $option['options'] = $this->filterDriverClasses($option['options'], $driver->get('field_driver_class')->value);
              }

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

        $form['wait-list-add'] = [
          '#type' => 'link',
          '#title' => $this->t('Add me to the wait list'),
          '#url' => Url::fromRoute('entity.commerce_product.canonical', [
            'commerce_product' => $product_id,
          ]),
          '#attributes' => [
            'id' => 'wait-list-add',
            'style' => 'display:none;text-align:center;',
          ],
          '#suffix' => '<br/>',
          '#weight' => 100,
        ];

        $form['wait-list-block'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'wait-list-block',
            'style' => 'display:none;',
          ],
          '#weight' => 110,
        ];

        $form['wait-list-block']['message'] = [
          '#type' => 'markup',
          '#markup' => "<p>You've been added to the wait list. This run group may be at capacity but additional spaces may be released. We will contact you with availability.</p>",
        ];

        $form['wait-list-block']['wait-list-close'] = array(
          '#type' => 'button',
          '#value' => $this->t('Close'),
          '#attributes' => [
            'id' => 'wait-list-close',
          ],
        );
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

    if ($this->currentUser->isAuthenticated()) {
      $actions['register'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add to cart'),
        '#submit' => ['::submitForm'],
      ];
    }
    else {
      $actions['login'] = [
        '#type' => 'submit',
        '#value' => $this->t('Log in to register'),
        '#submit' => ['::loginRegisterForm'],
      ];
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $this->entity;

    $combine = $form_state->get(['settings', 'combine']);
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
    elseif ($form_state->getValue('sku-generation') === 'byOption') {
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

    foreach ($form['options'] as $field) {
      if (!empty($field['#type']) && strcmp($field['#type'], 'select') === 0 &&
          !empty($field['#attributes']['data-add-on']) && strcmp($field['#attributes']['data-add-on'], 'Yes') === 0) {

        if (!empty($field['#value'])) {

          $query = $this->entityTypeManager->getStorage('commerce_product_variation');
          $id = $query->getQuery()
            ->condition('sku', $field['#value'])
            ->execute();
          $variation = ProductVariation::load(current($id));

          $add_on_item = $this->cartManager->createOrderItem($variation);
          $store = $this->selectStore($variation);
          $context = new Context($this->currentUser, $store);
          $resolved_price = $this->chainPriceResolver->resolve($variation, 1, $context);
          $add_on_item->setTitle($variation->getOrderItemTitle());
          $add_on_item->setUnitPrice($resolved_price);

          $this->cartManager->addOrderItem($cart, $add_on_item, $combine);
        }
      }
    }

    // Other submit handlers might need the cart ID.
    if (empty($cart)) {
      $this->messenger->addError(t('You have not selected any products to add to your shopping cart.'));
    }
    else {
      $form_state->set('cart_id', $cart->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loginRegisterForm(array &$form, FormStateInterface $form_state) {

    $form_state->setRedirect(
      'user.login',
      [
        'destination' => substr($this->currentPath->getPath(), 1),
      ]
    );
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
        if (isset($field['#type']) && $field['#type'] === 'select' && $field['#attributes']['data-sku-generation'][0] === 'Yes') {
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
   * Builds an array of values from form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An array containing values from options entered in form.
   */
  private function buildOptions(FormStateInterface $form_state) {

    $options = [];
    $all_fields = $form_state->getCompleteForm();
    $field_keys = array_intersect_key($form_state->getValues(), $all_fields['options']);

    foreach ($all_fields['options'] as $field) {

      if (isset($field['#name']) && array_key_exists($field['#name'], $field_keys)) {

        $title = $field['#title']->getUntranslatedString();

        switch ($field['#type']) {

          case 'select':
            if (empty($field['#attributes']['data-add-on'])) {
              $options[$title] = $field['#options'][$field['#value']];
            }
            break;

          case 'textfield':
            $options[$title] = $field['#value'];
            break;

          case 'checkbox':
            if ($form_state->getValue('sku-generation') === 'bySegment' ||
                 ($form_state->getValue('sku-generation') === 'byOption' && empty($field['#attributes']['data-sku-generation']))) {
              $options[$title] = $field['#value'] ? 'Yes' : 'No';
            }
            break;

          case 'container':
            $options[$title] = $field['driver_vehicle']['#options'][$field['driver_vehicle']['#value']];
            break;
        }
      }
    }

    return $options;
  }

  /**
   * Filters out driver classes not available to logged in user.
   *
   * @param array $classes
   *   Array of driver class options.
   * @param string $level
   *   The driver's class.
   *
   * @return array
   *   An array containing filtered driver class options.
   */
  private function filterDriverClasses(array $classes, $level) {

    $filtered_classes = [];

    // TODO: REMEMBER TO REMOVE AFTER 2020 SEASON!!!
    if (strcmp($level, 'NSL') === 0) {
      $level = 'NOV';
    }

    foreach ($classes as $class) {

      array_push($filtered_classes, $class);
      if (strcmp($class['skuSegment'], $level) === 0) {
        break;
      }

      if (empty($level)) {
        break;
      }
    }

    return $filtered_classes;
  }

  /**
   * Gets all cars saved in driver's the garage.
   *
   * @return array
   *   An associative array containing car details.
   */
  private function getVehicles() {

    $garage = [];

    $vehicles = $this->entityTypeManager
      ->getStorage('vehicle')
      ->loadByProperties([
        'uid' => $this->currentUser->id(),
      ]);

    foreach ($vehicles as $vehicle) {

      $details = $vehicle->getColor();
      $details .= ' ' . $vehicle->getYear();
      $details .= ' ' . $vehicle->getMake();
      $details .= ' ' . $vehicle->getModel();

      if (!empty($vehicle->getPermanentNumber())) {
        $details .= ' (#' . $vehicle->getPermanentNumber() . ')';
      }

      $garage[$vehicle->id()] = $details;
    }

    if (!empty($garage)) {
      asort($garage);
    }

    return $garage;
  }

}
