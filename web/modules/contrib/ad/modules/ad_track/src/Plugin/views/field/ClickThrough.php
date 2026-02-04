<?php

namespace Drupal\ad_track\Plugin\views\field;

use Drupal\ad\Track\TrackerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\UncacheableFieldHandlerTrait;
use Drupal\views\ResultRow;

/**
 * Field handler to show a counter of the current row.
 *
 * @ViewsField("ad_track_click_through")
 */
class ClickThrough extends FieldPluginBase {

  use UncacheableFieldHandlerTrait;

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $display = $this->view->getDisplay();

    /** @var \Drupal\views\Plugin\views\field\FieldHandlerInterface $impression_handler */
    $impression_handler = $display->getHandler('field', TrackerInterface::EVENT_IMPRESSION);
    /** @var \Drupal\views\Plugin\views\field\FieldHandlerInterface $click_handler */
    $click_handler = $display->getHandler('field', TrackerInterface::EVENT_CLICK);

    if ($impression_handler && $click_handler) {
      $impressions = (int) $values->{$impression_handler->field_alias};
      $clicks = (int) $values->{$click_handler->field_alias};
      $click_through = $impressions ? ($clicks / $impressions * 100) : 0;
      return new TranslatableMarkup('@percentage%', ['@percentage' => number_format($click_through, 2)]);
    }

    return '';
  }

}
