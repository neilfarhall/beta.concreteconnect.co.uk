<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Links paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_links"
 * )
 */
class CcParagraphLinks extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('paragraphs_item_field_data', 'p')
      ->fields('p', [
        'id',
        'revision_id',
        'langcode',
        'status',
        'created',
        'parent_id',
        'parent_type',
        'parent_field_name',
      ]);

    $query->condition('p.type', 'links');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'id' => $this->t('Paragraph ID'),
      'revision_id' => $this->t('Paragraph revision ID'),
      'langcode' => $this->t('Language'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created'),
      'parent_id' => $this->t('Parent ID'),
      'parent_type' => $this->t('Parent type'),
      'parent_field_name' => $this->t('Parent field name'),
      'field_links' => $this->t('Links'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'revision_id' => [
        'type' => 'integer',
        'alias' => 'p',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $revision_id = $row->getSourceProperty('revision_id');

    $links = [];

    $query = $this->select('paragraph_revision__field_links', 'l')
      ->fields('l', [
        'delta',
        'field_links_uri',
        'field_links_title',
      ])
      ->condition('revision_id', $revision_id)
      ->condition('deleted', 0)
      ->orderBy('delta', 'ASC');

    $results = $query->execute()->fetchAll();

    foreach ($results as $record) {
      $record = (array) $record;

      $links[] = [
        'uri' => $record['field_links_uri'] ?? NULL,
        'title' => $record['field_links_title'] ?? NULL,
        'delta' => $record['delta'] ?? 0,
      ];
    }

    $row->setSourceProperty('field_links', $links);

    return parent::prepareRow($row);
  }

}
