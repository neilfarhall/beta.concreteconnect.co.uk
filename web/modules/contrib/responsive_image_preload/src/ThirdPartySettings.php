<?php

namespace Drupal\responsive_image_preload;

use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * A unit-testable wrapper class around third party settings hook logic.
 *
 * @internal
 */
class ThirdPartySettings {

  use StringTranslationTrait;

  /**
   * Generates the third party settings form elements.
   *
   * @param \Drupal\Core\Field\FormatterInterface $plugin
   *   The formatter to generate a form for.
   *
   * @return array
   *   A build array containing third party settings.
   */
  public function settingsForm(FormatterInterface $plugin) {
    $element = [];
    if ($plugin->getPluginId() === 'responsive_image') {
      $element['generate_preloads'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Generate preloads'),
        '#default_value' => $plugin->getThirdPartySetting('responsive_image_preload', 'generate_preloads'),
      ];
    }
    return $element;
  }

  /**
   * Alters the provided summary based on the provided context.
   *
   * @param array $summary
   *   The summary to alter.
   * @param array $context
   *   The context of the formatter.
   */
  public function settingsSummaryAlter(array &$summary, array $context) {
    if (isset($context['formatter'])) {
      $formatter = $context['formatter'];
      if ($formatter instanceof FormatterInterface && $formatter->getPluginId() === 'responsive_image' && $formatter->getThirdPartySetting('responsive_image_preload', 'generate_preloads')) {
        $summary[] = $this->t('Preloads will be generated');
      }
    }
  }

}
