<?php

namespace Drupal\ad_content\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * AD content list builder.
 */
class AdContentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => $this->t('ID'),
      'title' => $this->t('Title'),
      'size' => $this->t('Size'),
      'status' => $this->t('Status'),
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $ad_content) {
    /** @var \Drupal\ad_content\Entity\AdContentInterface $ad_content */

    $row = [
      'id' => $ad_content->id(),
      'title' => [
        'data' => [
          '#type' => 'link',
          '#title' => $ad_content->label(),
          '#url' => $ad_content->toUrl(),
        ],
      ],
      'size' => $ad_content->getSizeId(),
      'status' => $ad_content->isPublished(),
    ];

    return $row + parent::buildRow($ad_content);
  }

}
