<?php

namespace Drupal\ad_track\Controller;

use Drupal\ad\Bucket\BucketFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Click track controller.
 */
class ClickTrackController extends ControllerBase {

  /**
   * The AD bucket factory.
   *
   * @var \Drupal\ad\Bucket\BucketFactoryInterface
   */
  protected BucketFactoryInterface $bucketFactory;

  /**
   * ClickTrackController constructor.
   *
   * @param \Drupal\ad\Bucket\BucketFactoryInterface $bucket_factory
   *   The AD tracker factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(BucketFactoryInterface $bucket_factory, AccountInterface $current_user) {
    $this->bucketFactory = $bucket_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ad.bucket_factory'),
      $container->get('current_user')
    );
  }

  /**
   * Tracks an AD click event.
   *
   * @param string $bucket_id
   *   The bucket identifier.
   * @param string $ad_id
   *   The AD identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response pointing to the AD target URL.
   */
  public function track(string $bucket_id, string $ad_id, Request $request): RedirectResponse {
    // @todo Add CSRF protection.
    $bucket = $this->bucketFactory->get($bucket_id);
    $ad = $bucket->getAd($ad_id);

    if ($ad) {
      $context = $request->query->all();
      $bucket->getTracker()->trackClick($ad, $this->currentUser, $context);

      $url = $ad->getTargetUrl()
        ->setAbsolute()
        ->toString(TRUE)
        ->getGeneratedUrl();

      $response = new TrustedRedirectResponse($url);
      $response->getCacheableMetadata()
        ->setCacheMaxAge(0);
      return $response;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

}
