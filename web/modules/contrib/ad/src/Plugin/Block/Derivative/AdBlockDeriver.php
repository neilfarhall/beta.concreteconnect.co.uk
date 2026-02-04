<?php

namespace Drupal\ad\Plugin\Block\Derivative;

use Drupal\ad\Size\SizeFactory;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AD block deriver creating a derivative for each AD size.
 */
class AdBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The AD size factory.
   *
   * @var \Drupal\ad\Size\SizeFactory
   */
  protected SizeFactory $sizeFactory;

  /**
   * AdBlockDeriver constructor.
   *
   * @param \Drupal\ad\Size\SizeFactory $ad_size_factory
   *   The AD size factory.
   */
  public function __construct(SizeFactory $ad_size_factory) {
    $this->sizeFactory = $ad_size_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('ad.size_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->sizeFactory->getAll() as $size) {
      $definition = $base_plugin_definition;
      $definition['admin_label'] = $size->getLabel();
      $this->derivatives[$size->getId()] = $definition;
    }

    return $this->derivatives;
  }

}
