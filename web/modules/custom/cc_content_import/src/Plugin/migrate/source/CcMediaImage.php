<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Media (image).
 *
 * @MigrateSource(
 *   id = "cc_media_image"
 * )
 */
class CcMediaImage extends SqlBase {

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
        'created',
        'changed',
      ]);

    // Join exactly as the source data exists.
    $query->leftJoin('media__field_image', 'i', 'm.mid = i.entity_id');
    $query->addField('i', 'field_image_target_id', 'field_image_target_id');
    $query->addField('i', 'field_image_alt', 'field_image_alt');
    $query->addField('i', 'field_image_title', 'field_image_title');

    $query->leftJoin('media__field_description', 'd', 'm.mid = d.entity_id');
    $query->addField('d', 'field_description_value', 'field_description');

    $query->condition('m.bundle', 'image');

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
      'created' => $this->t('Created'),
      'changed' => $this->t('Changed'),
      'field_image_target_id' => $this->t('File reference'),
      'field_image_alt' => $this->t('Image alt text'),
      'field_image_title' => $this->t('Image title'),
      'field_description' => $this->t('Caption'),
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
