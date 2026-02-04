<?php

/**
 * Implements hook_preprocess_html().
 */
function themag_preprocess_html(&$variables) {
  if($variables['themag']['sticky_header']) {
    $variables['attributes']['class'][] = 'themag--has-sticky-header';
  }
}
