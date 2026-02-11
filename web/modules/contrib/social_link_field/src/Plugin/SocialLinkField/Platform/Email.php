<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'Email' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "email",
 *   name = @Translation("Email"),
 *   icon = "fa-envelope",
 *   iconSquare = "fa-square-envelope",
 *   iconSet = "fa-solid",
 *   urlPrefix = "mailto:",
 * )
 */
class Email extends PlatformBase {}
