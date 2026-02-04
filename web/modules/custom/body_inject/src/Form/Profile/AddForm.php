<?php

/**
 * @file
 * Contains \Drupal\body_inject\Form\Profile\AddForm.
 */

namespace Drupal\body_inject\Form\Profile;

use Drupal\Core\Form\FormStateInterface;

/**
 * Controller for profile addition forms.
 *
 * @see \Drupal\body_inject\Profile\FormBase
 */
class AddForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    return $actions;
  }

}
