<?php

namespace Drupal\commerce_product_options_availability_checker\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "waitlist_resource",
 *   label = @Translation("Waitlist resource"),
 *   uri_paths = {
 *     "create" = "/commerce-product-options/waitlist"
 *   }
 * )
 */
class WaitlistResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->logger = $container->get('logger.factory')->get('commerce_product_options_availability_checker');
    $instance->currentUser = $container->get('current_user');
    $instance->configFactory = $container->get('config.factory');
    $instance->mailManager = $container->get('plugin.manager.mail');
    $instance->languageManager = $container->get('language_manager');
    return $instance;
  }

  /**
   * Responds to POST requests.
   *
   * @param string $data
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    if (!$this->currentUser->isAuthenticated()) {
      throw new AccessDeniedHttpException();
    }

    $module = 'commerce_product_options';
    $key = 'waitlist_add';

    $to = $this->configFactory->get('system.site')->get('mail');
    $from = $this->configFactory->get('system.site')->get('mail');
    $language_code = $this->languageManager->getDefaultLanguage()->getId();

    $body = $this->currentUser->getAccountName() . ' (' . $this->currentUser->getEmail() . ') with the following details wants to be added to the waitlist.<br/>';
    foreach ($data['details'] as $field => $detail) {
      $body .= $field . ': ' . $detail . '<br/>';
    }

    $params = [
      'user' => $this->currentUser->getAccountName(),
      'product' => $data['product'],
      'body' => $body,
    ];

    $result = $this->mailManager->mail($module, $key, $to, $language_code, $params, $from, TRUE);

    if ($result['result'] == TRUE) {
      return new ModifiedResourceResponse('SUCCESS', 200);
    }
    else {
      return new ModifiedResourceResponse('FAILURE', 300);
    }
  }

}
