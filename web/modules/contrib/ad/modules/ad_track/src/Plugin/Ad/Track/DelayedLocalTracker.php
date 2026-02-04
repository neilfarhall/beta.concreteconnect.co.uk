<?php

namespace Drupal\ad_track\Plugin\Ad\Track;

use Drupal\ad\AdInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Plugin(
 *   id = \Drupal\ad_track\Plugin\Ad\Track\DelayedLocalTracker::TRACKER_ID,
 *   label = @Translation("Queue-based local AD event tracker"),
 * )
 *
 * @internal
 */
class DelayedLocalTracker extends LocalTracker {

  const TRACKER_ID = 'delayed_local';

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected QueueFactory $queueFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->queueFactory = $container->get('queue');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function saveEvent(AdInterface $ad, array $values): ?string {
    $this->queueFactory
      ->get('ad_track_queue')
      ->createItem([
        'ad_id' => $ad->getAdIdentifier(),
        'bucket_id' => $ad->getBucketId(),
        'values' => $values,
      ]);

    return $values['uuid'] ?? NULL;
  }

  /**
   * Actually saves the specified AD event.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD to be tracked.
   * @param array $values
   *   The event values to be stored.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   If there was an issue storing the event data.
   */
  public function doDelayedSave(AdInterface $ad, array $values): void {
    if (!isset($values['url']) && !empty($values['parent_id'])) {
      $values += $this->getParentValues($values['parent_id']);
    }
    parent::saveEvent($ad, $values);
  }

}
