<?php

namespace Drupal\paragraph_blocks\Entity;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Extend the Paragraph entity.
 */
class ParagraphBlocksEntity extends Paragraph {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getSummary(array $options = []) {
    $summary = '';

    $summary_items = $this->getSummaryItems($options);
    if (!empty($summary_items['content'])) {
      foreach ($summary_items['content'] as $item) {
        $summary .= trim(strip_tags(str_replace(["\r", "\n"], " ", $item))) . ' ';
      }
    }

    return Unicode::truncate(html_entity_decode($summary), 100, TRUE, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getSummaryItems(array $options = []): array {
    $summary_items = parent::getSummaryItems($options);
    if ($this->hasAdminTitle()) {
      array_unshift($summary_items['content'], $this->getAdminTitle());
    }
    return $summary_items;
  }

  /**
   * Get the admin title value.
   *
   * @return string
   *   A cleaned up and truncated admin title.
   */
  public function getAdminTitle(): string {
    if (!$this->hasField('admin_title')) {
      return '';
    }
    $text = $this->get('admin_title')->value ?? '';
    return Unicode::truncate(trim(strip_tags($text)), 100);
  }

  /**
   * Paragraph has a non-empty value as admin title.
   *
   * @return bool
   *   TRUE if admin title exists.
   */
  public function hasAdminTitle(): bool {
    return strlen($this->getAdminTitle());
  }

}
