<?php

/**
 * @file
 * Contains \Drupal\body_inject\ProfileInterface.
 */

namespace Drupal\body_inject;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a profile entity.
 */
interface ProfileInterface extends ConfigEntityInterface {

  /**
   * Gets the profile description.
   *
   * @return string
   *   The profile description.
   */
  public function getDescription();

  /**
   * Sets the profile description.
   *
   * @param string $description
   *   The profile description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Returns the selected inject block.
   *
   * @return $this
   */
  public function getBlock();

  /**
   * Sets the selected inject block.
   *
   * @param string $block_reference
   *   The block to inject.
   *
   * @return $this
   */
  public function setBlock($block_reference);

  /**
   * Returns the selected node type for inject block.
   *
   * @return $this
   */
  public function getNodeType();

  /**
   * Sets the node type for inject block.
   *
   * @param string $node_type
   *   The type of node to inject the block.
   *
   * @return $this
   */
  public function setNodeType($node_type);

}
