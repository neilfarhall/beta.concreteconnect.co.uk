<?php

namespace Drupal\ad\Bucket;

use Drupal\ad\AdFactoryBase;
use Drupal\ad\Plugin\Ad\Track\NullTracker;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The AD bucket factory.
 *
 * @internal
 */
class BucketFactory extends AdFactoryBase implements BucketFactoryInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * BucketFactory constructor.
   *
   * @param \Drupal\ad\Bucket\BucketPluginManager $plugin_manager
   *   The AD bucket plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    BucketPluginManager $plugin_manager,
    ConfigFactoryInterface $config_factory,
    RequestStack $request_stack
  ) {
    $this->pluginManager = $plugin_manager;
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $id): BucketInterface {
    try {
      $config = $this->configFactory->get('ad.settings');
      $tracker_id = $config->get('trackers')[$id] ?? NullTracker::TRACKER_ID;
      $configuration = [
        'ad_context' => $this->requestStack->getCurrentRequest()->query->all(),
        'tracker_id' => $tracker_id,
      ];
      $bucket = $this->pluginManager->createInstance($id, $configuration);
      if ($bucket instanceof BucketInterface) {
        return $bucket;
      }
    }
    catch (PluginException $e) {
    }
    throw new \LogicException('No valid AD bucket found.');
  }

}
