<?php

namespace Drupal\responsive_image_preload;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A unit-testable wrapper class around preprocess field hook logic.
 *
 * @internal
 */
class FieldPreprocessor {

  /**
   * The preload generator service.
   *
   * @var \Drupal\responsive_image_preload\PreloadGeneratorInterface
   */
  protected $preloadGenerator;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a field preprocessor service instance.
   *
   * @param \Drupal\responsive_image_preload\PreloadGeneratorInterface $preload_generator
   *   The preload generator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(PreloadGeneratorInterface $preload_generator, EntityTypeManagerInterface $entity_type_manager) {
    $this->preloadGenerator = $preload_generator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Preprocesses the provided field variables.
   *
   * @param array $variables
   *   The variables to preprocess.
   */
  public function preprocessField(array &$variables) {
    if ($variables['element']['#formatter'] === 'responsive_image') {
      /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
      $entity = $variables['element']['#object'];
      $view_mode = $variables['element']['#view_mode'];
      $field_name = $variables['element']['#field_name'];

      /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
      $display = current($this->entityTypeManager->getStorage('entity_view_display')
        ->loadByProperties([
          'id' => $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode,
          'status' => TRUE,
        ]));

      if (!$display) {
        $display = $this->entityTypeManager->getStorage('entity_view_display')
          ->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.default');
      }
      if ($display) {
        $component = $display->getComponent($field_name);
        if (!empty($component['third_party_settings']['responsive_image_preload']['generate_preloads'])) {

          $preloads = $this->preloadGenerator->generatePreloads($variables['element']);
          // Let the preloads bubble up to the attachments processor.
          foreach ($preloads as $key => $preload) {
            $variables['#attached']['html_head'][] = [$preload, $key];
          }
        }
      }
    }
  }

}
