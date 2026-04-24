<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Block Reference paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_block_reference"
 * )
 */
class CcParagraphBlockReference extends SqlBase {

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

    $query->leftJoin('paragraph_revision__field_block_reference', 'b', 'p.revision_id = b.revision_id');
    $query->addField('b', 'field_block_reference_plugin_id', 'field_block_reference_plugin_id');
    $query->addField('b', 'field_block_reference_settings', 'field_block_reference_settings');

    $query->condition('p.type', 'block_reference');

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
      'field_block_reference_plugin_id' => $this->t('Block plugin ID'),
      'field_block_reference_settings' => $this->t('Block settings'),
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
