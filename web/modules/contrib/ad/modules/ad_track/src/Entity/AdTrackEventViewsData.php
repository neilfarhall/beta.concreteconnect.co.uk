<?php

namespace Drupal\ad_track\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;

/**
 * Entity views data for the AD Track Event entity type.
 */
class AdTrackEventViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    if ($this->moduleHandler->moduleExists('ad_content')) {
      $data['ad_track_event']['ad_id']['relationship'] = [
        'label' => new TranslatableMarkup('Content AD'),
        'base' => 'ad_content',
        'base field' => 'uuid',
        'id' => 'standard',
      ];
    }

    return $data;
  }

}
