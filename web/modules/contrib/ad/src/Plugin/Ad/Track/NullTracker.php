<?php

namespace Drupal\ad\Plugin\Ad\Track;

use Drupal\ad\AdInterface;
use Drupal\ad\Track\TrackerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Null AD tracker, useful as a fallback if no actual tracker is available.
 *
 * @Plugin(
 *   id = \Drupal\ad\Plugin\Ad\Track\NullTracker::TRACKER_ID,
 *   label = @Translation("None"),
 * )
 */
class NullTracker implements TrackerInterface {

  const TRACKER_ID = 'null';

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return static::TRACKER_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function trackImpression(AdInterface $ad, AccountInterface $user, array $context = []): ?string {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function trackClick(AdInterface $ad, AccountInterface $user, array $context = []): ?string {
    return '';
  }

}
