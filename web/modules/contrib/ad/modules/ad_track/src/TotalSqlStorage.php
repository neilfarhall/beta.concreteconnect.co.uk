<?php

namespace Drupal\ad_track;

use Drupal\ad\AdInterface;
use Drupal\ad\Track\TrackerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;

/**
 * Track totals SQL storage backend.
 *
 * @private
 */
class TotalSqlStorage implements TotalStorageInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * A set of transactions.
   *
   * @var \Drupal\Core\Database\Transaction[]
   */
  protected array $transactions = [];

  /**
   * AdTrackTotalSqlStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function loadTotals(AdInterface $ad): array {
    $result = [
      TrackerInterface::EVENT_CLICK => 0,
      TrackerInterface::EVENT_IMPRESSION => 0,
    ];

    try {
      $record = $this->database
        ->select('ad_track_total', 'att')
        ->fields('att', [TrackerInterface::EVENT_CLICK, TrackerInterface::EVENT_IMPRESSION])
        ->condition('ad_id', $ad->getAdIdentifier())
        ->condition('bucket_id', $ad->getBucketId())
        ->execute()
        ->fetch();

      if ($record) {
        $result = (array) $record;
      }
    }
    catch (DatabaseExceptionWrapper $e) {
      watchdog_exception('ad_track', $e);
    }

    return $result;
  }

  /**
   * Returns a transaction ID.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD being tracked.
   *
   * @return string
   *   A transaction ID.
   */
  protected function getTransactionId(AdInterface $ad): string {
    return $ad->getBucketId() . ':' . $ad->getAdIdentifier();
  }

  /**
   * {@inheritdoc}
   */
  public function startTransaction(AdInterface $ad): void {
    $id = $this->getTransactionId($ad);
    if (!isset($this->transactions[$id])) {
      $this->transactions[$id] = $this->database->startTransaction($id);
    }
    else {
      $message = sprintf('A transaction already exists for AD %s', $id);
      throw new \InvalidArgumentException($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function increaseTotal(string $type, AdInterface $ad): void {
    // Denormalize these counters taking advantage of the properties of the base
    // table. This allows to be quick while still easily avoiding race conditions.
    $field = $this->database->escapeField($type);
    $query = "
      INSERT INTO {ad_track_total}
      (ad_id, bucket_id, $field)
      VALUES (:ad_id, :bucket_id, 1)
      ON DUPLICATE KEY UPDATE $field = $field + 1
    ";

    $args = [
      ':ad_id' => $ad->getAdIdentifier(),
      ':bucket_id' => $ad->getBucketId(),
    ];

    try {
      $this->database->query($query, $args);
      unset($this->transactions[$this->getTransactionId($ad)]);
    }
    catch (DatabaseExceptionWrapper $e) {
      $this->rollbackTransaction($ad);
      throw new \LogicException($e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rollbackTransaction(AdInterface $ad): void {
    $id = $this->getTransactionId($ad);
    if (isset($this->transactions[$id])) {
      $this->transactions[$id]->rollBack();
      unset($this->transactions[$id]);
    }
  }

}
