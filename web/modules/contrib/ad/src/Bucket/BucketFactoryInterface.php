<?php

namespace Drupal\ad\Bucket;

use Drupal\ad\AdFactoryInterface;

/**
 * Common interface for AD bucket factories.
 */
interface BucketFactoryInterface extends AdFactoryInterface {

  /**
   * Returns the specified AD bucket.
   *
   * @param string $id
   *   The bucket machine name.
   *
   * @return \Drupal\ad\Bucket\BucketInterface
   *   An AD bucket instance.
   */
  public function get(string $id): BucketInterface;

}
