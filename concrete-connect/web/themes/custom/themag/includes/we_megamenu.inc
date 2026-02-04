<?php

function themag_preprocess_we_megamenu_row(&$variables) {
  $variables['attributes']
    ->addClass('row')
    ->addClass('g-0');
}

/**
 * Implements template_preprocess_we_megamenu_col
 */
function themag_preprocess_we_megamenu_col(&$variables) {
  if(isset($variables['item_config'])) {
    $col_items = $variables['item_config'];
    $col_config = $col_items->col_config;

    $variables['attributes']
      ->removeClass('span' . $col_config->width)
      ->addClass('col-' . $col_config->width);
  }
}
