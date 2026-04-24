<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for Insights (article) content.
 *
 * @MigrateSource(
 *   id = "cc_article"
 * )
 */
class CcArticle extends SqlBase {

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

    $query->leftJoin('node__field_email', 'e', 'n.nid = e.entity_id');
    $query->addField('e', 'field_email_value', 'field_email_value');

    $query->leftJoin('node__field_featured_article', 'fa', 'n.nid = fa.entity_id');
    $query->addField('fa', 'field_featured_article_value', 'field_featured_article_value');

    $query->leftJoin('node__field_omit_from_news_listing', 'om', 'n.nid = om.entity_id');
    $query->addField('om', 'field_omit_from_news_listing_value', 'field_omit_from_news_listing_value');

    $query->leftJoin('node__field_teaser_media', 'tm', 'n.nid = tm.entity_id');
    $query->addField('tm', 'field_teaser_media_target_id', 'field_teaser_media_target_id');

    $query->leftJoin('node__field_teaser_text', 'tt', 'n.nid = tt.entity_id');
    $query->addField('tt', 'field_teaser_text_value', 'field_teaser_text_value');

    $query->condition('n.type', 'article');

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
      'field_email_value' => $this->t('Email'),
      'field_featured_article_value' => $this->t('Featured article'),
      'field_omit_from_news_listing_value' => $this->t('Omit from news listing'),
      'field_teaser_media_target_id' => $this->t('Teaser media'),
      'field_teaser_text_value' => $this->t('Teaser text'),
      'field_tags' => $this->t('Tag references'),
      'field_paragraphs' => $this->t('Paragraph references'),
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

    $tags = [];
    $tags_query = $this->select('node__field_tags', 't')
      ->fields('t', [
        'delta',
        'field_tags_target_id',
      ])
      ->condition('entity_id', $nid)
      ->condition('deleted', 0)
      ->orderBy('delta', 'ASC');

    $tag_results = $tags_query->execute()->fetchAll();
    foreach ($tag_results as $record) {
      $record = (array) $record;
      $tags[] = [
        'target_id' => $record['field_tags_target_id'] ?? NULL,
        'delta' => $record['delta'] ?? 0,
      ];
    }
    $row->setSourceProperty('field_tags', $tags);

    $paragraphs = [];
    $paragraphs_query = $this->select('node__field_paragraphs', 'p')
      ->fields('p', [
        'delta',
        'field_paragraphs_target_id',
        'field_paragraphs_target_revision_id',
      ])
      ->condition('entity_id', $nid)
      ->condition('deleted', 0)
      ->orderBy('delta', 'ASC');

    $paragraph_results = $paragraphs_query->execute()->fetchAll();
    foreach ($paragraph_results as $record) {
      $record = (array) $record;
      $paragraphs[] = [
        'target_id' => $record['field_paragraphs_target_id'] ?? NULL,
        'target_revision_id' => $record['field_paragraphs_target_revision_id'] ?? NULL,
        'delta' => $record['delta'] ?? 0,
      ];
    }
    $row->setSourceProperty('field_paragraphs', $paragraphs);

    return parent::prepareRow($row);
  }

}
