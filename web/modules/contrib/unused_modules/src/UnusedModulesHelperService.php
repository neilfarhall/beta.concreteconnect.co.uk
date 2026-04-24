<?php

namespace Drupal\unused_modules;

use Composer\InstalledVersions;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;

/**
 * Common Unused Modules functionality.
 */
class UnusedModulesHelperService implements UnusedModulesHelperServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function getModulesByProject() {
    $enabled_modules = static::getEnabledModules();
    $available_modules = static::getAvailableModules();

    // Projects are organized by path.
    // Foreach path check if there are $enabled_modules.
    // If so, project_has_enabled_modules = TRUE.
    foreach ($available_modules as &$available_module) {
      foreach ($enabled_modules as $enabled_module) {
        if (isset($enabled_module)) {
          // Test if there is an enabled module with the same path.
          if ($enabled_module->projectPath === $available_module->projectPath) {
            $available_module->projectHasEnabledModules = TRUE;
          }

          // Test if module is enabled.
          if ($enabled_module->getPathname() === $available_module->getPathname()) {
            $available_module->moduleIsEnabled = TRUE;
          }
        }
      }
    }

    // Sort by project.
    uasort($available_modules, [$this, 'sortByProject']);

    return $available_modules;
  }

  /**
   * Returns an array of available modules.
   */
  private static function getAvailableModules() {
    $available_modules = &drupal_static(__FUNCTION__);

    if (!isset($available_modules)) {
      $listing = new ExtensionDiscovery(\Drupal::root());
      $available_modules = array_map('\Drupal\unused_modules\UnusedModulesHelperService::decorateExtension', $listing->scan('module'));
      // Remove core modules.
      self::removeCoreModules($available_modules);
      // Add information from .info file.
      self::addInfoFileInformation($available_modules);
      // Add project info.
      self::addProjectPath($available_modules);
    }

    return $available_modules;
  }

  /**
   * Returns an array of enabled modules.
   */
  private static function getEnabledModules() {
    // Get all modules available.
    $available_modules = self::getAvailableModules();
    // Get all enabled modules.
    $moduleHandler = \Drupal::moduleHandler();
    $enabled_modules = array_map('\Drupal\unused_modules\UnusedModulesHelperService::decorateExtension', $moduleHandler->getModuleList());

    // Return only enabled.
    $return = [];
    foreach ($enabled_modules as $enabled_module => $extension) {
      // Some enabled_modules are actually not available. This is the case for
      // installation profiles like 'minimal'.
      if (isset($available_modules[$enabled_module])) {
        $return[$enabled_module] = $available_modules[$enabled_module];
      }
    }

    return $return;
  }

  /**
   * Add project path.
   *
   * Group modules by 'project' and extract their lowest common basepath.
   *
   * @param \Drupal\unused_modules\UnusedModulesExtensionDecorator[] $modules
   *   List of modules.
   */
  private static function addProjectPath(&$modules) {

    // Group modules by project.
    $modules_grouped_by_project = [];
    foreach ($modules as $module) {
      $modules_grouped_by_project[$module->projectName][$module->getName()] = $module;
    }

    // Add project_path to module.
    foreach ($modules_grouped_by_project as $project) {

      // Determine common basepath by looking for needle "/<project>/" in uri.
      // As a fallback use the shortest path method.
      foreach ($project as $module) {
        /** @var \Drupal\unused_modules\UnusedModulesExtensionDecorator $module */
        if (!$module->parsingError) {
          $needle = "/" . $module->projectName;
          $before_needle = TRUE;
          $project_path = strstr($module->getSubpath(), $needle, $before_needle) . "/" . $module->projectName;

          $module->projectPath = $project_path;
        }
      }

      // Fallback: determine common basepath by picking the shortest path of all
      // project modules.
      if (!$module->projectPath && !$module->parsingError) {
        $project_paths = [];
        foreach ($project as $module) {
          $project_paths[] = $module->getPath();
        }

        // Get length of each module path in a project.
        $lengths = array_map('\strlen', $project_paths);
        // Sort by value (lowest number first).
        asort($lengths);
        // Get lowest key.
        reset($lengths);
        $key = key($lengths);
        // Shortest path.
        $shortest_path = $project_paths[$key];

        // Add the project_path to each module.
        foreach ($project as $module) {
          $module->projectPath = $shortest_path;
        }
      }

      unset($project_paths);
    }
  }

  /**
   * Remove core modules.
   *
   * @param \Drupal\unused_modules\UnusedModulesExtensionDecorator[] $modules
   *   List of modules.
   */
  private static function removeCoreModules(&$modules) {
    // Removes core modules from the array.
    foreach ($modules as $key => $module) {
      if ($module->getOrigin() === 'core') {
        unset($modules[$key]);
      }
    }
  }

  /**
   * Add module information from <module>.info file.
   *
   * @param \Drupal\unused_modules\UnusedModulesExtensionDecorator[] $modules
   *   List of modules.
   *
   *   Adds properties to $module objects inside $modules:
   *   - project.
   *   - error (default FALSE).
   */
  private static function addInfoFileInformation(&$modules = []) {
    // Prepare a composer package list with the install path.
    $packages = array_combine(InstalledVersions::getInstalledPackages(), InstalledVersions::getInstalledPackages());
    $packages = array_map(function ($package) {
      return realpath(InstalledVersions::getInstallPath($package) ?? '');
    }, $packages);

    // The Drupal packaging script adds project information to the .info file.
    foreach ($modules as $module) {
      try {
        if (!file_exists($module->getPathname())) {
          $error_message = "No .info.yml file found for module '" . $module->getName() . "'";
          throw new UnusedModulesException($error_message);
        }

        // Add the project name to the module object.
        $info_file = Yaml::decode(file_get_contents($module->getPathname()));
        if (!empty($info_file['project'])) {
          $module->projectName = $info_file['project'];
        }

        if (!$module->projectName) {
          $pathname = dirname($module->getPathname());
          // If module's path contains the word custom,
          // assign it to custom project.
          if (strpos(dirname($pathname), 'custom') !== FALSE) {
            $module->projectName = 'custom';
          }
          elseif (class_exists('\Composer\InstalledVersions')) {
            // Check if it is installed with composer.
            // Example: modules/contrib/paragraphs.
            $packageName = array_search(\Drupal::root() . '/' . $pathname, $packages);
            // Check again if it is a submodule of a drupal module.
            // By convention all submodules are places in the modules/* folder
            // of the package.
            // Example: modules/contrib/paragraphs/modules/paragraphs_demo.
            if (!$packageName) {
              $pathname = dirname($pathname, 2);
              $packageName = array_search(\Drupal::root() . '/' . $pathname, $packages);
            }
            // Remove "drupal/" prefix for Drupal modules, because that breaks
            // the table output in the report.
            if (!str_starts_with($packageName, 'drupal/')) {
              $error_message = "Could not parse Composer information for module '" . $module->getName() . "'";
              throw new UnusedModulesException($error_message);
            }
            $module->projectName = str_replace('drupal/', '', $packageName);
          }
        }

        // Throw error if no project information is found.
        // This should only be the case for sandbox modules.
        if (!$module->projectName) {
          $error_message = "No project information found for module '" . $module->getName() . "'";
          throw new UnusedModulesException($error_message);
        }
      }
      catch (UnusedModulesException $e) {
        $module->parsingError = TRUE;
        $module->projectName = "_NO_PROJECT_INFORMATION_";

        // Don't write warnings during site_audit execution.
        $show_warnings = TRUE;
        if (function_exists('drush_get_command')) {
          $command = drush_get_command();
          if ($command['command'] == 'audit_extensions') {
            $show_warnings = FALSE;
          }
        }
        if ($show_warnings) {
          \Drupal::messenger()->addWarning($e->getMessage());
        }
      }
    }
  }

  /**
   * Sort helper. Used as uasort() callback.
   *
   * @return int
   *   The result of comparing two projects.
   */
  private static function sortByProject($a, $b) {

    // Sort by module name if from same project.
    if ($a->projectName === $b->projectName) {
      return strnatcmp($a->getPathname(), $b->getPathname());
    }

    // Fallback sort by project name.
    return strnatcmp($a->projectName, $b->projectName);
  }

  /**
   * Decorate helper. Used as array_map() callback.
   *
   * @param \Drupal\Core\Extension\Extension $extension
   *   The Extension to decorate.
   *
   * @return \Drupal\unused_modules\UnusedModulesExtensionDecorator
   *   Decorated Extension.
   */
  private static function decorateExtension(Extension $extension) {
    return new UnusedModulesExtensionDecorator($extension);
  }

}
