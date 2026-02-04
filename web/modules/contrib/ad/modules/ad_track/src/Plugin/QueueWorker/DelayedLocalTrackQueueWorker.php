<?php

namespace Drupal\ad_track\Plugin\QueueWorker;

use Drupal\ad\Bucket\BucketFactoryInterface;
use Drupal\ad\Track\TrackerFactoryInterface;
use Drupal\ad_track\Plugin\Ad\Track\DelayedLocalTracker;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "ad_track_queue",
 *   title = @Translation("Delayed local tracker queue worker"),
 *   cron = {
 *     "time" = 120
 *   }
 * )
 */
class DelayedLocalTrackQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The AD bucket factory.
   *
   * @var \Drupal\ad\Bucket\BucketFactoryInterface
   */
  protected BucketFactoryInterface $bucketFactory;

  /**
   * The AD tracker factory.
   *
   * @var \Drupal\ad\Track\TrackerFactoryInterface
   */
  protected TrackerFactoryInterface $trackerFactory;

  /**
   * DelayedLocalTrackQueueWorker constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ad\Bucket\BucketFactoryInterface $bucket_factory
   *   The AD bucket factory.
   * @param \Drupal\ad\Track\TrackerFactoryInterface $tracker_factory
   *   The AD tracker factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    BucketFactoryInterface $bucket_factory,
    TrackerFactoryInterface $tracker_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->bucketFactory = $bucket_factory;
    $this->trackerFactory = $tracker_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ad.bucket_factory'),
      $container->get('ad.tracker_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $bucket = $this->bucketFactory->get($data['bucket_id']);
    $ad = $bucket->getAd($data['ad_id']);
    if ($ad) {
      /** @var \Drupal\ad_track\Plugin\Ad\Track\DelayedLocalTracker $tracker */
      $tracker = $this->trackerFactory->get(DelayedLocalTracker::TRACKER_ID);
      $tracker->doDelayedSave($ad, $data['values']);
    }
  }

}
