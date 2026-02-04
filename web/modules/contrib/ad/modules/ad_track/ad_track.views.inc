<?php

/**
 * @file
 * Views integration file for the "AD Track" module.
 */

use Drupal\ad\Track\TrackerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Implements hook_views_data().
 */
function ad_track_views_data() {
  $data = [];

  $data['ad_track_total']['table']['group'] = new TranslatableMarkup('AD Track');
  $data['ad_track_total']['table']['provider'] = 'ad_track';

  $data['ad_track_total']['table']['base'] = [
    'field' => 'ad_id',
    'title' => new TranslatableMarkup('AD Track Totals'),
    'help' => new TranslatableMarkup('Stores AD total event counts.'),
  ];

  $ids = [
    'ad_id' => new TranslatableMarkup('AD ID'),
    'bucket_id' => new TranslatableMarkup('Bucket ID'),
  ];

  foreach ($ids as $field_name => $label) {
    $data['ad_track_total'][$field_name] = [
      'title' => $label,
      'field' => [
        'id' => 'standard',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'string',
      ],
    ];
  }

  $types = [
    TrackerInterface::EVENT_IMPRESSION => new TranslatableMarkup('Total impressions'),
    TrackerInterface::EVENT_CLICK => new TranslatableMarkup('Total clicks'),
  ];

  foreach ($types as $type => $label) {
    $data['ad_track_total'][$type] = [
      'title' => $label,
      'field' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
    ];
  }

  $data['ad_track_total']['click_through'] = [
    'title' => new TranslatableMarkup('Click through'),
    'field' => [
      'id' => 'ad_track_click_through',
    ],
  ];

  if (Drupal::moduleHandler()->moduleExists('ad_content')) {
    $data['ad_track_total']['ad_id']['relationship'] = [
      'label' => new TranslatableMarkup('AD Content'),
      'base' => 'ad_content',
      'base field' => 'uuid',
      'id' => 'standard',
    ];
  }

  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function ad_track_views_data_alter(array &$data) {
  if (Drupal::moduleHandler()->moduleExists('ad_content')) {
    $data['ad_content']['uuid']['relationship'] = [
      'label' => new TranslatableMarkup('AD Track Totals'),
      'base' => 'ad_track_total',
      'base field' => 'ad_id',
      'id' => 'standard',
    ];
  }
}
