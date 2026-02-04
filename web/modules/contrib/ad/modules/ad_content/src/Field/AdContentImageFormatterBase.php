<?php

namespace Drupal\ad_content\Field;

use Drupal\ad_content\Entity\AdContentInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;

/**
 * Base class for AD image formatters.
 */
abstract class AdContentImageFormatterBase extends ImageFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    /** @var \Drupal\ad_content\Entity\AdContentInterface $ad_content */
    $ad_content = $items->getEntity();
    $url = $this->getImageUrl($ad_content);

    $files = $this->getEntitiesToView($items, $langcode);
    foreach ($files as $delta => $file) {
      $cache_tags = $file->getCacheTags();

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      assert(isset($file->_referringItem));
      $item = $file->_referringItem;
      $item_attributes = $item->_attributes;
      unset($item->_attributes);

      $elements[$delta] = [
        '#theme' => 'image_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#url' => $url,
        '#cache' => [
          'tags' => $cache_tags,
          'contexts' => ['url.site'],
        ],
      ];
    }

    return $elements;
  }

  /**
   * Returns the URL the image should be linked to.
   *
   * @param \Drupal\ad_content\Entity\AdContentInterface $ad_content
   *   A content AD entity.
   *
   * @return \Drupal\Core\Url|null
   *   A URL object or NULL if none is available.
   */
  protected function getImageUrl(AdContentInterface $ad_content): ?Url {
    $url = $ad_content->getTargetUrl();
    $attributes = $url->getOption('attributes');
    $attributes['target'] = '_blank';
    $attributes['rel'] = 'nofollow';
    $url->setOption('attributes', $attributes);
    return $url;
  }

}
