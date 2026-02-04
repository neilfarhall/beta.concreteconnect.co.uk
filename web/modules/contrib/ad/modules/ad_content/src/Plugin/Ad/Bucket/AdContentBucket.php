<?php

namespace Drupal\ad_content\Plugin\Ad\Bucket;

use Drupal\ad\AdInterface;
use Drupal\ad\Bucket\BucketInterface;
use Drupal\ad\Size\SizeInterface;
use Drupal\ad\Track\TrackerFactoryInterface;
use Drupal\ad\Track\TrackerInterface;
use Drupal\ad_content\Entity\AdContentInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Basic AD content bucket.
 *
 * @Plugin(
 *   id = \Drupal\ad_content\Entity\AdContentInterface::BUCKET_ID,
 *   label = @Translation("AD Content"),
 * )
 */
class AdContentBucket extends PluginBase implements BucketInterface, ContainerFactoryPluginInterface, TrustedCallbackInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected EntityRepositoryInterface $entityRepository;

  /**
   * The AD tracker factory.
   *
   * @var \Drupal\ad\Track\TrackerFactoryInterface
   */
  protected TrackerFactoryInterface $trackerFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * AdContentBucket constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\ad\Track\TrackerFactoryInterface $tracker_factory
   *   The AD tracker factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    TrackerFactoryInterface $tracker_factory,
    AccountInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->trackerFactory = $tracker_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('ad.tracker_factory'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAd(string $id): ?AdInterface {
    $ad_content = NULL;

    try {
      /** @var \Drupal\ad_content\Entity\AdContentInterface $ad_content */
      $ad_content = $this->entityRepository->loadEntityByUuid('ad_content', $id);
    }
    catch (EntityStorageException $e) {
      watchdog_exception('ad_content', $e);
    }

    return $ad_content;
  }

  /**
   * {@inheritdoc}
   */
  public function getTracker(): TrackerInterface {
    return $this->trackerFactory->get($this->configuration['tracker_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPlaceholder(SizeInterface $size): array {
    $bucket_id = $this->getPluginDefinition()['id'];
    $size_id = $size->getId();
    $html_id = $bucket_id . '-' . $size_id;

    return [
      'placeholder' => [
        '#type' => 'html_tag',
        '#tag' => 'ad-content',
        '#attributes' => [
          'id' => Html::getUniqueId($html_id),
          'bucket' => $bucket_id,
          'size' => $size_id,
        ],
        '#attached' => [
          'library' => ['ad_content/ad_content.render_ads'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildAd(SizeInterface $size): array {
    $build = [];

    $ad_content = $this->getAdContent($size);
    if ($ad_content) {
      $build = $this->entityTypeManager
        ->getViewBuilder($ad_content->getEntityTypeId())
        ->view($ad_content, 'impression');

      $impression_id = $this->getTracker()
        ->trackImpression($ad_content, $this->currentUser, $this->configuration['ad_context']);

      $build['#ad_impression_id'] = $impression_id;
      $build['#post_render'][] = static::class . '::postAdRender';
    }

    return $build;
  }

  /**
   * Returns the AD to be rendered.
   *
   * @param \Drupal\ad\Size\SizeInterface $size
   *   The AD size.
   *
   * @return \Drupal\ad_content\Entity\AdContentInterface
   *   An AD content entity.
   */
  protected function getAdContent(SizeInterface $size): ?AdContentInterface {
    return $this->getRandomAd($size);
  }

  /**
   * Post render callback for the AD view builder.
   */
  public static function postAdRender($markup, array $build) {
    $output = str_replace(TrackerInterface::PLACEHOLDER_IMPRESSION, $build['#ad_impression_id'], $markup);
    return $markup instanceof MarkupInterface ? Markup::create($output) : $output;
  }

  /**
   * Retrieves a random AD of the specified size.
   *
   * @param \Drupal\ad\Size\SizeInterface $size
   *   The AD size.
   *
   * @return \Drupal\ad_content\Entity\AdContentInterface|null
   *   An AD entity or NULL if none could be found.
   */
  protected function getRandomAd(SizeInterface $size): ?AdContentInterface {
    $size_id = $size->getId();
    $ad_content = NULL;

    try {
      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('ad_content');

      $query = $this->getAdQuery($size_id)
        ->accessCheck(TRUE);

      $result = $query
        ->range(0, 1)
        ->sort('id')
        ->execute();

      if ($result) {
        /** @var \Drupal\ad_content\Entity\AdContentInterface $ad_content */
        $ad_content = $storage->load(reset($result));
      }
    }
    catch (PluginException $e) {
      watchdog_exception('ad_content', $e);
    }

    return $ad_content;
  }

  /**
   * Get the base query for finding an ad by size.
   *
   * @param $size_id
   *   The AD size ID.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   Ad base query.
   */
  protected function getAdQuery($size_id): QueryInterface {
    /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('ad_content');

    return $storage->getQuery()
      ->accessCheck()
      ->condition('size', $size_id)
      ->condition('status', 1);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'postAdRender',
    ];
  }

}
