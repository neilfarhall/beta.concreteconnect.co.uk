<?php
/**
 * Contains the shared logic of both Plyr formatters.
 */


namespace Drupal\video_embed_field_plyr\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use stdClass;

trait PlyrSharedTrait
{

    /**
     * Attach the correct libraries for this field instance
     *
     * @param array $element
     * @param $delta
     */
    public function attachPlyrLibraries(array &$element, $delta): void
    {

        $element[$delta]['#plyr_settings'] = $this->buildPlyrDrupalSettings($element[$delta]['#provider']);
        $element[$delta]['#attached']['library'][] = 'video_embed_field_plyr/video_embed_field_plyr.plyr-styles';

        if (TRUE === (bool)$this->settings['advanced']['polyfilled']) {
            $element[$delta]['#attached']['library'][] = 'video_embed_field_plyr/video_embed_field_plyr.polyfilled';
        } else {
            $element[$delta]['#attached']['library'][] = 'video_embed_field_plyr/video_embed_field_plyr.modern';
        }
        if (TRUE === (bool)$this->settings['advanced']['rangetouch']) {
            $element[$delta]['#attached']['library'][] = 'video_embed_field_plyr/video_embed_field_plyr.rangetouch';
        }
        $element[$delta]['#attached']['library'][] = 'video_embed_field_plyr/video_embed_field_plyr.drupal';

    }


    /**
     * Build the settings array that is passed to the Twig template
     * to build up the data-attributes per player instance.
     *
     * @return array
     */
    public function buildPlyrDrupalSettings($provider)
    {

        if ($this->isBackgroundEnabled()) {
            $settings = $this->buildBackgroundPlyrSettings($provider);
        } else {
            $settings = $this->buildDefaultPlyrSettings($provider);
        }
        // attach drupal thumbnail instead of loading it remote. Allows to override on render level
        //$settings['previewThumbnails'] = "";
        return $settings;
    }

    public function buildDefaultPlyrSettings($provider)
    {
        $settings = [];
        $settings['background'] = FALSE;
        foreach ($this->settings as $settingName => $settingValue) {
            if ($settingName === 'youtube') {
                continue;
            }
            if ($settingName === 'controls') {
                $controls = [];
                foreach ($settingValue as $controlName => $controlValue) {
                    if (((bool)$controlValue)) {
                        $controls[] = $controlName;
                    }
                }
                $settings['controls'] = $controls;
            } elseif ((bool)$settingValue) {
                $settings[$settingName] = TRUE;
            }
        }
        $settings['youtube'] = $this->youtubeNoCookie();
        return $settings;
    }

    public function isBackgroundEnabled()
    {
        return (bool)$this->settings['advanced']['background'];
    }

    public function buildBackgroundPlyrSettings($provider)
    {
        $settings = [];
        $settings['background'] = TRUE;

        $settings['clickToPlay'] = FALSE;
        $settings['hideControls'] = TRUE;
        $settings['controls'] = [];
        $settings['loadSprite'] = FALSE;
        $settings['autoplay'] = TRUE;
        $settings['muted'] = TRUE;
        $settings['volume'] = 0;
        $settings['autopause'] = FALSE;
        $settings['settings']['loop'] = TRUE;
        $settings['playsinline'] = TRUE;
        $settings['displayDuration'] = FALSE;
        $settings['keyboard'] = new stdClass();
        $settings['keyboard']->focused = false;
        $settings['keyboard']->global = false;


//        if ($provider === 'vimeo') {
//          @adjust when needed, see https://github.com/vimeo/player.js/#embed-options
//        }
        if ($provider === 'youtube') {
            // @see https://developers.google.com/youtube/player_parameters#Parameters
            $settings['youtube'] = $this->youtubeNoCookie();
        }
        return $settings;
    }

    public function youtubeNoCookie(): stdClass
    {
        $youTube = new \stdClass();
        $youTube->noCookie = $this->settings['youtube']['noCookie'];
        return $youTube;
    }

