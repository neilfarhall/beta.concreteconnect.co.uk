<?php

namespace Drupal\paragraph_blocks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\layout_builder\SectionListInterface;

/**
 * The entity presave helper.
 */
class ParagraphBlocksEntityPresaveHelper {

  /**
   * The private temp store factory service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected PrivateTempStoreFactory $tempStoreFactory;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * The paragraph_blocks_entity_presave temp store.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore|null
   */
  private ?PrivateTempStore $tempStore;

  /**
   * The key identifying the temp store.
   *
   * @var string
   */
  private string $tempStoreKey;

  /**
   * The entity to use the presave helper on.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private EntityInterface $entity;

  /**
   * Layout builder section item list.
   *
   * @var \Drupal\layout_builder\SectionListInterface
   */
  private SectionListInterface $layout;

  /**
   * Constructs a ParagraphBlocksEntityPresaveHelper object.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The Drupal Logger Factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, LoggerChannelFactoryInterface $loggerFactory) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->tempStore = $this->tempStoreFactory->get('paragraph_blocks_entity_presave');
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Getter for the entity property.
   */
  public function getEntity(): ?EntityInterface {
    return $this->entity;
  }

  /**
   * Setter for the entity property.
   */
  public function setEntity(EntityInterface $entity): void {
    $this->entity = $entity;
    $this->tempStoreKey = $entity->bundle() . '.' . $entity->id() . '.'
      . $entity->language()->getId();
    $this->layout = $this->entity->get('layout_builder__layout');
  }

  /**
   * Update the layout builder configuration of an entity.
   *
   * This is necessary to prevent a broken layout when the paragraph references
   * change order or are deleted.
   *
   * @see: https://www.drupal.org/project/paragraph_blocks/issues/3099424
   */
  public function updateLayoutBuilderConfiguration(): void {
    if (is_null($this->getEntity())) {
      $this->loggerFactory->get('paragraph_blocks')
        ->error('Could not perform presave helper method %method because entity was not set.', ['%method' => __FUNCTION__]);
      return;
    }

    if ($this->entity->isNew()) {
      // For new entities (including clones), set paragraph_id on any components
      // that still reference paragraphs by field delta position. Deletion
      // checks are skipped because there is no original entity to compare
      // against.
      $sections = $this->layout->getIterator()->getArrayCopy();
      foreach ($this->getParagraphBlocksFields() as $field) {
        $current_delta_to_id = $this->getCurrentParagraphIds($field);
        foreach ($sections as $section_index => $section) {
          foreach ($section->getValue()['section']->getComponents() as $component_index => $component) {
            $configuration = $component->get('configuration');
            $plugin_id_parts = explode(':', $configuration['id']);
            if (!$this->matchesParagraphBlockField($plugin_id_parts, $field) || !empty($configuration['paragraph_id'])) {
              continue;
            }
            $configuration = $this->migrateLegacyComponent($configuration, $plugin_id_parts, $current_delta_to_id);
            $this->setComponentConfiguration($section_index, $component_index, $configuration);
          }
        }
      }
    }
    elseif (isset($this->entity->original)) {
      // Pre-compute cloned paragraph IDs (from temp store) as integers once.
      $cloned_paragraph_ids = array_map('intval', $this->getTempStoreValue() ?? []);

      $sections = $this->layout->getIterator()->getArrayCopy();

      foreach ($this->getParagraphBlocksFields() as $field) {
        $original_delta_to_id = $this->getOriginalDeltaToIdMap($field);
        $current_paragraph_ids = $this->getCurrentParagraphIds($field);

        $components_to_remove = [];
        foreach ($sections as $section_index => $section) {
          foreach ($section->getValue()['section']->getComponents() as $component_index => $component) {
            $configuration = $component->get('configuration');
            $plugin_id_parts = explode(':', $configuration['id']);

            if (!$this->matchesParagraphBlockField($plugin_id_parts, $field)) {
              continue;
            }

            // Migrate legacy blocks: set paragraph_id from original delta
            // mapping.
            if (empty($configuration['paragraph_id'])) {
              $configuration = $this->migrateLegacyComponent($configuration, $plugin_id_parts, $original_delta_to_id);
              $this->setComponentConfiguration($section_index, $component_index, $configuration);
              // If paragraph_id is still unresolved, the delta doesn't exist in
              // the original entity — the component is orphaned.
              if (empty($configuration['paragraph_id'])) {
                $components_to_remove[] = [
                  'section_index' => $section_index,
                  'component_uuid' => $component->getUuid(),
                ];
                continue;
              }
            }

            // Remove component if its paragraph was deleted.
            if ($this->isParagraphDeleted($configuration, $current_paragraph_ids, $cloned_paragraph_ids)) {
              $components_to_remove[] = [
                'section_index' => $section_index,
                'component_uuid' => $component->getUuid(),
              ];
            }
          }
        }

        foreach ($components_to_remove as $remove_info) {
          $this->layout
            ->getIterator()
            ->offsetGet($remove_info['section_index'])
            ->getValue()['section']
            ->removeComponent($remove_info['component_uuid']);
        }
      }
    }

    $this->storeParagraphIdsForCloneDetection();
  }

  /**
   * Get the value of the entity's paragraph_blocks_entity_presave temp store.
   *
   * @return mixed
   *   The data associated with the key, or NULL if the key does not exist.
   */
  private function getTempStoreValue() {
    return $this->tempStore->get($this->tempStoreKey);
  }

