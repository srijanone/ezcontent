<?php

namespace Drupal\ezcontent_smart_article\EventSubscriber;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 *
 */
class SmartArticleCheckSubscriptionEvent implements EventSubscriberInterface {

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
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    // Implement getSubscribedEvents() method.
    $events[KernelEvents::REQUEST][] = ['checkSubscription', 30];
    return $events;
  }

  /**
   * Check if user has subscription, if not redirect to subscription page.
   */
  public function checkSubscription(Event $event) {
    if (!$event->getRequest()->isXmlHttpRequest() && $event->getRequest()
      ->getRequestUri() == "/node/add/smart_article") {
      $summary_config = \Drupal::config('summary_generator.settings');
      if (empty($summary_config->get('summary_generator_api_url'))) {
        // @todo: Uncomment the below line to redirect it to subscription page once its design is ready.
        // $event->setResponse(new RedirectResponse(\Drupal::url('ezcontent_smart_article.get_subscription'), 301, []));
      }

    }
  }

}
