<?php

namespace Drupal\ad_track\Plugin\Ad\Track;

use Drupal\ad\AdInterface;
use Drupal\ad\Track\TrackerInterface;
use Drupal\ad_track\TotalStorageInterface;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Plugin(
 *   id = \Drupal\ad_track\Plugin\Ad\Track\LocalTracker::TRACKER_ID,
 *   label = @Translation("Local AD event tracker"),
 * )
 *
 * @internal
 */
class LocalTracker implements TrackerInterface, ContainerFactoryPluginInterface {

  const TRACKER_ID = 'local';

  /**
   * The page view identifier.
   *
   * @var string
   */
  protected static string $pageViewId;

  /**
   * The tracker ID.
   *
   * @var string
   */
  protected string $id;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

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
   * The track total storage.
   *
   * @var \Drupal\ad_track\TotalStorageInterface
   */
  protected TotalStorageInterface $totalStorage;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected UuidInterface $uuidGenerator;

  /**
   * LocalTracker constructor.
   *
   * @param string $id
   *   The tracker ID.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\ad_track\TotalStorageInterface $track_total_storage
   *   The track total storage.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   The UUID generator.
   */
  public function __construct(
    string $id,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    TotalStorageInterface $track_total_storage,
    UuidInterface $uuid_generator
  ) {
    $this->id = $id;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->totalStorage = $track_total_storage;
    $this->uuidGenerator = $uuid_generator;

    if (!isset(static::$pageViewId)) {
      static::$pageViewId = $this->uuidGenerator->generate();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('ad_track.total_storage'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function trackImpression(AdInterface $ad, AccountInterface $user, array $context = []): ?string {
    if (!isset($context['url'])) {
      $context['url'] = Url::fromRoute('<current>')
        ->setAbsolute()
        ->toString(FALSE);
    }

    if (!isset($context['referrer'])) {
      $request = $this->requestStack->getCurrentRequest();
      $context['referrer'] = $request->server->get('HTTP_REFERER');
    }

    $values = [
      'type' => TrackerInterface::EVENT_IMPRESSION,
      'url' => $context['url'],
      'page_title' => $context['page_title'] ?? '',
      'referrer' => $context['referrer'],
      'page_view_id' => static::$pageViewId,
    ];

    return $this->trackEvent($ad, $user, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function trackClick(AdInterface $ad, AccountInterface $user, array $context = []): ?string {
    $values = [
      'type' => TrackerInterface::EVENT_CLICK,
    ];

    if (!empty($context['parent_id'])) {
      $values += $this->getParentValues($context['parent_id']);
    }

    return $this->trackEvent($ad, $user, $values);
  }

  /**
   * Returns the specified parent values.
   *
   * @param string $parent_id
   *   A parent event ID.
   *
   * @return string[]
   *   An associative array of event values.
   */
  protected function getParentValues(string $parent_id): array {
    $values = [
      'parent_id' => $parent_id,
    ];

    try {
      $parent_event = $this->entityRepository->loadEntityByUuid('ad_track_event', $parent_id);
      if ($parent_event) {
        foreach (['url', 'page_title', 'referrer', 'page_view_id'] as $field_name) {
          $values[$field_name] = $parent_event->get($field_name)->value;
        }
      }
    }
    catch (EntityStorageException $e) {
      watchdog_exception('ad_track', $e);
    }

    return $values;
  }

  /**
   * Tracks the specified event.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD to be tracked.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user triggering the AD event.
   * @param array $values
   *   The event values to be stored.
   *
   * @return string|null
   *   The event identifier or NULL if an error occurred.
   */
  protected function trackEvent(AdInterface $ad, AccountInterface $user, array $values): ?string {
    $event_id = NULL;

    $request = $this->requestStack->getCurrentRequest();
    $values['ip_address'] = $request->getClientIp();
    $values['user_agent'] = $request->server->get('HTTP_USER_AGENT');
    $values['uuid'] = $this->uuidGenerator->generate();
    $values['user'] = $user->id();
    $values['ad_id'] = $ad->getAdIdentifier();

    // @todo Implement session handing.
    $values['session'] = '';

    try {
      $event_id = $this->saveEvent($ad, $values);
    }
    catch (EntityStorageException $e) {
      watchdog_exception('ad_track', $e);
      $this->totalStorage->rollbackTransaction($ad);
    }

    return $event_id;
  }

  /**
   * Saves the specified AD event.
   *
   * @param \Drupal\ad\AdInterface $ad
   *   The AD to be tracked.
   * @param array $values
   *   The event values to be stored.
   *
   * @return string|null
   *   The event identifier or NULL if an error occurred.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   If there was an issue storing the event data.
   */
  protected function saveEvent(AdInterface $ad, array $values): ?string {
    $event_id = NULL;

    try {
      $storage = $this->entityTypeManager->getStorage('ad_track_event');

      $this->totalStorage->startTransaction($ad);

      $this->sanitizeValues($values);
      $event = $storage->create($values);
      $storage->save($event);
      $event_id = $event->uuid();

      $this->totalStorage->increaseTotal($values['type'], $ad);
    }
    catch (PluginException $e) {
      watchdog_exception('ad_track', $e);
    }
    catch (\Exception $e) {
      $this->totalStorage->rollbackTransaction($ad);
      $e = $e instanceof EntityStorageException ? $e : new EntityStorageException($e);
      throw $e;
    }

    return $event_id;
  }

  /**
   * Sanitizes user-supplied values.
   *
   * A malicious user may try to insert invalid data in the database, so this
   * needs to be sanitized before save to avoid storage errors.
   *
   * @param array $values
   *   The event values to be stored.
   */
  protected function sanitizeValues(array &$values): void {
    if ($values['url']) {
      $values['url'] = UrlHelper::isValid($values['url']) ? $values['url'] : '';
    }
    if ($values['referrer']) {
      $values['referrer'] = UrlHelper::isValid($values['referrer']) ? $values['referrer'] : '';
    }
    if ($values['user_agent']) {
      $values['user_agent'] = $this->sanitizeString($values['user_agent']);
    }
    if ($values['page_title']) {
      $values['page_title'] = $this->sanitizeString($values['page_title']);
    }
  }

  /**
   * Sanitizes a user-supplied string.
   *
   * @param string $string
   *   The original string.
   *
   * @return string
   *   The sanitized string.
   */
  protected function sanitizeString(string $string): string {
    // @todo Add support for foreign characters.
    return preg_replace('/[^A-Za-z0-9\-_()\/,;|. ]/', '', $string);
  }

}
