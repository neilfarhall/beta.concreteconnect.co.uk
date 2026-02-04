<?php

namespace Drupal\unused_modules\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drupal\unused_modules\UnusedModulesHelperService;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class UnusedModulesCommands extends DrushCommands {

  /**
   * Unused modules helper service.
   *
   * @var \Drupal\unused_modules\UnusedModulesHelperService
   */
  protected $unusedModulesHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(UnusedModulesHelperService $unusedModulesHelper) {
    parent::__construct();
    $this->unusedModulesHelper = $unusedModulesHelper;
  }

  /**
   * Show unused modules or projects.
   *
   * @param string $type
   *   Options "projects" and "modules". Show modules or projects.
   * @param string $show
   *   Options "all" and "disabled". Show only disabled modules.
   *
   * @usage drush unused-modules projects disabled
   *   Show projects that are unused.
   * @usage drush um
   *   As above, shorthand.
   * @usage drush unused-modules projects disabled
   *   As above, include projects with enabled modules.
   * @usage drush unused-modules modules disabled
   *   Show modules that are unused.
   * @usage drush unused-modules modules all
   *   As above, include enabled modules.
   *
   * @table-style default
   * @field-labels
   *   project: Project
   *   module: Module
   *   enabled: Module enabled
   *   has_modules: Project has Enabled Modules
   *   path: Project Path
   * @default-fields project,module,enabled,has_modules,path
   *
   * @command unused:modules
   * @aliases um,unused_modules,unused-modules
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Rows with unused project/module information.
   */
  public function modules($type = "projects", $show = "disabled") {
    // Print projects.
    if ($type == 'projects') {
      if ($show == 'all') {
        return $this->showProjects('all');
      }
      elseif ($show == 'disabled') {
        return $this->showProjects('disabled');
      }
      else {
        throw new \Exception("unknown 'show' argument " . $show . ". See drush unused-modules --help");
      }
    }
    // Print modules.
    elseif ($type == 'modules') {
      if ($show == 'all') {
        return $this->showModules('all');
      }
      elseif ($show == 'disabled') {
        return $this->showModules('disabled');
      }
      else {
        throw new \Exception("unknown 'show' argument " . $show . ". See drush unused-modules --help");
      }
    }
    else {
      throw new \Exception("unknown 'type' argument " . $type . ". See drush unused-modules --help");
    }
  }

  /**
   * Drush callback.
   *
   * Prints a table with orphaned projects.
   *
   * @param string $op
   *   Either 'all' or 'disabled'.
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Rows with unused project information.
   */
  private function showProjects($op = 'all') {
    $modules = $this->unusedModulesHelper->getModulesByProject();

    $rows = [];
    foreach ($modules as $module) {
      if ($op == 'all') {
        $rows[$module->projectName] = [
          'project' => $module->projectName,
          'has_modules' => $module->projectHasEnabledModules ? dt("Yes") : dt("No"),
          'path' => $module->projectPath,
        ];
      }
      elseif ($op == 'disabled') {
        if (!$module->projectHasEnabledModules) {
          $rows[$module->projectName] = [
            'project' => $module->projectName,
            'has_modules' => $module->projectHasEnabledModules ? dt("Yes") : dt("No"),
            'path' => $module->projectPath,
          ];
        }
      }
    }

    if (!count($rows)) {
      $this->output()->writeln("Hurray, no orphaned projects!");
      return NULL;
    }
    return new RowsOfFields($rows);
  }

  /**
   * Drush callback.
   *
   * Prints a table with orphaned modules.
   *
   * @param string $op
   *   Either 'all' or 'disabled'.
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Rows with unused module information.
   */
  private function showModules($op = 'all') {
    $modules = $this->unusedModulesHelper->getModulesByProject();

    $rows = [];
    foreach ($modules as $module) {
      if ($op == 'all') {
        $rows[$module->getName()] = [
          'project' => $module->projectName,
          'module' => $module->getName(),
          'enabled' => $module->moduleIsEnabled ? dt("Yes") : dt("No"),
          'has_modules' => $module->projectHasEnabledModules ? dt("Yes") : dt("No"),
          'path' => $module->projectPath,
        ];
      }
      elseif ($op == 'disabled') {
        if (!$module->projectHasEnabledModules) {
          $rows[$module->getName()] = [
            'project' => $module->projectName,
            'module' => $module->getName(),
            'enabled' => $module->moduleIsEnabled ? dt("Yes") : dt("No"),
            'has_modules' => $module->projectHasEnabledModules ? dt("Yes") : dt("No"),
            'path' => $module->projectPath,
          ];
        }
      }
    }

    if (!count($rows)) {
      $this->output()->writeln("Hurray, no orphaned modules!");
      return NULL;
    }
    return new RowsOfFields($rows);
  }

}
