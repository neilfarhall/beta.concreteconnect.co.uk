<?php

namespace Drupal\Tests\responsive_image_preload\Unit;

use Drupal\Core\Field\FormatterInterface;
use Drupal\responsive_image_preload\ThirdPartySettings;
use Drupal\Tests\UnitTestCase;

/**
 * Test cases for the field preprocessor service.
 *
 * @group responsive_image_preload
 */
class ThirdPartySettingsTest extends UnitTestCase {

  /**
   * The subject under test.
   *
   * @var \Drupal\responsive_image_preload\ThirdPartySettings
   */
  protected $instance;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->instance = new ThirdPartySettings();
    $this->instance->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Gets a mocked plugin instance.
   *
   * @param string $id
   *   The ID of the plugin.
   * @param bool $generate_preloads
   *   Should preloads be enabled?
   *
   * @return \Drupal\Core\Field\FormatterInterface|\PHPUnit\Framework\MockObject\MockObject
   *   A mocked plugin instance.
   */
  protected function getMockPlugin($id, $generate_preloads = FALSE) {
    $plugin = $this->createMock(FormatterInterface::class);

    $plugin
      ->method('getPluginId')
      ->willReturn($id);

    $plugin
      ->method('getThirdPartySetting')
      ->willReturn($generate_preloads);

    return $plugin;
  }

  /**
   * Test case for when an unsupported formatter is provided.
   */
  public function testSettingsFormWrongFormatter() {
    $plugin = $this->getMockPlugin('not-the-right-one');
    static::assertEmpty($this->instance->settingsForm($plugin));
  }

  /**
   * Test case for when a supported formatter is provided and TPS is disabled.
   */
  public function testSettingsForm() {
    $plugin = $this->getMockPlugin('responsive_image');
    static::assertEquals([
      'generate_preloads' => [
        '#type' => 'checkbox',
        '#title' => $this->getStringTranslationStub()->translate('Generate preloads'),
        '#default_value' => FALSE,
      ],
    ], $this->instance->settingsForm($plugin));
  }

  /**
   * Test case for when a supported formatter is provided and TPS is enabled.
   */
  public function testSettingsFormEnabled() {
    $plugin = $this->getMockPlugin('responsive_image', TRUE);
    static::assertEquals([
      'generate_preloads' => [
        '#type' => 'checkbox',
        '#title' => $this->getStringTranslationStub()->translate('Generate preloads'),
        '#default_value' => TRUE,
      ],
    ], $this->instance->settingsForm($plugin));
  }

  /**
   * Test case for when an unsupported formatter is provided.
   */
  public function testSettingsSummaryAlterWrongFormatter() {
    $plugin = $this->getMockPlugin('not-the-right-one');

    $summary = [];
    $context = [
      'formatter' => $plugin,
    ];

    $this->instance->settingsSummaryAlter($summary, $context);

    static::assertEmpty($summary);
  }

  /**
   * Test case for when a supported formatter is provided and TPS is disabled.
   */
  public function testSettingsSummaryAlter() {
    $plugin = $this->getMockPlugin('responsive_image');

    $summary = [];
    $context = [
      'formatter' => $plugin,
    ];

    $this->instance->settingsSummaryAlter($summary, $context);

    static::assertEmpty($summary);
  }

  /**
   * Test case for when a supported formatter is provided and TPS is enabled.
   */
  public function testSettingsSummaryAlterEnabled() {
    $plugin = $this->getMockPlugin('responsive_image', TRUE);

    $summary = [];
    $context = [
      'formatter' => $plugin,
    ];

    $this->instance->settingsSummaryAlter($summary, $context);

    static::assertEquals([
      $this->getStringTranslationStub()->translate('Preloads will be generated'),
    ], $summary);
  }

}
