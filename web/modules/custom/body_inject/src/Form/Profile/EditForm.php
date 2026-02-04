<?php

/**
 * @file
 * Contains \Drupal\body_inject\Form\Profile\EditForm.
 */

namespace Drupal\body_inject\Form\Profile;

use Drupal\Core\Form\FormStateInterface;

/**
 *  Provides an edit form for profile.
 *
 * @see \Drupal\body_inject\Profile\FormBase
 */
class EditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update profile');
    $actions['delete']['#value'] = $this->t('Delete profile');
    return $actions;
  }

}
