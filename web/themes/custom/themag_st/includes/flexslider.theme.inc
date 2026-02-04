<?php

/**
 * @file
 * Theming functions for the flexslider module.
 *
 * Preprocessor functions fill variables for templates and helper
 * functions to make theming easier.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\flexslider\Entity\Flexslider;
use Drupal\flexslider\FlexsliderDefaults;


function themag_st_theme_suggestions_flexslider_alter(array &$suggestions, array $variables) {

  $suggestions[] = 'flexslider__'   . $variables['flexslider']['settings']['optionset'];
  $suggestions[] = 'flexslider__'   . $variables['flexslider']['settings']['optionset'] . '__' . $variables['flexslider']['settings']['attributes']['id'];
  // this is an odd one;
  // the only info we have here in $variables are $variables[flexslider] _items or _. settings
  // nought though

  return $suggestions;
}

function themag_st_theme_suggestions_flexslider_list_alter(array &$suggestions, array $variables) {

  //  $suggestions[] = 'flexslider__'   . $variables['flexslider']['settings']['optionset'];
  //  $suggestions[] = 'flexslider__'   . $variables['flexslider']['settings']['optionset'] . '__' . $variables['flexslider']['settings']['attributes']['id'];

  $suggestions[] = $variables['theme_hook_original'] . '__' . $variables['settings']['optionset']->id();
  $suggestions[] = $variables['theme_hook_original'] . '__' . $variables['settings']['optionset']->id() . '__' . $variables['settings']['attributes']['id'];




  // this is an odd one;
  // the only info we have here in $variables are $variables[flexslider] _items or _. settings
  // nought though

//  dpm($variables);

  return $suggestions;
}


//
///**
// * Prepares variables for flexslider template.
// *
// * Default template: flexslider.html.twig.
// */
//function themag_st_preprocess_flexslider(&$variables) {
//
//  // Reference configuration variables.
//  $settings = &$variables['flexslider']['settings'];
//  $optionset = &$settings['optionset'];
//  $items = &$variables['flexslider']['items'];
//
//  // Set the default container type.
//  if (empty($settings['type'])) {
//    $settings['type'] = 'ul';
//  }
//
//  // Load the selected optionset.
//  if (!empty($optionset)) {
//    $optionset = Flexslider::load($optionset);
//  }
//
//  // Check if an optionset was loaded.
//  if (is_null($optionset)) {
//    // Fall back to 'default' options.
//    $options = FlexsliderDefaults::defaultOptions();
//    \Drupal::logger('flexslider')->warning('Fallback to default optionset.', []);
//  }
//  else {
//    $options = $optionset->getOptions();
//  }
//
//  // Configure attributes for containing elements.
//  $attributes = $variables['attributes'];
//  // Merge with defined attributes.
//  if (isset($settings['attributes']) && is_array($settings['attributes'])) {
//    $attributes = NestedArray::mergeDeep($attributes, $settings['attributes']);
//  }
//
//  // Set the ID for each flexslider instance if none is provided.
//  if (empty($attributes['id'])) {
//    $flexslider_id = &drupal_static('flexslider_id', 0);
//    $attributes['id'] = 'flexslider-' . ++$flexslider_id;
//  }
//
//  // Add the namespace to any classes.
//  // @todo figure out what this is supposed to do
//  if (!empty($attributes['class']) && !empty($options['namespace'])) {
//    foreach ($attributes['class'] as $key => $value) {
//      $attributes['class'][$key] = $options['namespace'] . $value;
//    }
//  }
//
//  // Add the flexslider class to be namespaced.
//  $attributes['class'][] = 'flexslider';
//
//  // Add the optionset name as a class to the container.
//  $attributes['class'][] = 'optionset-' . Html::getClass($optionset->id());
//
//  // Add the image style name as a class to the container.
//  if (!empty($settings['image_style'])) {
//    $attributes['class'][] = 'imagestyle-' . Html::getClass($settings['image_style']);
//  }
//
//  // Pass attributes to twig.
//  $variables['attributes'] = $attributes;
//
//  // Add the list render array.
//  $variables['content']['list'] = [
//    '#theme' => 'flexslider_list',
//    '#items' => $items,
//    '#settings' => $settings,
//  ];
//
//  // Finally, add the configuration to the page.
//  $attached = flexslider_add($variables['attributes']['id'], $variables['flexslider']['settings']['optionset']);
//  $variables['#attached'] = $attached;
//
//}
//
///**
// * Prepares variables for flexslider list template.
// *
// * Default template: flexslider-list.html.twig.
// */
//function themag_st_preprocess_flexslider_list(&$variables) {
//
//  // Reference configuration variables.
//  $optionset = &$variables['settings']['optionset'];
//  $items = &$variables['items'];
//  $attributes = &$variables['attributes'];
//
//  // @todo find a way to detect the outer container class if possible
//  $attributes['class'][] = 'slides';
//
//  // Build the list.
//  $variables['content']['list'] = [];
//  if (!empty($items)) {
//    foreach ($items as $i => $item) {
//
//      $caption = '';
//      if (!empty($item['caption'])) {
//        $caption = $item['caption'];
//      }
//
//      // Add the list item render array.
//      $variables['content']['list'][$i] = [
//        '#theme' => 'flexslider_list_item',
//        '#item' => $item['slide'],
//        '#settings' => [
//          'optionset' => $optionset,
//        ],
//        '#caption' => $caption,
//      ];
//    }
//  }
//
//}
//
///**
// * Prepares variables for flexslider list item template.
// *
// * Default template: flexslider-list-item.html.twig.
// */
//function themag_st_preprocess_flexslider_list_item(&$variables) {
//
//  // Reference configuration variables.
//  $item = &$variables['item'];
//  $settings = &$variables['settings'];
//  $caption = &$variables['caption'];
//  $attributes = &$variables['attributes'];
//  /** @var \Drupal\flexslider\Entity\Flexslider $optionset */
//  $optionset = $settings['optionset'];
//
//  // Generated thumbnail support.
//  if ($optionset->getOption('controlNav') === "thumbnails") {
//    // If the thumbnails are enabled in the option set,
//    // extract the url to set as the thumbnail data.
//    $src = [];
/*    if (!preg_match("/<img.+?src=[\"'](.+?)[\"'].+?>/", $item, $src)) {*/
/*      preg_match("/<img.+?srcset=[\"'](.+?)[\"'].+?>/", $item, $src);*/
//    }
//
//    if (!empty($src[1])) {
//      $attributes['data-thumb'] = $src[1];
//    }
//
//    // Let's also get the alt attribute to apply to thumbnails.
//    // This only works in library version 2.6+.
//    $alt = [];
/*    preg_match("/<img.+?alt=[\"'](.*?)[\"'].+?>/", $item, $alt);*/
//
//    if (!empty($alt)) {
//      $attributes['data-thumb-alt'] = $alt[1];
//    }
//  }
//
//  if ($optionset->getOption('thumbCaptions') and !empty($caption)) {
//    $attributes['data-thumbcaption'] = $caption;
//    // Prevent captions from appearing in the slider as well.
//    if (FALSE === $optionset->getOption('thumbCaptionsBoth')) {
//      $caption = '';
//    }
//  }
//
//}
