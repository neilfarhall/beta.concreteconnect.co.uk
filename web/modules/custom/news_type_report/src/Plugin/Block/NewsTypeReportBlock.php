<?php

namespace Drupal\news_type_report\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a monthly News Type report block.
 *
 * @Block(
 *   id = "news_type_report_block",
 *   admin_label = @Translation("News Type monthly report")
 * )
 */
class NewsTypeReportBlock extends BlockBase {

  public function build() {
    $start = strtotime('first day of this month -11 months 00:00:00');

    $months = [];

    for ($i = 0; $i < 12; $i++) {
      $date = strtotime("-$i months");
      $months[date('Y-m', $date)] = date('M y', $date);
    }

    $results = \Drupal::database()->query("
      SELECT
        nt.field_news_type_target_id AS tid,
        t.name AS news_type,
        DATE_FORMAT(FROM_UNIXTIME(n.created), '%Y-%m') AS month_key,
        COUNT(DISTINCT n.nid) AS article_count
      FROM {node_field_data} n
      INNER JOIN {node__field_news_type} nt
        ON n.nid = nt.entity_id
        AND nt.deleted = 0
      INNER JOIN {taxonomy_term_field_data} t
        ON nt.field_news_type_target_id = t.tid
      WHERE n.type = :type
        AND n.status = 1
        AND n.created >= :start
      GROUP BY tid, news_type, month_key
      ORDER BY news_type ASC, month_key DESC
    ", [
      ':type' => 'news_article',
      ':start' => $start,
    ])->fetchAll();

    $data = [];

    foreach ($results as $row) {
      if (!isset($data[$row->news_type])) {
        $data[$row->news_type] = array_fill_keys(array_keys($months), 0);
      }

      $data[$row->news_type][$row->month_key] = (int) $row->article_count;
    }

    $header = array_merge(['News Type'], array_values($months));
    $rows = [];

    foreach ($data as $news_type => $counts) {
      $rows[] = array_merge([$news_type], array_values($counts));
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['news-type-report-wrapper'],
      ],
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No news articles found for the last 12 months.'),
      ],
      '#cache' => [
        'tags' => ['node_list:news_article', 'taxonomy_term_list'],
      ],
    ];
  }

}
