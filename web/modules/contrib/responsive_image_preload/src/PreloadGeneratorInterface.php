<?php

namespace Drupal\responsive_image_preload;

/**
 * A service that is capable of generating a set of responsive image preloads.
 */
interface PreloadGeneratorInterface {

  /**
   * Generates a set of preloads.
   *
   * @param array $elements
   *   The elements to generate preloads for.
   *
   * @return array
   *   A set of preloads.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   This should never happen under normal circumstances.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   This should never happen under normal circumstances.
   */
  public function generatePreloads(array $elements);

}
