<?php

namespace Drupal\unused_modules\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\unused_modules\UnusedModulesHelperService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Page callbacks.
 */
class UnusedModulesController extends ControllerBase {

  /**
   * The unused modules helper service.
   *
   * @var \Drupal\unused_modules\UnusedModulesHelperService
   */
  protected $helper;

  /**
   * Constructs a new UnusedModulesController object.
   *
   * @param \Drupal\unused_modules\UnusedModulesHelperService $helper
   *   The unused modules helper service.
   */
  public function __construct(UnusedModulesHelperService $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('unused_modules.helper')
    );
  }

  /**
   * Returns a table with orphaned projects.
   *
   * @param string $filter
   *   Either 'all' or 'disabled'.
   *
   * @return array
   *   table render array.
   */
  public function renderProjectsTable($filter) {
    $header = [
      'Project',
      'Project has Enabled Modules',
      'Project Path',
    ];

    $rows = [];
    foreach ($this->helper->getModulesByProject() as $module) {
      if ($filter === 'all') {
        $rows[$module->projectName] = [
          $module->projectName,
          $module->projectHasEnabledModules ? $this->t("Yes") : $this->t("No"),
          $module->projectPath,
        ];
      }
      elseif ($filter === 'disabled') {
        if (!$module->projectHasEnabledModules) {
          $rows[$module->projectName] = [
            $module->projectName,
            $module->projectHasEnabledModules ? $this->t("Yes") : $this->t("No"),
            $module->projectPath,
          ];
        }
      }
    }

    if (!$rows) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t("Hurray, no orphaned projects!"),
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * Returns a table with orphaned modules.
   *
   * @param string $filter
   *   Either 'all' or 'disabled'.
   *
   * @return array
   *   Table render array.
   */
  public function renderModulesTable($filter) {
    $header = [
      'Project',
      'Module',
      'Module enabled',
      'Project has Enabled Modules',
      'Project Path',
    ];

    $rows = [];
    foreach ($this->helper->getModulesByProject() as $module) {
      if ($filter === 'all') {
        $rows[$module->getName()] = [
          $module->projectName,
          $module->getName(),
          $module->moduleIsEnabled ? $this->t("Yes") : $this->t("No"),
          $module->projectHasEnabledModules ? $this->t("Yes") : $this->t("No"),
          $module->projectPath,
        ];
      }
      elseif ($filter === 'disabled') {
        if (!$module->projectHasEnabledModules) {
          $rows[$module->getName()] = [
            $module->projectName,
            $module->getName(),
            $module->moduleIsEnabled ? $this->t("Yes") : $this->t("No"),
            $module->projectHasEnabledModules ? $this->t("Yes") : $this->t("No"),
            $module->projectPath,
          ];
        }
      }
    }

    if (!$rows) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t("Hurray, no orphaned modules!"),
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}
