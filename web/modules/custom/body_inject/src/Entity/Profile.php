<?php

/**
 * @file
 * Contains \Drupal\body_inject\Entity\Profile.
 */

namespace Drupal\body_inject\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\body_inject\ProfileInterface;

/**
 * Defines the body_inject_profile entity.
 *
 * @ConfigEntityType(
 *   id = "body_inject_profile",
 *   label = @Translation("BodyInject profile"),
 *   handlers = {
 *     "list_builder" = "Drupal\body_inject\ProfileListBuilder",
 *     "form" = {
 *       "add" = "Drupal\body_inject\Form\Profile\AddForm",
 *       "edit" = "Drupal\body_inject\Form\Profile\EditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer body_inject profiles",
 *   config_prefix = "body_inject_profile",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "collection" = "/admin/config/content/body_inject",
 *     "edit-form" = "/admin/config/content/body_inject/manage/{body_inject_profile}",
 *     "delete-form" = "/admin/config/content/body_inject/manage/{body_inject_profile}/delete"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "block_reference",
 *     "node_type",
 *     "paragraph_operator",
 *     "paragraph_number",
 *     "and_or",
 *     "char_operator",
 *     "char_number",
 *     "paragraph_offset",
 *     "paragraph_position",
 *     "char_position",
 *   }
 * )
 */
class Profile extends ConfigEntityBase implements ProfileInterface {

  /**
   * The ID of this profile.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of this profile.
   *
   * @var string
   */
  protected $label;

  /**
   * Description of this profile.
   *
   * @var string
   */
  protected $description;

  /**
   * Block this profile targets for injection.
   *
   * @var string
   */
  protected $block_reference;


  /**
   * Node type for the block to be injected into.
   *
   * @var string
   */
  protected $node_type;


  /**
   *
   *
   * @var string
   */
  protected $paragraph_operator;

  /**
   *
   *
   * @var string
   */
  protected $paragraph_number;

  /**
   *
   *
   * @var string
   */
  protected $and_or;

  /**
   *
   *
   * @var string
   */
  protected $char_operator;


  /**
   *
   *
   * @var string
   */
  protected $char_number;


  /**
   *
   *
   * @var string
   */
  protected $paragraph_offset;


  /**
   *
   *
   * @var string
   */
  protected $paragraph_position;

  /**
   *
   *
   * @var string
   */
  protected $char_position;

  /**
   * {@inheritdoc}
   */
  public function getBlock() {
    return $this->get('block_reference');
  }

  /**
   * {@inheritdoc}
   */
  public function setBlock($block_reference) {
    $this->set('block_reference', trim($block_reference));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraphOperator() {
    return $this->get('paragraph_operator');
  }

  /**
   * {@inheritdoc}
   */
  public function setParagraphOperator($paragraph_operator) {
    $this->set('paragraph_operator', trim($paragraph_operator));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraphNumber() {
    return $this->get('paragraph_number');
  }

  /**
   * {@inheritdoc}
   */
  public function setParagraphNumber($paragraph_number) {
    $this->set('paragraph_number', trim($paragraph_number));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAndOr() {
    return $this->get('and_or');
  }

  /**
   * {@inheritdoc}
   */
  public function setAndOr($and_or) {
    $this->set('and_or', trim($and_or));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCharOperator() {
    return $this->get('char_operator');
  }

  /**
   * {@inheritdoc}
   */
  public function setCharOperator($char_operator) {
    $this->set('char_operator', trim($char_operator));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCharNumber() {
    return $this->get('char_number');
  }

  /**
   * {@inheritdoc}
   */
  public function setCharNumber($char_operator) {
    $this->set('char_number', trim($char_operator));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraphOffset() {
    return $this->get('paragraph_offset');
  }

  /**
   * {@inheritdoc}
   */
  public function setParagraphOffset($paragraph_offset) {
    $this->set('paragraph_offset', trim($paragraph_offset));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraphPosition() {
    return $this->get('paragraph_position');
  }

  /**
   * {@inheritdoc}
   */
  public function setParagraphPosition($paragraph_position) {
    $this->set('paragraph_position', trim($paragraph_position));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCharPosition() {
    return $this->get('char_position');
  }

  /**
   * {@inheritdoc}
   */
  public function setCharPosition($char_position) {
    $this->set('char_position', trim($char_position));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeType() {
    return $this->get('node_type');
  }

  /**
   * {@inheritdoc}
   */
  public function setNodeType($block_reference) {
    $this->set('node_type', trim($block_reference));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description');
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->set('description', trim($description));
    return $this;
  }

}