    public static function defaultSettings()
    {

        return [
            'autoplay' => FALSE,
            'loop' => FALSE,
            'resetOnEnd' => TRUE,
            'hideControls' => TRUE,
            'controls' => [
                'play-large' => FALSE,
                'play' => TRUE,
                'restart' => FALSE,
                'fast-forward' => FALSE,
                'progress' => TRUE,
                'current-time' => TRUE,
                'duration' => FALSE,
                'mute' => TRUE,
                'volume' => TRUE,
                'settings' => TRUE,
                'captions' => FALSE,
                'airplay' => FALSE,
                'fullscreen' => TRUE,
                'pip' => FALSE,
            ],
            'advanced' => [
                'polyfilled' => FALSE,
                'rangetouch' => TRUE,
                'background' => FALSE,
            ],
            'youtube' => [
                'noCookie' => FALSE,
            ],
        ];
    }

    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $elements = parent::settingsForm($form, $form_state);
        $settings = $this->getSettings();

        $elements['autoplay'] = [
            '#title' => $this->t('Autoplay'),
            '#type' => 'checkbox',
            '#description' => $this->t('Autoplay the media on load. This is generally advised against on UX grounds. It is also disabled by default in some browsers.'),
            '#default_value' => $settings['autoplay'],
        ];
        $elements['loop'] = [
            '#title' => $this->t('Loop'),
            '#type' => 'checkbox',
            '#description' => $this->t('Loop the video'),
            '#default_value' => $settings['loop'],
        ];
        $elements['resetOnEnd'] = [
            '#title' => $this->t('Reset on end'),
            '#type' => 'checkbox',
            '#description' => $this->t('Reset the playback to the start once playback is complete.'),
            '#default_value' => $settings['resetOnEnd'],
        ];
        $elements['hideControls'] = [
            '#title' => $this->t('Hide controls'),
            '#type' => 'checkbox',
            '#description' => $this->t('Hide video controls automatically after 2s of no mouse or focus movement, on control element blur (tab out), on playback start or entering fullscreen.'),
            '#default_value' => $settings['hideControls'],
        ];


        $elements['controls'] = [
            '#title' => $this->t('Plyr Controls'),
            '#type' => 'fieldset',
        ];

