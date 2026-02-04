<?php

namespace Drupal\ad_content\Controller;

use Drupal\ad\Bucket\BucketFactoryInterface;
use Drupal\ad\Size\SizeFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AD impression controller.
 */
class ImpressionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The AD size factory.
   *
   * @var \Drupal\ad\Size\SizeFactory
   */
  protected SizeFactory $sizeFactory;

  /**
   * The AD bucket factory.
   *
   * @var \Drupal\ad\Bucket\BucketFactoryInterface
   */
  protected BucketFactoryInterface $bucketFactory;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Impression constructor.
   *
   * @param \Drupal\ad\Size\SizeFactory $ad_size_factory
   *   The AD size factory.
   * @param \Drupal\ad\Bucket\BucketFactoryInterface $bucket_factory
   *   The AD bucket factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    SizeFactory $ad_size_factory,
    BucketFactoryInterface $bucket_factory,
    RendererInterface $renderer
  ) {
    $this->sizeFactory = $ad_size_factory;
    $this->bucketFactory = $bucket_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ad.size_factory'),
      $container->get('ad.bucket_factory'),
      $container->get('renderer'),
    );
  }

  /**
   * Renders the specified ADs.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function renderAds(Request $request): JsonResponse {
    $response_data = [];
    $request_data = $request->query->all();

    if (!empty($request_data['ads'])) {
      foreach ($request_data['ads'] as $html_id => $data) {
        $size = $this->sizeFactory->get($data['size']);
        $bucket = $this->bucketFactory->get($data['bucket']);
        $build = $bucket->buildAd($size);
        $response_data[$html_id] = $this->renderer->renderPlain($build);
      }
    }

    return new JsonResponse($response_data);
  }

}
