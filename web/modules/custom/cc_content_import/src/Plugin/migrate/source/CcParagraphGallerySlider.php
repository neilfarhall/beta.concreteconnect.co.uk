<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Gallery Slider paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_gallery_slider"
 * )
 */
class CcParagraphGallerySlider extends SqlBase {

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

    $query->leftJoin('paragraph_revision__field_title', 't', 'p.revision_id = t.revision_id');
    $query->addField('t', 'field_title_value', 'field_title_value');

    $query->condition('p.type', 'gallery_slider');

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
      'field_title_value' => $this->t('Title'),
      'field_media' => $this->t('Media references'),
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

    $media = [];

    $query = $this->select('paragraph_revision__field_media', 'm')
      ->fields('m', [
        'delta',
        'field_media_target_id',
      ])
      ->condition('revision_id', $revision_id)
      ->condition('deleted', 0)
      ->orderBy('delta', 'ASC');

    $results = $query->execute()->fetchAll();

    foreach ($results as $record) {
      $record = (array) $record;

      $media[] = [
        'target_id' => $record['field_media_target_id'] ?? NULL,
        'delta' => $record['delta'] ?? 0,
      ];
    }

    $row->setSourceProperty('field_media', $media);

    return parent::prepareRow($row);
  }

}
