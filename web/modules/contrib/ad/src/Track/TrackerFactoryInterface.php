<?php

namespace Drupal\ad\Track;

use Drupal\ad\AdFactoryInterface;

/**
 * Common interface for AD tracker factories.
 */
interface TrackerFactoryInterface extends AdFactoryInterface {

  /**
   * Returns the specified AD tracker.
   *
   * @param string $name
   *   The tracker machine name.
   *
   * @return \Drupal\ad\Track\TrackerInterface
   *   An AD tracker instance.
   */
  public function get(string $name): TrackerInterface;

}
