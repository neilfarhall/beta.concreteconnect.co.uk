<?php

/**
 * Implements hook_page_attachments_alter().
 */
function themag_st_page_attachments_alter(array &$attachments) {

  /**
   * Attach custom CSS file
   */
  if(theme_get_setting('themag_use_custom_css_file')) {
    $attachments['#attached']['library'][] = 'themag_st/custom-css';
  }

  /**
   * Attach custom JS file
   */
  if(theme_get_setting('themag_use_custom_js_file')) {
    $attachments['#attached']['library'][] = 'themag_st/custom-js';
  }
  $library = 'charm/font-awesome-cdn';
  $library_key = array_search($library, $attachments['#attached']['library']);

  if ($library_key !== FALSE) {
    // Remove the library.
    unset($attachments['#attached']['library'][$library_key]);
  }

  $library = 'charm/font-awesome-4-upgrade-cdn';
  $library_key = array_search($library, $attachments['#attached']['library']);

  if ($library_key !== FALSE) {
    // Remove the library.
    unset($attachments['#attached']['library'][$library_key]);
  }
}
