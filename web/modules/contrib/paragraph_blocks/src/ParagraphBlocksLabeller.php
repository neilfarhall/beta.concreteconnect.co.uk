<?php

namespace Drupal\paragraph_blocks;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraph_blocks\Entity\ParagraphBlocksEntity;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs_library\Entity\LibraryItem;

/**
 * Labels the paragraph blocks once the entity context is known.
 */
class ParagraphBlocksLabeller {

  use StringTranslationTrait;

  /**
   * The plugin type id.
   *
   * @var string
   */
  const PLUGIN_TYPE_ID = 'paragraph_field';

  /**
   * The label format.
   *
   * @var string
   */
  const LABEL_FORMAT = 'Page: @label';

  /**
   * The current entity, or NULL.
   *
   * @var \Drupal\Core\Entity\EntityInterface|null
   */
  protected ?EntityInterface $entity;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The paragraph blocks entity manager.
   *
   * @var \Drupal\paragraph_blocks\ParagraphBlocksEntityManager
   */
  protected ParagraphBlocksEntityManager $paragraphBlocksEntityManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Paragraphs entity storage instance.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $paragraphStorage;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a new ParagraphBlocksLabeller object.
   *
   * @param \Drupal\paragraph_blocks\ParagraphBlocksEntityManager $paragraph_blocks_entity_manager
   *   The Paragraph blocks entity manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The interface for an entity field manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    ParagraphBlocksEntityManager $paragraph_blocks_entity_manager,
    EntityFieldManagerInterface $entity_field_manager,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->paragraphBlocksEntityManager = $paragraph_blocks_entity_manager;
    $this->entity = $this->paragraphBlocksEntityManager->getRefererEntity();
    $this->entityFieldManager = $entity_field_manager;
    $this->moduleHandler = $module_handler;
    $this->paragraphStorage = $entity_type_manager->getStorage('paragraph');
    $this->configFactory = $config_factory;
  }

  /**
   * Removed unused paragraphs and update the layout builder title.
   *
   * @param array $definitions
   *   The plugin definitions.
   */
  public function hookLayoutBuilderChooseBlocksAlter(array &$definitions) {
    $config = $this->configFactory->get('paragraph_blocks.settings');
    $library_items_only = $config->get('library_items_only');

    // Loop through all the plugin definitions.
    foreach ($definitions as $plugin_id => $definition) {
      if ($this->isParagraphField($plugin_id)) {
        // Remove if this paragraph field is not enabled or if there is no
        // paragraph data for the delta.
        if (!$this->paragraphFieldIsEnabled($plugin_id) || is_null($this->getParagraph($plugin_id))) {
          unset($definitions[$plugin_id]);
        }
        // Replace the admin label.
        else {
          $paragraph = $this->getParagraph($plugin_id);
          $is_from_library = $this->isParagraphFromLibrary($paragraph);

          // If library_items_only is enabled, remove non-library paragraphs.
          if ($library_items_only && !$is_from_library) {
            unset($definitions[$plugin_id]);
            continue;
          }

          // Replace the paragraph if it is from the paragraphs library.
          if ($is_from_library) {
            $paragraph = $this->getParagraphFromLibrary($paragraph) ?? $paragraph;
            $definitions[$plugin_id]['category'] .= ' ' . $this->t('from library');
          }
          $definitions[$plugin_id]['admin_label'] = $this->getTitle($paragraph);
        }
      }
    }
  }

  /**
   * Check if the given plugin id represents a paragraph field.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return bool
   *   True if plugin type ID is a paragraph field.
   */
  public function isParagraphField(string $plugin_id): bool {
    $plugin_parts = $this->getPluginInfo($plugin_id);
    return $plugin_parts['plugin_type_id'] === self::PLUGIN_TYPE_ID;
  }

  /**
   * Check if this paragraph is from the paragraphs library.
   *
   * This is only relevant if the paragraphs_library module is enabled.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph block entity.
   *
   * @return bool
   *   True if paragraph is promoted to the paragraphs library.
   */
  public function isParagraphFromLibrary(Paragraph $paragraph): bool {
    return $paragraph->bundle() === 'from_library';
  }

  /**
   * Check if paragraph field is enabled.
   *
   * Only check the field bundle if it exists. This is new to the 2.x branch.
   * So this check exists for backwards compatibility with plugins saved using
   * the 1.x branch.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return bool
   *   TRUE if paragraph field is enabled.
   */
  public function paragraphFieldIsEnabled(string $plugin_id): bool {
    $plugin_parts = $this->getPluginInfo($plugin_id);
    if (!empty($plugin_parts) && isset($plugin_parts['plugin_field_bundle'])) {
      $field_definitions = $this->entityFieldManager
        ->getFieldDefinitions($plugin_parts['plugin_entity_type_id'], $plugin_parts['plugin_field_bundle']);
      $field_config = $field_definitions[$plugin_parts['plugin_field_name']]
        ->getConfig($plugin_parts['plugin_field_bundle']);
      if ($field_config->getThirdPartySetting('paragraph_blocks', 'status', TRUE)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns the plugin's paragraph.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return ?\Drupal\paragraph_blocks\Entity\ParagraphBlocksEntity
   *   The paragraph title.
   */
  public function getParagraph(string $plugin_id): ?ParagraphBlocksEntity {
    $plugin_parts = $this->getPluginInfo($plugin_id);

    // Return NULL if this plugin id represents no valid paragraph data.
    if (!$this->entity
      || $this->entity->bundle() !== $plugin_parts['plugin_field_bundle']
      || $plugin_parts['plugin_field_delta'] >= $this->entity->get($plugin_parts['plugin_field_name'])
        ->count()
    ) {
      return NULL;
    }

    // Get the referenced paragraph.
    return $this->entity->get($plugin_parts['plugin_field_name'])
      ->referencedEntities()[$plugin_parts['plugin_field_delta']];
  }

  /**
   * Gets the original paragraph referenced by the paragraphs library item.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Paragraphs library entity.
   *
   * @return ?\Drupal\Core\Entity\EntityInterface
   *   The referenced paragraph entity or NULL.
   */
  public function getParagraphFromLibrary(Paragraph $paragraph): ?EntityInterface {
    if ($paragraph->hasField('field_reusable_paragraph')
      && $library_item = LibraryItem::load($paragraph->get('field_reusable_paragraph')->target_id)
    ) {
      if ($this->paragraphStorage instanceof RevisionableStorageInterface) {
        $result = $this->paragraphStorage->loadRevision($library_item->get('paragraphs')->target_revision_id);
        if ($result) {
          return $result;
        }
      }
      // Fall back to loading by entity ID if revision loading fails.
      return $this->paragraphStorage->load($library_item->get('paragraphs')->target_id);
    }
    return NULL;
  }

  /**
   * Explode the plugin id and extract information about the plugin.
   *
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @return array
   *   Keyed array with information about the plugin.
   */
  public function getPluginInfo(string $plugin_id): array {
    $parts = explode(':', $plugin_id);
    $count = count($parts);
    if ($count < 4) {
      return [
        'plugin_type_id' => '',
        'plugin_entity_type_id' => '',
        'plugin_field_name' => '',
        'plugin_field_delta' => '',
        'plugin_field_bundle' => '',
        'count' => $count,
      ];
    }
    return [
      'plugin_type_id' => $parts[0] ?? '',
      'plugin_entity_type_id' => $parts[1] ?? '',
      'plugin_field_name' => $parts[2] ?? '',
      'plugin_field_delta' => $parts[3] ?? '',
      'plugin_field_bundle' => $parts[4] ?? '',
      'count' => $count,
    ];
  }

  /**
   * Returns the plugin's paragraph title.
   *
   * @param \Drupal\paragraph_blocks\Entity\ParagraphBlocksEntity $paragraph
   *   The paragraph block.
   *
   * @return string
   *   The paragraph title.
   */
  public function getTitle(ParagraphBlocksEntity $paragraph): string {
    // Use admin title as block label.
    if ($paragraph->hasAdminTitle()) {
      return $paragraph->getAdminTitle();
    }

    // Fallback to paragraph summary title behavior.
    return $paragraph->getSummary();
  }

  /**
   * Set add/edit form title and display properties.
   *
   * @param array $form
   *   The form values.
   * @param string $title
   *   The title.
   */
  public function setFormTitle(array &$form, string $title) {
    $section = &$form['flipper']['front']['settings'];
    $section['admin_label']['#type'] = 'hidden';
    $section['label']['#type'] = 'hidden';
    $section['label']['#required'] = FALSE;
    $section['label_display']['#type'] = 'hidden';
    $section['label_display']['#value'] = 0;
    $section['label']['#value'] = $title;
  }

}
