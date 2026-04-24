<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Video paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_video"
 * )
 */
class CcParagraphVideo extends SqlBase {

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

    $query->leftJoin('paragraph_revision__field_video_embed', 'v', 'p.revision_id = v.revision_id');
    $query->addField('v', 'field_video_embed_value', 'field_video_embed_value');

    $query->leftJoin('paragraph_revision__field_video_caption', 'c', 'p.revision_id = c.revision_id');
    $query->addField('c', 'field_video_caption_value', 'field_video_caption_value');

    $query->condition('p.type', 'video');

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
      'field_video_embed_value' => $this->t('Video embed'),
      'field_video_caption_value' => $this->t('Video caption'),
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

}