  /**
   * Set the value of the entity's paragraph_blocks_entity_presave temp store.
   *
   * @param mixed $value
   *   The data to store.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function setTempStoreValue($value): void {
    $this->tempStore->set($this->tempStoreKey, $value);
  }

  /**
   * Collect paragraph reference fields using paragraph blocks.
   *
   * @return array
   *   The fields with status settings.
   */
  private function getParagraphBlocksFields(): array {
    $fields = [];
    foreach ($this->entity->getFields() as $key => $field) {
      if (
        method_exists($field->getFieldDefinition(), 'getThirdPartySetting')
        && $field->getFieldDefinition()->getThirdPartySetting('paragraph_blocks', 'status')
      ) {
        $fields[] = $key;
      }
    }
    return $fields;
  }

  /**
   * Get a mapping of original field deltas to paragraph entity IDs.
   *
   * @param string $field
   *   The field name.
   *
   * @return array
   *   Array keyed by delta with paragraph entity ID as value.
   */
  private function getOriginalDeltaToIdMap(string $field): array {
    $map = [];
    $language = $this->entity->language()->getId();
    $original = $this->entity->original;
    $entity = $original->hasTranslation($language) ? $original->getTranslation($language) : $original;

    foreach ($entity->get($field)->getIterator() as $delta => $item) {
      $map[$delta] = (int) $item->getValue()['target_id'];
    }
    return $map;
  }

  /**
   * Get current paragraph entity IDs for a field.
   *
   * @param string $field
   *   The field name.
   *
   * @return int[]
   *   Array of paragraph entity IDs.
   */
  private function getCurrentParagraphIds(string $field): array {
    $ids = [];
    foreach ($this->entity->get($field)->getIterator() as $item) {
      $ids[] = (int) $item->getValue()['target_id'];
    }
    return $ids;
  }

  /**
   * Check if pre-parsed plugin ID parts match a paragraph block for a field.
   *
   * @param array $parts
   *   The exploded plugin ID parts.
   * @param string $field
   *   The field name.
   *
   * @return bool
   *   TRUE if the component is a paragraph block for this field.
   */
  private function matchesParagraphBlockField(array $parts, string $field): bool {
    return count($parts) >= 5
      && $parts[0] === 'paragraph_field'
      && $parts[1] === $this->entity->getEntityType()->id()
      && $parts[2] === $field
      && $parts[4] === $this->entity->bundle();
  }

  /**
   * Migrate a legacy component by setting paragraph_id from its delta.
   *
   * @param array $configuration
   *   The component configuration.
   * @param array $plugin_id_parts
   *   The exploded plugin ID parts.
   * @param array $original_delta_to_id
   *   Mapping of original deltas to paragraph entity IDs.
   *
   * @return array
   *   The updated configuration.
   */
  private function migrateLegacyComponent(array $configuration, array $plugin_id_parts, array $original_delta_to_id): array {
    $delta = isset($plugin_id_parts[3]) && is_numeric($plugin_id_parts[3]) ? (int) $plugin_id_parts[3] : NULL;
    if ($delta !== NULL && isset($original_delta_to_id[$delta])) {
      $configuration['paragraph_id'] = $original_delta_to_id[$delta];
    }
    return $configuration;
  }

  /**
   * Check if a component's paragraph has been deleted.
   *
   * @param array $configuration
   *   The component configuration.
   * @param int[] $current_paragraph_ids
   *   Current paragraph entity IDs on the entity.
   * @param int[] $cloned_paragraph_ids
   *   Paragraph IDs from a clone operation to exclude from deletion.
   *
   * @return bool
   *   TRUE if the paragraph was deleted.
   */
  private function isParagraphDeleted(array $configuration, array $current_paragraph_ids, array $cloned_paragraph_ids): bool {
    if (empty($configuration['paragraph_id'])) {
      return FALSE;
    }
    $paragraph_id = (int) $configuration['paragraph_id'];
    return !in_array($paragraph_id, $current_paragraph_ids, TRUE)
      && !in_array($paragraph_id, $cloned_paragraph_ids, TRUE);
  }

  /**
   * Set configuration on a specific layout builder component.
   *
   * @param int|string $section_index
   *   The section index.
   * @param int|string $component_index
   *   The component index.
   * @param array $configuration
   *   The configuration to set.
   */
  private function setComponentConfiguration($section_index, $component_index, array $configuration): void {
    $this->layout
      ->getIterator()
      ->offsetGet($section_index)
      ->getValue()['section']
      ->getComponents()[$component_index]
      ->setConfiguration($configuration);
  }

  /**
   * Store paragraph IDs in temp store for clone detection on next save.
   */
  private function storeParagraphIdsForCloneDetection(): void {
    $new_paragraph_ids = [];
    if ($this->entity->isNew()) {
      foreach ($this->entity->getFields() as $fieldKey => $field) {
        if (
          method_exists($field->getFieldDefinition(), 'getThirdPartySetting')
          && $field->getFieldDefinition()->getThirdPartySetting('paragraph_blocks', 'status')
        ) {
          foreach ($this->entity->get($fieldKey)->getIterator() as $item) {
            $new_paragraph_ids[] = $item->getValue()['target_id'];
          }
        }
      }
    }

    try {
      $this->setTempStoreValue($new_paragraph_ids);
    }
    catch (TempStoreException $e) {
      $this->loggerFactory->get('paragraph_blocks')->error($e->getMessage());
    }
  }

}
