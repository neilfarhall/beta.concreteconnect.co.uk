<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'behance' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "behance",
 *   name = @Translation("Behance"),
 *   icon = "fa-behance",
 *   iconSquare = "fa-behance-square",
 *   iconSet = "fa-brands",
 *   urlPrefix = "https://www.behance.net/",
 * )
 */
class Behance extends PlatformBase {}
