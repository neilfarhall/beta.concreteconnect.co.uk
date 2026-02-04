<?php

namespace Drupal\ad_content\Entity;

use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;

/**
 * AD content view controller.
 */
class AdContentViewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $ad_content, $view_mode = 'full') {
    return parent::view($ad_content, $view_mode);
  }

  /**
   * The _title_callback for the page that renders a single node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $ad_content
   *   The current AD content.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $ad_content) {
    return $ad_content->label();
  }

}
