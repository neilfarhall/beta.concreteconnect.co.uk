<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Bullet List paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_bullet_list"
 * )
 */
class CcParagraphBulletList extends SqlBase {

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

    $query->leftJoin('paragraph_revision__field_bullet_text', 'b', 'p.revision_id = b.revision_id');
    $query->addField('b', 'field_bullet_text_value', 'field_bullet_text_value');
    $query->addField('b', 'field_bullet_text_format', 'field_bullet_text_format');

    $query->condition('p.type', 'bullet_list');

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
      'field_bullet_text_value' => $this->t('Bullet text'),
      'field_bullet_text_format' => $this->t('Bullet format'),
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
