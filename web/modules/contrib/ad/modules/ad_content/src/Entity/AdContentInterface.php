<?php

namespace Drupal\ad_content\Entity;

use Drupal\ad\AdInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Common interface for AD content entities.
 */
interface AdContentInterface extends AdInterface, ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface, EntityPublishedInterface, RevisionLogInterface {

  const BUCKET_ID = 'ad_content';

}
