<?php

namespace Drupal\ad\Plugin\Block;

use Drupal\ad\Bucket\BucketFactoryInterface;
use Drupal\ad\Size\SizeFactory;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an AD block type.
 *
 * @Block(
 *  id = "ad",
 *  admin_label = @Translation("Advertisement block"),
 *  category = @Translation("Advertisement"),
 *  deriver = "Drupal\ad\Plugin\Block\Derivative\AdBlockDeriver"
 * )
 */
class AdBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    SizeFactory $size_factory,
    BucketFactoryInterface $bucket_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->sizeFactory = $size_factory;
    $this->bucketFactory = $bucket_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ad.size_factory'),
      $container->get('ad.bucket_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'bucket_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['bucket_id'] = [
      '#type' => 'select',
      '#title' => new TranslatableMarkup('Bucket'),
      '#options' => $this->bucketFactory->getList(),
      '#default_value' => $this->configuration['bucket_id'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['bucket_id'] = $form_state->getValue('bucket_id');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (isset($this->configuration['bucket_id'])) {
      $bucket = $this->bucketFactory->get($this->configuration['bucket_id']);
      $size_id = $this->getDerivativeId();
      $size = $this->sizeFactory->get($size_id);
      return $bucket->buildPlaceholder($size);
    }
    return [];
  }

}
