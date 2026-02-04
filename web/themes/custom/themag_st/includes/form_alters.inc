<?php

/**
 * Implements hook_form_FORM_ID_alter().
 */
function themag_st_form_alter(&$form, &$form_state, $form_id) {

  // on aggregates directory
  if ($form['#id'] == 'views-exposed-form-aggregates-directory-block-1') {

    $field = 'dir_products_type_tid';
    if (isset($form[$field]) && $form[$field]['#type'] == 'select') {
      $form[$field]['#attributes']['data-placeholder'] = t('Product type');
    }

    //next field
    $field = 'dir_products_tid';
    if (isset($form[$field]) && $form[$field]['#type'] == 'select') {
      $form[$field]['#attributes']['data-placeholder'] = t('Record Type');
    }

    //next field
    $field = 'dir_materials_tid';
    if (isset($form[$field]) && $form[$field]['#type'] == 'select') {
      $form[$field]['#attributes']['data-placeholder'] = t('Materials');
    }
    //next field
    $field = 'dir_agg_prod_search_tid';
    if (isset($form[$field]) && $form[$field]['#type'] == 'select') {
      $form[$field]['#attributes']['data-placeholder'] = t('Search Aggregate Products Database');
    }
  }


  // example for -- equipmrnt services
  if ($form['#id'] == 'views-exposed-form-equipment-services-block-1') {
    if (isset($form['dir_products_tid']) && $form['dir_products_tid']['#type'] == 'select') {
      $form['dir_products_tid']['#attributes']['data-placeholder'] = t('Select Products');
    }
  }
  // example for -- Quarry Consultants
  if ($form['#id'] == 'views-exposed-form-consultants-directory-block-1') {
    if (isset($form['field_directory_consultants_cat_target_id']) && $form['field_directory_consultants_cat_target_id']['#type'] == 'select') {
      $form['field_directory_consultants_cat_target_id']['#attributes']['data-placeholder'] = t('Select Category');
    }
  }
  // example for -- Quarry Organisations
  if ($form['#id'] == 'views-exposed-form-news-listings-block-6') {
    if (isset($form['field_article_article_target_id']) && $form['field_article_article_target_id']['#type'] == 'select') {
      $form['field_article_article_target_id']['#attributes']['data-placeholder'] = t('Select Catetory');
    }
  }

  if ($form_id == 'comment_comment_form') {

	$currentUser = \Drupal::currentUser();
	if (!$currentUser->isAuthenticated()) {
	    $form['homepage']['#access'] = FALSE;
	}

 //   foreach ($form as $field_name => &$field) {
        // Check if the element is a field.
  //      if (is_array($field) && isset($field['#type']) ) {
            // Add a class using the field's machine name.
  //          $field['#attributes']['class'][] = 'omp--' . $field_name;
   //     }
  //  }

  }

}
