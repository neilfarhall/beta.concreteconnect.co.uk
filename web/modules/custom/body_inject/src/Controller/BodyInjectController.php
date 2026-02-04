<?php

/**
 * @file
 * Contains \Drupal\body_inject\Controller\BodyInjectController.
 */

namespace Drupal\body_inject\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\body_inject\ProfileInterface;

/**
 * Provides route responses for body_inject.module.
 */
class BodyInjectController extends ControllerBase {

  /**
   * Route title callback.
   *
   * @param \Drupal\body_inject\ProfileInterface $body_inject_profile
   *   The profile.
   *
   * @return string
   *   The profile label as a render array.
   */
  public function profileTitle(ProfileInterface $body_inject_profile) {
    return $this->t('Edit %label profile', array('%label' => $body_inject_profile->label()));
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\body_inject\ProfileInterface $body_inject_profile
   *   The profile.
   * @param string $plugin_instance_id
   *   The plugin instance id.
   *
   * @return string
   *   The title for the matcher edit form.
   */

}
