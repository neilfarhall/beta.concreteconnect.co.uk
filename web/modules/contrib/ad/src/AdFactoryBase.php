<?php

namespace Drupal\ad;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Base class for AD factories.
 *
 * @internal
 */
abstract class AdFactoryBase implements AdFactoryInterface {

  /**
   * The AD bucket plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected PluginManagerInterface $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function getList(): array {
    $list = [];
    foreach ($this->pluginManager->getDefinitions() as $id => $definition) {
      $list[$id] = $definition['label'] ?? $id;
    }
    asort($list);
    return $list;
  }

}
