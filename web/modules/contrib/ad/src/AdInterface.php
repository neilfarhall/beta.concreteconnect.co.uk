<?php

namespace Drupal\ad;

use Drupal\Core\Url;

/**
 * Common interface for AD domain objects.
 */
interface AdInterface {

  /**
   * Returns the AD identifier.
   *
   * @return string
   *   An string uniquely identifying the AD.
   */
  public function getAdIdentifier(): string;

  /**
   * Returns the ID of the bucket providing the AD.
   *
   * @return string
   *   A bucket machine name.
   */
  public function getBucketId(): string;

  /**
   * Returns the AD size.
   *
   * @return string
   *   The AD size ID.
   */
  public function getSizeId(): string;

  /**
   * Returns the target URL.
   *
   * @return \Drupal\Core\Url|null
   *   A URL object or NULL if none is defined.
   */
  public function getTargetUrl(): ?Url;

}
