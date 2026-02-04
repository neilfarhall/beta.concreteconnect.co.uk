<?php

namespace Drupal\Tests\responsive_image_preload\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for responsive image preload functional tests.
 */
abstract class ResponsiveImagePreloadBrowserTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'breakpoint',
    'image',
    'responsive_image',
    'responsive_image_preload',
    'responsive_image_preload_test',
  ];

  /**
   * The storage configuration for the test field.
   *
   * @var \Drupal\field\FieldStorageConfigInterface
   */
  protected $fieldStorage;

  /**
   * The configuration for the test field.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $field;

  /**
   * The view display configuration for the test entity.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $entityViewDisplay;

  /**
   * A test entity instance.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   This should never happen under normal circumstances.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'image',
      'entity_type' => 'entity_test',
      'type' => 'image',
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'field_storage' => $this->fieldStorage,
      'label' => 'Image',
      'bundle' => 'entity_test',
    ]);
    $this->field->save();

    $this->entityViewDisplay = EntityViewDisplay::create([
      'targetEntityType' => $this->field->getTargetEntityTypeId(),
      'bundle' => $this->field->getTargetBundle(),
      'mode' => 'full',
      'status' => TRUE,
    ]);
    $this->entityViewDisplay->setComponent('image', [
      'region' => 'content',
      'label' => 'hidden',
      'type' => 'responsive_image',
      'settings' => [
        'responsive_image_style' => 'test1',
      ],
      'third_party_settings' => [
        'responsive_image_preload' => [
          'generate_preloads' => TRUE,
        ],
      ],
    ]);
    $this->entityViewDisplay->save();

    $fixture_path = implode(DIRECTORY_SEPARATOR, [
      \Drupal::service('extension.list.module')->getPath('responsive_image_preload_test'),
      'fixtures',
      'test.jpg',
    ]);
    $file = \Drupal::service('file.repository')->writeData(
      file_get_contents($fixture_path),
      'public://test.jpg'
    );

    $this->entity = EntityTest::create([
      'image' => [
        'target_id' => $file->id(),
        'alt' => 'Test image',
      ],
    ]);
    $this->entity->save();
  }

  /**
   * Sets the responsive image style to be used.
   *
   * @param string $responsive_image_style
   *   The responsive image style to set.
   * @param bool $generate_preload
   *   TRUE if preloading should be enabled, otherwise FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   This should never happen under normal circumstances.
   */
  protected function setFormatterSettings($responsive_image_style, $generate_preload) {
    $component = $this->entityViewDisplay->getComponent('image');
    $component['settings']['responsive_image_style'] = $responsive_image_style;
    $component['third_party_settings']['responsive_image_preload']['generate_preloads'] = $generate_preload;
    $this->entityViewDisplay->setComponent('image', $component)->save();
  }

  /**
   * Asserts that the expected preloads are present.
   *
   * @param array $expected
   *   An associative array of expected preload data.
   */
  protected function assertPreloadsPresent(array $expected) {

    $page = $this->getSession()->getPage();
    $preloads = $page->findAll('css', 'link[rel="preload"]');
    foreach ($preloads as $idx => $preload) {
      static::assertEquals('image', $preload->getAttribute('as'));
      static::assertEquals($expected[$idx]['media'], $preload->getAttribute('media'));

      if (isset($expected[$idx]['image_style'])) {
        $pattern = '~^(/subdirectory)?/sites/simpletest/\d*/files/styles/' . $expected[$idx]['image_style'] . '/public/test.jpg?itok=.*\s1x$~';
        static::assertNotFalse(preg_match($pattern, $preload->getAttribute('image_style')));
      }

      if (isset($expected[$idx]['imagesizes'])) {
        static::assertEquals($expected[$idx]['imagesizes'], $preload->getAttribute('imagesizes'));
      }

      if (isset($expected[$idx]['imagesrcset'])) {

        $imagesrcset = [];
        foreach ($expected[$idx]['imagesrcset'] as $image_style => $width) {
          $imagesrcset[] = '(/subdirectory)?/sites/simpletest/\d*/files/styles/' . $image_style . '/public/test\.jpg\?itok=[a-zA-Z0-9\-_]*\s*' . $width;
        }
        static::assertMatchesRegularExpression('~^' . implode(',\s*', $imagesrcset) . '$~', $preload->getAttribute('imagesrcset'));
      }
    }
  }

}
