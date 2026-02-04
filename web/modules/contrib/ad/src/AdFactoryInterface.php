<?php

namespace Drupal\ad;

/**
 * Common interface for AD factories.
 */
interface AdFactoryInterface {

  /**
   * Returns a list of buckets.
   *
   * @return string[]
   *   An associative array of AD bucket labels keyed by ID.
   */
  public function getList(): array;

}
