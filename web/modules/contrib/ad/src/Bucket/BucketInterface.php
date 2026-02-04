<?php

namespace Drupal\ad\Bucket;

use Drupal\ad\AdInterface;
use Drupal\ad\Size\SizeInterface;
use Drupal\ad\Track\TrackerInterface;

/**
 * Common interface for AD buckets.
 */
interface BucketInterface {

  /**
   * Builds an AD placeholder of the specified size.
   *
   * @param \Drupal\ad\Size\SizeInterface $size
   *   An AD size.
   *
   * @return array
   *   A renderable AD placeholder array.
   */
  public function buildPlaceholder(SizeInterface $size): array;

  /**
   * Builds an AD of the specified size.
   *
   * @param \Drupal\ad\Size\SizeInterface $size
   *   An AD size.
   *
   * @return array
   *   An AD render array.
   */
  public function buildAd(SizeInterface $size): array;

  /**
   * Returns the specified AD.
   *
   * @param string $id
   *   The AD identifier.
   *
   * @return \Drupal\ad\AdInterface|null
   *   An AD instance or NULL if none could be found.
   */
  public function getAd(string $id): ?AdInterface;

  /**
   * Returns the bucket's AD tracker.
   *
   * @return \Drupal\ad\Track\TrackerInterface
   *   A tracker instance.
   */
  public function getTracker(): TrackerInterface;

}
