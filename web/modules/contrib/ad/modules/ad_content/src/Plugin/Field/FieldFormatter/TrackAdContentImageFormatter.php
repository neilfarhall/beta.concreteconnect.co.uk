<?php

namespace Drupal\ad_content\Plugin\Field\FieldFormatter;

use Drupal\ad\Track\TrackerInterface;
use Drupal\ad_content\Entity\AdContentInterface;
use Drupal\ad_content\Field\AdContentImageFormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the AD image formatter with local tracking.
 *
 * @FieldFormatter(
 *   id = "ad_content_image_click_track",
 *   label = @Translation("AD Image with local click tracking"),
 *   field_types = {
 *     "image"
 *   }
 * )
 *
 * @internal
 */
class TrackAdContentImageFormatter extends AdContentImageFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    // Avoid render caching, otherwise also our placeholder replacement will be
    // cached.
    $elements['#cache']['max-age'] = 0;

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function getImageUrl(AdContentInterface $ad_content): ?Url {
    $url = parent::getImageUrl($ad_content);

    if ($url) {
      $parameters = [
        'bucket_id' => AdContentInterface::BUCKET_ID,
        'ad_id' => $ad_content->getAdIdentifier(),
      ];
      $options = [
        'query' => [
          'parent_id' => TrackerInterface::PLACEHOLDER_IMPRESSION,
        ],
        'attributes' => $url->getOption('attributes'),
      ];
      $url = Url::fromRoute('ad_track.track', $parameters, $options);
    }

    return $url;
  }

}
