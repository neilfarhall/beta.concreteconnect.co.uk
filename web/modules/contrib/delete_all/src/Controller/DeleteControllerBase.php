<?php

namespace Drupal\delete_all\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;

/**
 * Returns responses for devel module routes.
 */
abstract class DeleteControllerBase extends ControllerBase {

  /**
   * Database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a NodeController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->connection = $database;
  }

}
