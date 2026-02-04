<?php

namespace Drupal\ad\Track;

use Drupal\ad\AdInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Common interface for AD event trackers.
 */
interface TrackerInterface {

  const EVENT_IMPRESSION = 'impression';
  const EVENT_CLICK = 'click';

  const PLACEHOLDER_IMPRESSION = 'AD_TRACK_IMPRESSION_ID_PLACEHOLDER';

  /**
   * Returns the tracker ID.
   *
   * @return string
   *   A machine name.
   */
  public function id(): string;

  /**
   * Tracks an AD impression.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD to be tracked.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user triggering the AD event.
   * @param array $context
   *
   * @return string|null
   *   The event identifier or NULL if an error occurred.
   */
  public function trackImpression(AdInterface $ad, AccountInterface $user, array $context = []): ?string;

  /**
   * Tracks an AD click.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD to be tracked.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user triggering the AD event.
   * @param array $context
   *
   * @return string|null
   *   The event identifier or NULL if an error occurred.
   */
  public function trackClick(AdInterface $ad, AccountInterface $user, array $context = []): ?string;

}
