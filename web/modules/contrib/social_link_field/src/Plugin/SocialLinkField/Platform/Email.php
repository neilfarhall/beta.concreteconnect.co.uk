<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'Email' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "email",
 *   name = @Translation("Email"),
 *   icon = "fa-solid fa-envelope",
 *   iconSquare = "fa-envelope",
 *   urlPrefix = "mailto:",
 * )
 */
class Email extends PlatformBase {}
