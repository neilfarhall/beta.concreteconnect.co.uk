<?php

namespace Drupal\paragraph_blocks;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The paragraph blocks entity manager.
 */
class ParagraphBlocksEntityManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Constructs a new ParagraphBlocksEntityManager object.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    RequestStack $request_stack,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $request_stack;
  }

  /**
   * Return the entity from Layout Builders section storage in request.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity of the referer, or null if none.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRefererEntity() {
    /** @var \Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage $section_storage */
    $section_storage = $this->requestStack->getCurrentRequest()->attributes->get('section_storage');

    // If section storage is for a single entity override there will be an
    // entity to return.
    if (!empty($section_storage) && 'overrides' === $section_storage->getPluginId()) {
      $entity = $section_storage->getContext('entity')
        ->getContextData()
        ->getEntity();

      // Load entity fresh from database using ID, bypassing any tempstore
      // cached version to ensure newly added paragraphs are available.
      return $this->loadFreshEntity($entity);
    }
    return NULL;
  }

  /**
   * Load a fresh copy of the entity from the database.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to reload.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The freshly loaded entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadFreshEntity(ContentEntityInterface $entity): ContentEntityInterface {
    $entity_type_id = $entity->getEntityTypeId();
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    // Load the latest revision directly from the database.
    if ($entity->getEntityType()->isRevisionable()) {
      $latest_revision_id = $storage->getLatestTranslationAffectedRevisionId(
        $entity->id(),
        $entity->language()->getId()
      );
      if ($latest_revision_id) {
        return $storage->loadRevision($latest_revision_id);
      }
    }

    return $storage->load($entity->id());
  }

}
