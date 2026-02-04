<?php

namespace Drupal\ad_track\Entity;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the AD track event schema handler.
 */
class AdTrackEventStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    $base_table = $this->storage->getBaseTable();

    $schema[$base_table]['indexes'] += [
      'ad_id' => ['ad_id'],
      'parent_id' => ['parent_id'],
    ];

    return $schema;
  }

}
