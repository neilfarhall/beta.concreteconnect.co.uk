<?php

namespace Drupal\paragraph_blocks\Plugin\Block;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\paragraph_blocks\ParagraphBlocksEntityManager;
use Drupal\paragraph_blocks\ParagraphBlocksLabeller;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to a paragraph value on an entity.
 *
 * @Block(
 *   id = "paragraph_field",
 *   deriver = "Drupal\paragraph_blocks\Plugin\Deriver\ParagraphBlocksDeriver",
 *   category = @Translation("Paragraphs")
 * )
 */
class ParagraphBlock extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type id.
   *
   * @var string
   */
  protected string $entityTypeId;

  /**
   * The field name.
   *
   * @var string
   */
  protected string $fieldName;

  /**
   * The field delta.
   *
   * @var int
   */
  protected int $fieldDelta;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The Paragraph Blocks Entity Manager.
   *
   * @var \Drupal\paragraph_blocks\ParagraphBlocksEntityManager
   */
  protected ParagraphBlocksEntityManager $paragraphBlocksManager;

  /**
   * The Paragraph Blocks labeller.
   *
   * @var \Drupal\paragraph_blocks\ParagraphBlocksLabeller
   */
  protected ParagraphBlocksLabeller $paragraphBlocksLabeller;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected EntityDisplayRepository $entityDisplayRepository;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected UuidInterface $uuidService;

  /**
   * The content moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface|null
   */
  protected ?ModerationInformationInterface $moderationInformation = NULL;

  /**
   * Constructs a new ParagraphBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\paragraph_blocks\ParagraphBlocksEntityManager $paragraph_blocks_manager
   *   The paragraph blocks entity manager service.
   * @param \Drupal\paragraph_blocks\ParagraphBlocksLabeller $paragraph_blocks_labeller
   *   The paragraph blocks labeller service.
   * @param \Drupal\Core\Entity\EntityDisplayRepository $entity_display_repository
   *   The entity display repository service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\content_moderation\ModerationInformationInterface|null $moderation_information
   *   The content moderation information service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    ParagraphBlocksEntityManager $paragraph_blocks_manager,
    ParagraphBlocksLabeller $paragraph_blocks_labeller,
    EntityDisplayRepository $entity_display_repository,
    UuidInterface $uuid_service,
    ?ModerationInformationInterface $moderation_information = NULL,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->paragraphBlocksManager = $paragraph_blocks_manager;
    $this->paragraphBlocksLabeller = $paragraph_blocks_labeller;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->uuidService = $uuid_service;
    if ($this->moduleHandler->moduleExists('content_moderation')) {
      $this->moderationInformation = $moderation_information;
    }

    // Get the field delta from the plugin.
    [, $entity_type_id, $field_name, $field_delta] = explode(':', $plugin_id);
    $this->entityTypeId = $entity_type_id;
    $this->fieldName = $field_name;
    $this->fieldDelta = $field_delta;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('paragraph_blocks.entity_manager'),
      $container->get('paragraph_blocks.labeller'),
      $container->get('entity_display.repository'),
      $container->get('uuid'),
      $container->has('content_moderation.moderation_information')
        ? $container->get('content_moderation.moderation_information') : NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label' => '',
      'label_display' => FALSE,
      'display_mode' => 'default',
      'paragraph_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $entity = $this->paragraphBlocksManager->getRefererEntity();

    if (isset($entity)) {
      $config = $this->configFactory->get('paragraph_blocks.settings');
      $paragraph = $this->getLatestParagraph($entity);
      if (empty($paragraph)) {
        $admin_title = $this->uuidService->generate();
      }
      elseif (!$paragraph->hasAdminTitle()) {
        $admin_title = $paragraph->getSummary();
      }
      else {
        $admin_title = $paragraph->getAdminTitle();
      }
      $orig_title = $form['admin_label']['#plain_text'];
      $form['admin_label']['#plain_text'] = $admin_title;
      // Only change if it's currently the default title.
      if ($form['label']['#default_value'] == $orig_title) {
        $form['label']['#default_value'] = $admin_title;
        if ($config->get('suppress_label')) {
          $form['label']['#type'] = 'hidden';
          $form['label_display']['#type'] = 'hidden';
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;
    // Try entity-ID-based lookup first, fall back to delta-based.
    $paragraph_block = NULL;
    if (!empty($config['paragraph_id'])) {
      $paragraph_block = $this->entityTypeManager->getStorage('paragraph')->load($config['paragraph_id']);
    }
    if (!$paragraph_block) {
      $paragraph_block = $this->paragraphBlocksLabeller->getParagraph($config['id']);
    }
    if (!$paragraph_block) {
      // Preserve the existing display_mode so blockSubmit does not null it out.
      return [
        'display_mode' => [
          '#type' => 'hidden',
          '#value' => $config['display_mode'],
        ],
      ];
    }
    if ($this->paragraphBlocksLabeller->isParagraphFromLibrary($paragraph_block)) {
      $paragraph_block = $this->paragraphBlocksLabeller->getParagraphFromLibrary($paragraph_block);
    }
    $options = $this->entityDisplayRepository->getViewModeOptionsByBundle('paragraph', $paragraph_block->bundle());

    $form['display_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Display mode'),
      '#default_value' => $config['display_mode'],
      '#options' => $options,
      '#description' => $this->t('The display mode of the selected paragraph.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['label'] = $form_state->getValue('label');
    $this->configuration['label_display'] = $form_state->getValue('label_display');
    $this->configuration['display_mode'] = $form_state->getValue('display_mode');
    $entity = $this->paragraphBlocksManager->getRefererEntity();
    if (isset($entity)) {
      // Store the directly referenced paragraph ID (e.g. the from_library
      // wrapper), not the resolved library content ID. This ensures build()
      // can find the paragraph in the entity's field references.
      $paragraph = $this->getReferencedParagraph($entity);
      if (!$paragraph) {
        $fresh_entity = $this->paragraphBlocksManager->loadFreshEntity($entity);
        if ($fresh_entity !== $entity) {
          $paragraph = $this->getReferencedParagraph($fresh_entity);
        }
      }
      if ($paragraph) {
        $this->configuration['paragraph_id'] = (int) $paragraph->id();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $paragraph = NULL;
    // Get the referencing and referenced entity.
    $entity = $this->getContextEntity();

    if ($entity) {
      $paragraph = $this->getLatestParagraph($entity);
    }
    if (!$paragraph) {
      // The Paragraphs group block exists on the page, but the page's
      // Paragraphs group has been removed.
      return [
        '#markup' => $this->t('This block is broken. The Paragraphs group or the paragraph does not exist.'),
      ];
    }

    // Build the render array.
    /** @var \Drupal\Core\Entity\EntityViewBuilder $view_builder */
    $view_builder = $this->entityTypeManager->getViewBuilder($paragraph->getEntityTypeId());
    $config = $this->getConfiguration();
    $build = $view_builder->view($paragraph, $config['display_mode']);

    // Set the cache data appropriately.
    CacheableMetadata::createFromObject($this->getContext('entity'))
      ->applyTo($build);

    return $build;
  }

  /**
   * Return the entity that contains the paragraph.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity that holds the paragraph field.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   */
  protected function getContextEntity(): EntityInterface {
    return $this->getContextValue('entity');
  }

  /**
   * Get latest published paragraph from entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that holds the paragraph field.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph|null
   *   The paragraph.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getLatestParagraph(ContentEntityInterface $entity): ?Paragraph {
    $paragraph = $this->getReferencedParagraph($entity);

    // If paragraph not found, try loading a fresh entity from the database.
    if (is_null($paragraph)) {
      $fresh_entity = $this->paragraphBlocksManager->loadFreshEntity($entity);
      if ($fresh_entity !== $entity) {
        $paragraph = $this->getReferencedParagraph($fresh_entity);
      }
    }

    // Check if this is a paragraphs library item that we need to get the
    // referenced paragraph for.
    if (!is_null($paragraph) && $this->paragraphBlocksLabeller->isParagraphFromLibrary($paragraph)) {
      $paragraph = $this->paragraphBlocksLabeller->getParagraphFromLibrary($paragraph);
    }

    return $paragraph;
  }

  /**
   * Get referenced paragraph from entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that holds the entity field.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph|null
   *   The paragraph.
   */
  private function getReferencedParagraph(ContentEntityInterface $entity): ?Paragraph {
    $referenced_entities = $entity
      ->get($this->fieldName)
      ->referencedEntities();
    // Look up by entity ID if available.
    if (!empty($this->configuration['paragraph_id'])) {
      foreach ($referenced_entities as $referenced_entity) {
        if ($referenced_entity->id() == $this->configuration['paragraph_id']) {
          return $referenced_entity;
        }
      }
      return NULL;
    }
    // Fall back to delta-based lookup for backward compatibility.
    if (isset($referenced_entities[$this->fieldDelta])) {
      return $referenced_entities[$this->fieldDelta];
    }
    return NULL;
  }

}
