<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for path aliases.
 *
 * @MigrateSource(
 *   id = "cc_path_alias"
 * )
 */
class CcPathAlias extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('path_alias', 'p')
      ->fields('p', [
        'id',
        'path',
        'alias',
        'langcode',
      ])
      ->condition('status', 1);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('ID'),
      'path' => $this->t('System path'),
      'alias' => $this->t('URL alias'),
      'langcode' => $this->t('Language'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
      ],
    ];
  }

}
