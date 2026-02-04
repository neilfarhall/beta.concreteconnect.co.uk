<?php

namespace Drupal\Tests\responsive_image_preload\Unit;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\responsive_image_preload\FieldPreprocessor;
use Drupal\responsive_image_preload\PreloadGeneratorInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Test cases for the field preprocessor service.
 *
 * @group responsive_image_preload
 */
class FieldPreprocessorTest extends UnitTestCase {

  /**
   * The subject under test.
   *
   * @var \Drupal\responsive_image_preload\FieldPreprocessor
   */
  protected $instance;

  /**
   * The mocked display settings.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $display;

  /**
   * The mocked display storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $preload_generator = $this->createMock(PreloadGeneratorInterface::class);

    $preload_generator
      ->method('generatePreloads')
      ->willReturn([
        'test-preload-key' => ['test-preload-data'],
      ]);

    $this->display = $this->createMock(EntityViewDisplayInterface::class);

    $this->storage = $this->createMock(EntityStorageInterface::class);

    $this->storage->method('loadByProperties')
      ->willReturnCallback(function ($properties) {
        if ($properties['id'] === 'test_entity.test.disabled') {
          return NULL;
        }
        return [$this->display];
      });

    $this->storage
      ->method('load')
      ->willReturnCallback(function ($name) {
        return $name === 'entity_test.test.test' ? NULL : $this->display;
      });

    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);


    $entity_type_manager->method('getStorage')
      ->willReturn($this->storage);

    $this->instance = new FieldPreprocessor($preload_generator, $entity_type_manager);
  }

  /**
   * Sets the enabled state of the 'generate_preloads' third party setting.
   *
   * @param bool $enabled
   *   The new state of the 'generate_preloads' third party setting.
   */
  protected function setPreloadsEnabled($enabled) {
    $this->display
      ->method('getComponent')
      ->willReturn([
        'third_party_settings' => [
          'responsive_image_preload' => [
            'generate_preloads' => $enabled,
          ],
        ],
      ]
      );
  }

  /**
   * Gets a mocked entity instance.
   *
   * @param string $bundle
   *   The bundle of the entity.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface|\PHPUnit\Framework\MockObject\MockObject
   *   A mocked entity instance.
   */
  protected function getMockEntity($bundle = 'test') {
    $entity = $this->createMock(FieldableEntityInterface::class);

    $entity->method('getEntityTypeId')
      ->willReturn('test');
    $entity->method('bundle')
      ->willReturn($bundle);

    return $entity;
  }

  /**
   * Test case for when an unsupported formatter is provided.
   */
  public function testFieldPreprocessorWrongFormatter() {
    $variables = [
      'element' => [
        '#formatter' => 'not-the-right-one',
      ],
    ];

    $this->instance->preprocessField($variables);

    static::assertArrayNotHasKey('#attached', $variables);
  }

  /**
   * Test case for when preload generation is disabled.
   */
  public function testFieldPreprocessorPreloadsTurnedOff() {

    $variables = [
      'element' => [
        '#formatter' => 'responsive_image',
        '#object' => $this->getMockEntity(),
        '#view_mode' => 'test',
        '#field_name' => 'test',
      ],
    ];

    $this->setPreloadsEnabled(FALSE);

    $this->instance->preprocessField($variables);

    static::assertArrayNotHasKey('#attached', $variables);

  }

  /**
   * Test case for when preload generation is enabled.
   */
  public function testFieldPreprocessorTurnedOn() {

    $variables = [
      'element' => [
        '#formatter' => 'responsive_image',
        '#object' => $this->getMockEntity(),
        '#view_mode' => 'test',
        '#field_name' => 'test',
      ],
    ];

    $this->setPreloadsEnabled(TRUE);

    $this->instance->preprocessField($variables);

    $expected = [
      'html_head' => [[
        ['test-preload-data'],
        'test-preload-key',
      ],
      ],
    ];
    static::assertSame($expected, $variables['#attached']);
  }

  /**
   * Test case for when a view mode is overridden in config, but disabled.
   */
  public function testFieldPreprocessorDisabledViewMode() {
    $variables = [
      'element' => [
        '#formatter' => 'responsive_image',
        '#object' => $this->getMockEntity(),
        '#view_mode' => 'disabled',
        '#field_name' => 'test',
      ],
    ];

    $this->setPreloadsEnabled(TRUE);

    $this->instance->preprocessField($variables);

    $expected = [
      'html_head' => [[
        ['test-preload-data'],
        'test-preload-key',
      ],
      ],
    ];
    static::assertSame($expected, $variables['#attached']);

  }

  /**
   * Test case for when a view mode is not overridden.
   */
  public function testFieldPreprocessorDefaultViewMode() {
    $variables = [
      'element' => [
        '#formatter' => 'responsive_image',
        '#object' => $this->getMockEntity('no_view_mode_override'),
        '#view_mode' => 'test',
        '#field_name' => 'test',
      ],
    ];

    $this->setPreloadsEnabled(TRUE);

    $this->instance->preprocessField($variables);

    $expected = [
      'html_head' => [[
        ['test-preload-data'],
        'test-preload-key',
      ],
      ],
    ];
    static::assertSame($expected, $variables['#attached']);
  }

  /**
   * Test case for when a formatter configuration lacks third party settings.
   */
  public function testFieldPreprocessorNoThirdPartySettingsDefined() {
    $variables = [
      'element' => [
        '#formatter' => 'responsive_image',
        '#object' => $this->getMockEntity(),
        '#view_mode' => 'test',
        '#field_name' => 'test',
      ],
    ];

    $this->display
      ->method('getComponent')
      ->willReturn([
        'third_party_settings' => [],
      ],
    );

    $this->instance->preprocessField($variables);

    static::assertArrayNotHasKey('#attached', $variables);
  }

}
