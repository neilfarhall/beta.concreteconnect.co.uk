<?php

namespace Drupal\video_embed_media_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the video field formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_media_player",
 *   label = @Translation("Plyr video player"),
 *   description = @Translation("Displays the video with the Plyr library."),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class PlyrVideo extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The embed provider plugin manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * The logged in user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

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
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The logged in user.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ProviderManagerInterface $provider_manager, AccountInterface $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->providerManager = $provider_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('video_embed_field.provider_manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'autoplay' => FALSE,
      'click_to_play' => TRUE,
      'disable_context_menu' => TRUE,
      'controls' => [
        'play-large',
        'play',
        'progress',
        'current-time',
        'mute',
        'volume',
        'captions',
        'fullscreen',
      ],
      'volume' => 5,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      /** @var \Drupal\video_embed_field\ProviderPluginInterface|PluginInspectionInterface $provider */
      $provider = $this->providerManager->loadProviderFromInput($item->value);

      $options = [
        'autoplay' => $this->videoShouldAutoplay(),
        'clickToPlay' => $this->getSetting('click_to_play'),
        'disableContextMenu' => $this->getSetting('disable_context_menu'),
        'controls' => $this->getEnabledControls(),
        'volume' => $this->getSetting('volume'),
      ];

      $element[$delta]['#cache']['contexts'][] = 'user.permissions';

      $element[$delta] = [
        '#theme' => 'plyr_video',
        '#attributes' => [
          'data-plyr' => json_encode($options),
          'data-type' => $provider->getPluginId(),
          'data-video-id' => $provider->getIdFromInput($item->value),
        ],
        '#attached' => [
          'library' => [
            'video_embed_media_player/setup',
          ],
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
      '#description' => $this->t('Autoplay the videos for users without the "never autoplay videos" permission. Roles with this permission will bypass this setting.'),
      '#default_value' => $this->getSetting('autoplay'),
    ];

    $elements['click_to_play'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Click to play'),
      '#description' => $this->t('Click (or tap) of the video container will toggle pause/play.'),
      '#default_value' => $this->getSetting('click_to_play'),
    ];

    $elements['disable_context_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable context menu'),
      '#description' => $this->t('Disable right click menu on video to help as very primitive obfuscation to prevent downloads of content.'),
      '#default_value' => $this->getSetting('disable_context_menu'),
    ];

    $elements['controls'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Contols'),
      '#description' => $this->t('Toggle which control elements you would like to display.'),
      '#options' => $this->getControlOptions(),
      '#default_value' => $this->getSetting('controls'),
    ];

    $elements['volume'] = [
      '#type' => 'select',
      '#title' => $this->t('Volume'),
      '#description' => $this->t('A number, between 1 and 10, representing the initial volume of the player.'),
      '#options' => array_combine(range(1, 10), range(1, 10)),
      '#default_value' => $this->getSetting('volume'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    // Add summary for the the autoplay.
    $summary[] = $this->getSetting('autoplay')
      ? $this->t('Video is played automatically.')
      : $this->t('Video is not played automatically.');

    // Add summary for the click to play setting.
    $summary[] = $this->getSetting('click_to_play')
      ? $this->t('Plays when clicked.')
      : $this->t('Does not play when clicked.');

    // Add a summary for the context menu setting.
    $disable_context_menu = $this->getSetting('disable_context_menu');

    if ($disable_context_menu) {
      $summary[] = $this->t('Right click context menu is disabled.');
    }

    // Add a summary for the enabled controls.
    $enabled_controls = $this->getEnabledControlLabels();

    $summary[] = count($enabled_controls)
      ? $this->t('Enabled controls: @controls.', ['@controls' => implode(', ', $enabled_controls)])
      : $this->t('No controls are enabled.');

    // Add a summary for the initial volume.
    $summary[] = $this->t('Initial volume is set to @volume.', [
      '@volume' => $this->getSetting('volume'),
    ]);

    return $summary;
  }

  /**
   * Check to see if the video should play automatically.
   *
   * @return bool
   *   If the video should play automatically or not.
   */
  private function videoShouldAutoplay() {
    return $this->currentUser->hasPermission('never autoplay videos')
      ? FALSE
      : $this->getSetting('autoplay');
  }

  /**
   * Get the Plyr control options for inputs like checkboxes or select lists.
   *
   * @return array
   *   An array of options.
   */
  private function getControlOptions() {
    return [
      'captions' => $this->t('Captions'),
      'current-time' => $this->t('Current time'),
      'fullscreen' => $this->t('Fullscreen'),
      'mute' => $this->t('Mute'),
      'play' => $this->t('Play'),
      'play-large' => $this->t('Play large'),
      'progress' => $this->t('Progress'),
      'volume' => $this->t('Volume'),
    ];
  }

  /**
   * Get an array of enabled control options.
   *
   * @return array
   *   An array of enabled controls.
   */
  private function getEnabledControls() {
    return array_keys(array_filter($this->getSetting('controls')));
  }

  /**
   * Get the labels for all the enabled controls.
   *
   * @return array
   *   An array of translatable control labels.
   */
  private function getEnabledControlLabels() {
    return array_intersect_key(
      $this->getControlOptions(),
      array_filter($this->getSetting('controls'))
    );
  }

}
