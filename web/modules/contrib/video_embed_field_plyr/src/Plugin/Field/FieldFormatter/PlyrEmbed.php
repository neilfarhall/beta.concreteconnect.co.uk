<?php

namespace Drupal\video_embed_field_plyr\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Plugin implementation of the 'video_embed_field_plyr' formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_field_plyr",
 *   label = @Translation("Video (plyr)"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class PlyrEmbed extends FormatterBase implements ContainerFactoryPluginInterface {

  use PlyrSharedTrait;

  protected ProviderManagerInterface $providerManager;

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\video_embed_field\ProviderManagerInterface $provider_manager
   *   The video embed provider manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, $settings, $label, $view_mode, $third_party_settings, ProviderManagerInterface $provider_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->providerManager = $provider_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('video_embed_field.provider_manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $provider = $this->providerManager->loadProviderFromInput($item->value);
      if (!$provider) {
        $element[$delta] = ['#theme' => 'video_embed_field_missing_provider'];
      }
      else {
        // Should this be configurable?
        $embedCode = $provider->renderEmbedCode(1920, 1080, FALSE);
        $element[$delta] = [
          '#theme' => 'video_embed_plyr',
          '#videoId' => $provider::getIdFromInput($item->value),
          '#url' => $embedCode['#url'],
          '#provider' => $embedCode['#provider'],
          '#attributes' => [
            'allow' => "autoplay",
            'id' => Html::getUniqueId('video-embed-plyr-' . $delta),
            'class' => [Html::cleanCssIdentifier('video-embed-field-plyr')],
          ],
        ];
        $this->attachPlyrLibraries($element, $delta);
      }
    }
    return $element;
  }


}
