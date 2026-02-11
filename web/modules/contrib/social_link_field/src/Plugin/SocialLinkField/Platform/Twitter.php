<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides X (Twitter) platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "twitter",
 *   name = @Translation("X (Twitter)"),
 *   icon = "fa-x-twitter",
 *   iconSquare = "fa-square-x-twitter",
 *   iconSet = "fa-brands",
 *   urlPrefix = "https://www.x.com/",
 * )
 */
class Twitter extends PlatformBase {}
