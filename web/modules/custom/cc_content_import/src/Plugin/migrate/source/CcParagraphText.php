<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Text paragraphs.
 *
 * @MigrateSource(
 *   id = "cc_paragraph_text"
 * )
 */
class CcParagraphText extends SqlBase {

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

    $query->addField('t', 'field_text_value', 'field_text_value');
    $query->addField('t', 'field_text_format', 'field_text_format');

    $query->leftJoin('paragraph_revision__field_text', 't', 'p.revision_id = t.revision_id');

    $query->condition('p.type', 'text');

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
      'field_text_value' => $this->t('Text value'),
      'field_text_format' => $this->t('Text format'),
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
    return parent::prepareRow($row);
  }

}
