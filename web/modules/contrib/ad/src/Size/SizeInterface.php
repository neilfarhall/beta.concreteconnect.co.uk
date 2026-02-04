<?php

namespace Drupal\ad\Size;

/**
 * Common interface for AD sizes.
 */
interface SizeInterface {

  /**
   * The AD size ID.
   */
  public function getId(): string;

  /**
   * The AD size human-readable label.
   */
  public function getLabel(): string;

}
