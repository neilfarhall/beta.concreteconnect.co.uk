<?php

namespace Drupal\unused_modules\Plugin\SiteAuditCheck;

use Drupal\site_audit\Plugin\SiteAuditCheckBase;

/**
 * Provides the UnusedModulesCheck Check.
 *
 * @SiteAuditCheck(
 *  id = "unused_modules",
 *  name = @Translation("Unused modules status"),
 *  description = @Translation("Check to see if enabled"),
 *  checklist = "unused_modules"
 * )
 */
class UnusedModulesCheck extends SiteAuditCheckBase {

  /**
   * {@inheritdoc}.
   */
  public function getResultFail() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultInfo() {}

  /**
   * {@inheritdoc}.
   */
  public function getResultPass() {
    return $this->t('There is no unused modules.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getResultWarn() {
    return $this->t('There are unused modules.');
  }

  /**
   * {@inheritdoc}.
   */
  public function getAction() {
    if ($this->score == SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN) {
      return $this->t('Review the full report at <b>/admin/config/development/unused_modules/modules/disabled</b> page.');
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function calculateScore() {
    /** @var \Drupal\unused_modules\UnusedModulesHelperServiceInterface $helper */
    $helper = \Drupal::service('unused_modules.helper');

    // Check if there are unused modules that are disabled.
    foreach ($helper->getModulesByProject() as $module) {
      if (!$module->projectHasEnabledModules) {
        return SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN;
      }
    }

    return SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS;
  }

}
