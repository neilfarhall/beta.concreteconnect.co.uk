<?php

namespace Drupal\unused_modules;

/**
 * Interface for the unused modules service.
 */
interface UnusedModulesHelperServiceInterface {

  /**
   * Returns an array with all available modules.
   *
   * @return \Drupal\unused_modules\UnusedModulesExtensionDecorator[]
   *   The list of available modules.
   */
  public function getModulesByProject();

}
