<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Media (video).
 *
 * @MigrateSource(
 *   id = "cc_media_video"
 * )
 */
class CcMediaVideo extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('media_field_data', 'm')
      ->fields('m', [
        'mid',
        'vid',
        'bundle',
        'langcode',
        'uid',
        'name',
        'thumbnail__target_id',
        'thumbnail__alt',
        'thumbnail__title',
        'created',
        'changed',
      ]);

    $query->leftJoin('media__field_media_video_embed_field', 'v', 'm.mid = v.entity_id');
    $query->addField('v', 'field_media_video_embed_field_value', 'field_media_video_embed_field_value');

    $query->leftJoin('media__field_local_video', 'lv', 'm.mid = lv.entity_id');
    $query->addField('lv', 'field_local_video_target_id', 'field_local_video_target_id');

    $query->leftJoin('media__field_media_video_file', 'vf', 'm.mid = vf.entity_id');
    $query->addField('vf', 'field_media_video_file_target_id', 'field_media_video_file_target_id');

    $query->leftJoin('media__field_media_video_file_1', 'vf1', 'm.mid = vf1.entity_id');
    $query->addField('vf1', 'field_media_video_file_1_target_id', 'field_media_video_file_1_target_id');

    $query->condition('m.bundle', 'video');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'mid' => $this->t('Media ID'),
      'vid' => $this->t('Revision ID'),
      'bundle' => $this->t('Bundle'),
      'langcode' => $this->t('Language'),
      'uid' => $this->t('User ID'),
      'name' => $this->t('Name'),
      'thumbnail__target_id' => $this->t('Thumbnail file'),
      'thumbnail__alt' => $this->t('Thumbnail alt'),
      'thumbnail__title' => $this->t('Thumbnail title'),
      'created' => $this->t('Created'),
      'changed' => $this->t('Changed'),
      'field_media_video_embed_field_value' => $this->t('Remote video embed'),
      'field_local_video_target_id' => $this->t('Local video'),
      'field_media_video_file_target_id' => $this->t('Media video file'),
      'field_media_video_file_1_target_id' => $this->t('Media video file 1'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'mid' => [
        'type' => 'integer',
        'alias' => 'm',
      ],
    ];
  }

}
