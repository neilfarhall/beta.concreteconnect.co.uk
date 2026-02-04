<?php

/**
 * @file
 * Contains \Drupal\body_inject\ProfileListBuilder.
 */

namespace Drupal\body_inject;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of profile entities.
 *
 * @see \Drupal\body_inject\Entity\Profile
 */
class ProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * ProfileListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, BlockManagerInterface $block_manager) {
    parent::__construct($entity_type, $storage);
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Profile');
    $header['description'] = [
      'data' => t('Description'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['node_type'] = [
      'data' => t('Node Type'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['block_reference'] = [
      'data' => t('Block'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\body_inject\ProfileInterface $body_injectProfile */
    $body_injectProfile = $entity;
    $row['label'] = $body_injectProfile->label();
    $row['description']['data'] = ['#markup' => $body_injectProfile->getDescription()];
    $row['node_type']['data'] = ['#markup' => $body_injectProfile->getNodeType()];

    // Get label. add this to the Profile Class?
    $block_id = $body_injectProfile->getBlock();
    $block_is_valid = $this->blockManager->hasDefinition($block_id);
    if ($block_is_valid) {
      $block_label = $this->blockManager->createInstance($block_id, [])->label();
    }
    else {
      $block_label = 'invalid block/deleted block';
    }
    $row['block_reference']['data'] = ['#markup' => $block_label];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = t('Edit profile');
    }

    return $operations;
  }

}
