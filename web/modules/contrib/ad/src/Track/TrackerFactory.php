<?php

namespace Drupal\ad\Track;

use Drupal\ad\AdFactoryBase;
use Drupal\ad\Plugin\Ad\Track\NullTracker;
use Drupal\Component\Plugin\Exception\PluginException;

/**
 * The AD tracker factory.
 *
 * @internal
 */
class TrackerFactory extends AdFactoryBase implements TrackerFactoryInterface {

  /**
   * TrackerFactory constructor.
   *
   * @param \Drupal\ad\Track\TrackerPluginManager $plugin_manager
   *   The AD tracker plugin manager.
   */
  public function __construct(TrackerPluginManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $name): TrackerInterface {
    try {
      /** @var \Drupal\ad\Track\TrackerInterface $tracker */
      $tracker = $this->pluginManager->createInstance($name);
      return $tracker;
    }
    catch (PluginException $e) {
      watchdog_exception('ad', $e);
    }
    return $this->get(NullTracker::TRACKER_ID);
  }

  /**
   * {@inheritdoc}
   */
  public function getList(): array {
    $list = parent::getList();
    $list = array_intersect_key($list, [NullTracker::TRACKER_ID => TRUE]) + $list;
    return $list;
  }

}