        $elements['controls']['play'] = [
            '#title' => $this->t('Play'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show play/pause button'),
            '#default_value' => $settings['controls']['play'],
        ];

        $elements['controls']['play-large'] = [
            '#title' => $this->t('Play large'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show large play button'),
            '#default_value' => $settings['controls']['play-large'],
        ];

        $elements['controls']['restart'] = [
            '#title' => $this->t('Restart'),
            '#type' => 'checkbox',
            '#description' => $this->t('Restart playback'),
            '#default_value' => $settings['controls']['restart'],
        ];

        $elements['controls']['fast-forward'] = [
            '#title' => $this->t('Fast forward'),
            '#type' => 'checkbox',
            '#description' => $this->t('Fast forward by 10 seconds'),
            '#default_value' => $settings['controls']['fast-forward'],
        ];


        $elements['controls']['mute'] = [
            '#title' => $this->t('Mute'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show toggle mute button'),
            '#default_value' => $settings['controls']['mute'],
        ];

        $elements['controls']['airplay'] = [
            '#title' => $this->t('Airplay'),
            '#type' => 'checkbox',
            '#description' => $this->t('Enable airplay'),
            '#default_value' => $settings['controls']['airplay'],
        ];

        $elements['controls']['pip'] = [
            '#title' => $this->t('Picture-in-picture'),
            '#type' => 'checkbox',
            '#description' => $this->t('Picture-in-picture (limited browser support)'),
            '#default_value' => $settings['controls']['pip'],
        ];

        $elements['controls']['fullscreen'] = [
            '#title' => $this->t('Fullscreen'),
            '#type' => 'checkbox',
            '#description' => $this->t('Allow fullscreen playback'),
            '#default_value' => $settings['controls']['fullscreen'],
        ];
        $elements['controls']['progress'] = [
            '#title' => $this->t('Progress'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show progress bar'),
            '#default_value' => $settings['controls']['progress'],
        ];
        $elements['controls']['volume'] = [
            '#title' => $this->t('Volume'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show volume controls'),
            '#default_value' => $settings['controls']['volume'],
        ];

        $elements['controls']['current-time'] = [
            '#title' => $this->t('Current time'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show current time of playback'),
            '#default_value' => $settings['controls']['current-time'],
        ];

        $elements['controls']['duration'] = [
            '#title' => $this->t('Duration'),
            '#type' => 'checkbox',
            '#description' => $this->t('Show duration if available'),
            '#default_value' => $settings['controls']['duration'],
        ];

        $elements['controls']['settings'] = [
            '#title' => $this->t('Quality settings'),
            '#type' => 'checkbox',
            '#description' => $this->t('Playback quality settings'),
            '#default_value' => $settings['controls']['settings'],
        ];
        $elements['advanced'] = [
            '#title' => $this->t('Advanced settings'),
            '#type' => 'fieldset',
        ];

        $elements['advanced']['background'] = [
            '#title' => $this->t('Enable background playback options'),
            '#type' => 'checkbox',
            '#description' => $this->t('Fully disable the controls and enable Autoplay with loop<br/>Add parameters to the embedded video that try to optimize for background playback as much as Vimeo or Youtube allows'),
            '#default_value' => $settings['advanced']['background'],
        ];
        $elements['advanced']['polyfilled'] = [
            '#title' => $this->t('Use polyfilled version'),
            '#type' => 'checkbox',
            '#description' => $this->t('Use the IE11 polyfilled version'),
            '#default_value' => $settings['advanced']['polyfilled'],
        ];
        $elements['advanced']['rangetouch'] = [
            '#title' => $this->t('Add RangeTouch library'),
            '#type' => 'checkbox',
            '#description' => $this->t('This improves `input type="range"` on mobile devices.'),
            '#default_value' => $settings['advanced']['rangetouch'],
        ];

        $elements['youtube'] = [
            '#title' => $this->t('YouTube settings'),
            '#type' => 'fieldset',
        ];

        $elements['youtube']['noCookie'] = [
            '#title' => $this->t('No cookie player'),
            '#type' => 'checkbox',
            '#description' => $this->t('Use the no Cookie YouTube player.'),
            '#default_value' => $settings['youtube']['noCookie'],
        ];

        return $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function settingsSummary()
    {
        $settings = $this->getSettings();
        $global = [];
        $controls = [];
        if (TRUE === (bool)$settings['advanced']['background']) {
            $global[] = $this->t('Background playback mode');
            $controls[] = $this->t('Disabled');
        } else {
            if (TRUE === (bool)$settings['autoplay']) {
                $global[] = $this->t('Autoplaying');
            }
            if (TRUE === (bool)$settings['loop']) {
                $global[] = $this->t('Looping');
            }
            if (TRUE === (bool)$settings['resetOnEnd']) {
                $global[] = $this->t('Reset on end');
            }
            if (TRUE === (bool)$settings['hideControls']) {
                $global[] = $this->t('auto hide control');
            }
            if (TRUE === (bool)$settings['advanced']['polyfilled']) {
                $global[] = $this->t('Polyfilled version');
            } else {
                $global[] = $this->t('Modern version');
            }
            if (TRUE === (bool)$settings['advanced']['rangetouch']) {
                $global[] = $this->t('RangeTouch enabled');
            }

            foreach ($settings['controls'] as $name => $value) {
                if (TRUE === (bool)$value) {
                    $controls[] = $this->t(ucfirst($name));
                }
            }
        }


        $summary = [];
        $summary[] = $this->t('@global', ['@global' => implode(', ', $global)]);
        $summary[] = $this->t('Controls: @controls', ['@controls' => implode(', ', $controls)]);
        return $summary;
    }

}
