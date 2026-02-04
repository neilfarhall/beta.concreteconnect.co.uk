<?php

namespace Drupal\video_embed_field_plyr\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\media\Entity\MediaType;
use Drupal\media\IFrameMarkup;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\Resource;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Plugin implementation of the 'video_oembed_field_plyr' formatter.
 *
 * @FieldFormatter(
 *   id = "video_oembed_field_plyr",
 *   label = @Translation("oEmbed Video Plyr"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class PlyrOembed extends FormatterBase implements ContainerFactoryPluginInterface {

  use PlyrSharedTrait;


  /**
   * The oEmbed resource fetcher.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The media settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * Constructs an OEmbedFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
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
   *   Any third party settings.
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, IFrameUrlHelper $iframe_url_helper) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->logger = $logger_factory->get('media');
    $this->config = $config_factory->get('media.settings');
    $this->iFrameUrlHelper = $iframe_url_helper;
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
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('media.oembed.iframe_url_helper')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    foreach ($items as $delta => $item) {
      $main_property = $item->getFieldDefinition()
        ->getFieldStorageDefinition()
        ->getMainPropertyName();
      $iframeURL = $item->{$main_property};

      if (empty($iframeURL)) {
        continue;
      }
      // Make dynamic as well?

      $max_width = 1920;
      $max_height = 1080;

      try {
        $resource_url = $this->urlResolver->getResourceUrl($iframeURL, $max_width, $max_height);
        $resource = $this->resourceFetcher->fetchResource($resource_url);
      } catch (ResourceException $exception) {
        $this->logger->error('Could not retrieve the remote URL (@url).', ['@url' => $iframeURL]);
        continue;
      }
      if ($resource->getType() !== Resource::TYPE_VIDEO) {
        $error = $this->t('@url is not a supported video source.', ['@url' => $iframeURL]);
        $elements[$delta] = ['#markup' => $error->render()];
        continue;
      }

      $videoProvider = strtolower($resource->getProvider()->getName());
      /**
       * Override the URL with extra params if background mode is enabled.
       * And the provider is Vimeo or Youtube.
       */
      if (in_array($videoProvider, ['vimeo', 'youtube'])) {
        $iframeURL = $this->parseUrlFromIframeSource($resource->getHtml());
      }
      $iframeTargetURL = $iframeURL;

      $iframeDomain = $this->config->get('iframe_domain');
      if ($iframeDomain) {
        $iframeTargetURL = Url::fromRoute('media.oembed_iframe', [], [
          'query' => [
            'url' => $iframeURL,
            'max_width' => $max_width,
            'max_height' => $max_height,
            'hash' => $this->iFrameUrlHelper->getHash($iframeURL, $max_width, $max_height),
          ],
        ]);
        $iframeTargetURL->setOption('base_url', $iframeDomain);
      }

      if ($iframeTargetURL instanceof Url){
        $iframeTargetURL = $iframeTargetURL->toString();
      }
      $elements[$delta] = [
        '#theme' => 'video_oembed_plyr',
        '#iframe' => [
          '#type' => 'html_tag',
          '#tag' => 'iframe',
          '#attributes' => [
            'src' => $iframeTargetURL,
            'frameborder' => 0,
            'scrolling' => FALSE,
            'allow' => "autoplay",
            'width' => $resource->getWidth() ?: $max_width,
            'height' => $resource->getHeight() ?: $max_height,
          ],
        ],
        '#provider' => $videoProvider,
        '#attributes' => [
          'id' => Html::getUniqueId('video-embed-plyr-' . $delta),
          'class' => [Html::cleanCssIdentifier('video-embed-field-plyr')],
        ],
      ];
      $this->attachPlyrLibraries($elements, $delta);

      CacheableMetadata::createFromObject($resource)
        ->addCacheTags($this->config->getCacheTags())
        ->applyTo($elements[$delta]);


    }
    return $elements;
  }

  public function parseUrlFromIframeSource($markup) {
    $dom = Html::load($markup);
    $tags = $dom->getElementsByTagName('iframe');
    if ($tags->length > 0) {

      $tag = $tags->item(0);
      $iframeSrc = $tag->getAttribute('src');
      $url_parts = UrlHelper::parse($iframeSrc);
      if ($this->isBackgroundEnabled()) {
        $url_parts['query']['autoplay'] = 1;
        $url_parts['query']['background'] = 1;
        $url_parts['query']['mute'] = 1;
      }
      return Url::fromUri($url_parts['path'], [
        'query' => $url_parts['query'],
        'fragment' => $url_parts['fragment'],
      ]);
    }
  }

  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if ($field_definition->getTargetEntityTypeId() !== 'media') {
      return FALSE;
    }
    if (parent::isApplicable($field_definition)) {
      $media_type = $field_definition->getTargetBundle();
      if ($media_type) {
        $media_type = MediaType::load($media_type);
        return $media_type && $media_type->getSource() instanceof OEmbedInterface;

      }
    }
    return FALSE;
  }

}
