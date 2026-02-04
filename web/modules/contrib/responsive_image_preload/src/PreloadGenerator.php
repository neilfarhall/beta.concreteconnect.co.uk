<?php

namespace Drupal\responsive_image_preload;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Element;
use Drupal\file\FileInterface;
use Drupal\responsive_image\ResponsiveImageStyleInterface;

/**
 * Default implementation for the preload generator service.
 *
 * @package Drupal\responsive_image_preload
 */
class PreloadGenerator implements PreloadGeneratorInterface {

  /**
   * The breakpoint manager service.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The file url generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Creates a preload generator service instance.
   *
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file url generator service.
   */
  public function __construct(BreakpointManagerInterface $breakpoint_manager, EntityTypeManagerInterface $entity_type_manager, ImageFactory $image_factory, FileUrlGeneratorInterface $file_url_generator) {
    $this->breakpointManager = $breakpoint_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->imageFactory = $image_factory;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function generatePreloads(array $elements) {

    $responsive_image_style_storage = $this->entityTypeManager->getStorage('responsive_image_style');
    $preloads = [];
    foreach (Element::children($elements) as $delta) {

      /** @var \Drupal\file\FileInterface $file */
      $file = $elements[$delta]['#item']->entity;

      /** @var \Drupal\responsive_image\ResponsiveImageStyleInterface $responsive_image_style */
      $responsive_image_style = $responsive_image_style_storage->load($elements[$delta]['#responsive_image_style_id']);

      $preloads[] = $this->generatePreloadsForDelta($file, $responsive_image_style);

    }

    return array_merge([], ...$preloads);
  }

  /**
   * Generates preloads for the provided image file and responsive image style.
   *
   * @param \Drupal\file\FileInterface $file
   *   The image file to generate preloads for.
   * @param \Drupal\responsive_image\ResponsiveImageStyleInterface $responsive_image_style
   *   The responsive image style to generate preloads for.
   *
   * @return array
   *   An array of preloads built from the image derivatives.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   This should never happen under normal circumstances.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   This should never happen under normal circumstances.
   */
  protected function generatePreloadsForDelta(FileInterface $file, ResponsiveImageStyleInterface $responsive_image_style) {

    $image_style_storage = $this->entityTypeManager->getStorage('image_style');

    $image = $this->imageFactory->get($file->getFileUri());
    $width = $image->getWidth();
    $height = $image->getHeight();

    $preloads = [];

    $breakpoints = $this->breakpointManager->getBreakpointsByGroup($responsive_image_style->getBreakpointGroup());
    foreach ($responsive_image_style->getKeyedImageStyleMappings() as $breakpoint_id => $multipliers) {
      if (isset($breakpoints[$breakpoint_id])) {
        $breakpoint = $breakpoints[$breakpoint_id];
        $imagesizes = [];
        $src_value = [];
        foreach ($multipliers as $multiplier => $image_style_mapping) {
          switch ($image_style_mapping['image_mapping_type']) {
            case 'sizes':
              foreach ($image_style_mapping['image_mapping']['sizes_image_styles'] as $image_style_name) {
                $dimensions = responsive_image_get_image_dimensions(
                  $image_style_name,
                  ['width' => $width, 'height' => $height],
                  $file->getFileUri()
                );
                $imagesizes[] = explode(',', $image_style_mapping['image_mapping']['sizes']);

                /** @var \Drupal\image\ImageStyleInterface $image_style */
                $image_style = $image_style_storage->load($image_style_name);
                $image_url = $this->fileUrlGenerator->generateString($image_style->buildUrl($file->getFileUri()));

                $src_value[(int) $dimensions['width']] = $image_url . ' ' . $dimensions['width'] . 'w';
              }
              break;

            case 'image_style':
              $image_style_name = $image_style_mapping['image_mapping'];
              /** @var \Drupal\image\ImageStyleInterface $image_style */
              $image_style = $image_style_storage->load($image_style_name);
              $image_url = $this->fileUrlGenerator->generateString($image_style->buildUrl($file->getFileUri()));

              $src_value[(int) (mb_substr($multiplier, 0, -1) * 100)] = $image_url . ' ' . $multiplier;
              break;
          }
          ksort($src_value);
          $src_value = array_unique($src_value);
          $preload = [
            '#type' => 'html_tag',
            '#tag' => 'link',
            '#attributes' => [
              'rel' => 'preload',
              'as' => 'image',
              'imagesrcset' => implode(',', $src_value),
            ],
          ];

          if ($media_query = $breakpoint->getMediaQuery()) {
            $preload['#attributes']['media'] = $media_query;
          }
          if ($imagesizes) {

            // Unpack all of the nested arrays.
            $imagesizes = array_merge(...$imagesizes);
            $preload['#attributes']['imagesizes'] = implode(',', array_unique($imagesizes));
          }

          $preloads["{$file->id()}-{$responsive_image_style->id()}-$breakpoint_id"] = $preload;
        }
      }
    }
    return $preloads;

  }

}
