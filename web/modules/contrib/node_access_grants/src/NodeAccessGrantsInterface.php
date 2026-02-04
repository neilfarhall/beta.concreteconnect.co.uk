<?php

namespace Drupal\node_access_grants;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Central interface for implementing the Drupal access grants.
 */
interface NodeAccessGrantsInterface {

  /**
   * Returns the grants to be written for a given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @see hook_node_access_records().
   *
   * @return array
   *   The access grants records.
   */
  public function accessRecords(NodeInterface $node);

  /**
   * Inform the node access system what permissions the user has.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param $op
   *   The access operation.
   *
   * @see hook_node_grants()
   *
   * @return array
   *   The grants for the user.
   */
  public function grants(AccountInterface $account, $op);
}
