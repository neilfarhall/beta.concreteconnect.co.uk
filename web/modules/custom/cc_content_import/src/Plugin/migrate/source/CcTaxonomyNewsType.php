<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for News type taxonomy terms.
 *
 * @MigrateSource(
 *   id = "cc_taxonomy_news_type"
 * )
 */
class CcTaxonomyNewsType extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('taxonomy_term_field_data', 't')
      ->fields('t', [
        'tid',
        'vid',
        'langcode',
        'name',
        'description__value',
        'description__format',
        'weight',
        'changed',
      ]);

    $query->condition('t.vid', 'news_type');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tid' => $this->t('Term ID'),
      'vid' => $this->t('Vocabulary'),
      'langcode' => $this->t('Language'),
      'name' => $this->t('Name'),
      'description__value' => $this->t('Description'),
      'description__format' => $this->t('Description format'),
      'weight' => $this->t('Weight'),
      'changed' => $this->t('Changed'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 't',
      ],
    ];
  }

}
