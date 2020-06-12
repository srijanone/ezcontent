<?php

namespace Drupal\ezcontent_jsonapi_role_access\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks subscriptions for user role access for json api API keys.
 */
class CheckUserRolePermissionEvent implements EventSubscriberInterface {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Constructs this factory object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('ezcontent_jsonapi_role_access.settings');
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * ['eventName' => 'methodName']
   *  * ['eventName' => ['methodName', $priority]]
   *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
   *
   * @return array
   *   The event names to listen to.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkUserRoleAccess', 30];
    return $events;
  }

  /**
   * Check if user has subscription, if not redirect to subscription page.
   */
  public function checkUserRoleAccess(Event $event) {
    if (!$event->getRequest()
      ->isXmlHttpRequest() && str_starts_with($event->getRequest()
        ->get('_route'), 'jsonapi')) {
      $actionType = $this->config->get('action_type');
      $roles = $this->config->get('roles');
      $roles = array_filter($roles);
      $userRoles = \Drupal::currentUser()->getRoles();
      if ($actionType && !empty($roles) && !in_array('administrator', $userRoles)) {
        if (($actionType === 'allow' && empty(array_intersect($roles, $userRoles))) || ($actionType === 'restrict' && !empty(array_intersect($roles, $userRoles)))) {
          $response = new Response('Access denied', Response::HTTP_FORBIDDEN);
          $event->setResponse($response);
        }

      }

    }
  }

}
