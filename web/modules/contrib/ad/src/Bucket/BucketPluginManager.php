<?php

namespace Drupal\ad\Bucket;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * AD bucket plugin manager.
 *
 * @internal
 */
class BucketPluginManager extends DefaultPluginManager {

  /**
   * BucketPluginManager constructor.
   *
   * @see \Drupal\Core\Plugin\DefaultPluginManager::__construct()
   */
  public function __construct(
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
    $plugin_interface = NULL,
    $plugin_definition_annotation_name = Plugin::class,
    array $additional_annotation_namespaces = []
  ) {
    parent::__construct('Plugin/Ad/Bucket', $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name, $additional_annotation_namespaces);
    $this->alterInfo('ad_bucket_info');
  }

}
