<?php

namespace Drupal\taxonomy_delete;

use Drupal\Core\Entity\EntityStorageException;

/**
 * Class BatchService. The base class for batch operations.
 */
class BatchService {

  /**
   * Batch process callback.
   */
  public static function deleteTerm($tid, &$context) {
    if ($term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid)) {
      try {
        $term->delete();
      }
      catch (EntityStorageException $e) {
        \Drupal::logger('taxonomy_delete')->notice($e->getMessage());
      }
    }
  }

}
