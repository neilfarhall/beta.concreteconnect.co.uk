<?php

namespace Drupal\ad_track;

use Drupal\ad\AdInterface;

/**
 * Common interface for totals storage backends.
 */
interface TotalStorageInterface {

  /**
   * Loads the totals for the specified AD.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   An AD object.
   *
   * @return int[]
   *   An associative array of event totals keyed by event type.
   */
  public function loadTotals(AdInterface $ad): array;

  /**
   * Increases the total impression count for the specified AD.
   *
   * Always start a transaction before performing this.
   *
   * @param string $type
   *   The event type.
   * @param \Drupal\ad\AdInterface $ad
   *   An AD object.
   *
   * @see TotalStorageInterface::startTransaction()
   * @see TotalStorageInterface::rollbackTransaction()
   *
   * @throws \LogicException
   *   If there was an error while updating totals.
   */
  public function increaseTotal(string $type, AdInterface $ad): void;

  /**
   * Starts a transaction before increasing totals for the specified AD.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   An AD object.
   *
   * @throws \InvalidArgumentException
   *   If a transaction for the specified AD was already started, nested
   *   transactions are not supported.
   */
  public function startTransaction(AdInterface $ad): void;

  /**
   * Rolls back a transaction the specified AD.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   An AD object.
   */
  public function rollbackTransaction(AdInterface $ad): void;

}
