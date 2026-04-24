<?php

namespace Drupal\unused_modules\Plugin\SiteAuditChecklist;

use Drupal\site_audit\Plugin\SiteAuditChecklistBase;

/**
 * Provides an 'Unused modules' Report.
 *
 * @SiteAuditChecklist(
 *  id = "unused_modules",
 *  name = @Translation("Unused modules")
 * )
 */
class UnusedModulesChecklist extends SiteAuditChecklistBase {}
