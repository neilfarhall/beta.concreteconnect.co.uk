<?php

namespace Drupal\ad_content\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * AD content route subscriber.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Move AD settings to a dedicated route to make room for the AD content
    // listing.
    $collection->get('ad.settings')
      ->setPath('/admin/ad/settings');
  }

}
