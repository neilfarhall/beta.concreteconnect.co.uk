<?php

namespace Drupal\social_link_field\Plugin\SocialLinkField\Platform;

use Drupal\social_link_field\PlatformBase;

/**
 * Provides 'flickr' platform.
 *
 * @SocialLinkFieldPlatform(
 *   id = "flickr",
 *   name = @Translation("Flickr"),
 *   icon = "fa-flickr",
 *   iconSquare = "fa-flickr-square",
 *   urlPrefix = "https://www.flickr.com/",
 * )
 */
class Flickr extends PlatformBase {}
