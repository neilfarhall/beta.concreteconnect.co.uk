<?php

/**
 * @file
 * search.inc
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter.
 */

function themag_form_mailchimp_signup_subscribe_block_newsletter_signup_form_form_alter(&$form, FormStateInterface $form_state, $form_id) {

  $form['mergevars']['EMAIL']['#attributes']['placeholder'] =
    t($form['mergevars']['EMAIL']['#title']->__toString());

  unset($form['mergevars']['EMAIL']['#title']);

  if (isset($form['mergevars']['FNAME'])) {
    $form['mergevars']['FNAME']['#attributes']['placeholder'] =
      t($form['mergevars']['FNAME']['#title']->__toString());

    unset($form['mergevars']['FNAME']['#title']);
  }

  if (isset($form['mergevars']['LNAME'])) {
    $form['mergevars']['LNAME']['#attributes']['placeholder'] =
      t($form['mergevars']['LNAME']['#title']->__toString());

    unset($form['mergevars']['LNAME']['#title']);
  }

  if (isset($form['mergevars']['BIRTHDAY'])) {
    $form['mergevars']['BIRTHDAY']['#attributes']['placeholder'] =
      t($form['mergevars']['BIRTHDAY']['#title']->__toString());

    unset($form['mergevars']['BIRTHDAY']['#title']);
  }

}
