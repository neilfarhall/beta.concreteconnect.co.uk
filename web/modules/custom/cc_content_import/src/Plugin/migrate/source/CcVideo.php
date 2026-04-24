<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Video content.
 *
 * @MigrateSource(
 *   id = "cc_video"
 * )
 */
class CcVideo extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node_field_data', 'n')
      ->fields('n', [
        'nid',
        'vid',
        'type',
        'langcode',
        'status',
        'uid',
        'title',
        'created',
        'changed',
        'promote',
        'sticky',
      ]);

    $query->leftJoin('node__body', 'b', 'n.nid = b.entity_id');
    $query->addField('b', 'body_value', 'body_value');
    $query->addField('b', 'body_summary', 'body_summary');
    $query->addField('b', 'body_format', 'body_format');

    $query->leftJoin('node__field_teaser_text', 'tt', 'n.nid = tt.entity_id');
    $query->addField('tt', 'field_teaser_text_value', 'field_teaser_text_value');

    $query->leftJoin('node__field_teaser_media', 'tm', 'n.nid = tm.entity_id');
    $query->addField('tm', 'field_teaser_media_target_id', 'field_teaser_media_target_id');

    $query->leftJoin('node__field_video_embed', 've', 'n.nid = ve.entity_id');
    $query->addField('ve', 'field_video_embed_target_id', 'field_video_embed_target_id');

    $query->leftJoin('node__field_video_type', 'vt', 'n.nid = vt.entity_id');
    $query->addField('vt', 'field_video_type_target_id', 'field_video_type_target_id');

    $query->condition('n.type', 'video');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Revision ID'),
      'type' => $this->t('Content type'),
      'langcode' => $this->t('Language'),
      'status' => $this->t('Published status'),
      'uid' => $this->t('Author user ID'),
      'title' => $this->t('Title'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Changed timestamp'),
      'promote' => $this->t('Promoted'),
      'sticky' => $this->t('Sticky'),
      'body_value' => $this->t('Body value'),
      'body_summary' => $this->t('Body summary'),
      'body_format' => $this->t('Body format'),
      'field_teaser_text_value' => $this->t('Teaser text'),
      'field_teaser_media_target_id' => $this->t('Teaser media'),
      'field_video_embed_target_id' => $this->t('Video embed media'),
      'field_video_type_target_id' => $this->t('Video type'),
      'field_tagging_subject' => $this->t('Subject tags'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    $subject_tags = [];
    $query = $this->select('node__field_tagging_subject', 's')
      ->fields('s', [
        'delta',
        'field_tagging_subject_target_id',
      ])
      ->condition('entity_id', $nid)
      ->condition('deleted', 0)
      ->orderBy('delta', 'ASC');

    $results = $query->execute()->fetchAll();

    foreach ($results as $record) {
      $record = (array) $record;
      $subject_tags[] = [
        'target_id' => $record['field_tagging_subject_target_id'] ?? NULL,
        'delta' => $record['delta'] ?? 0,
      ];
    }

    $row->setSourceProperty('field_tagging_subject', $subject_tags);

    return parent::prepareRow($row);
  }

}
