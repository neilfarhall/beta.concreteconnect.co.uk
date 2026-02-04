<?php

namespace Drupal\ad\Form;

use Drupal\ad\Bucket\BucketFactoryInterface;
use Drupal\ad\Plugin\Ad\Track\NullTracker;
use Drupal\ad\Track\TrackerFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AD settings configuration form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The AD bucket factory.
   *
   * @var \Drupal\ad\Bucket\BucketFactoryInterface
   */
  protected BucketFactoryInterface $bucketFactory;

  /**
   * The AD tracker factory.
   *
   * @var \Drupal\ad\Track\TrackerFactoryInterface
   */
  protected TrackerFactoryInterface$trackerFactory;

  /**
   * AdSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\ad\Bucket\BucketFactoryInterface $bucket_factory
   *   The AD bucket factory.
   * @param \Drupal\ad\Track\TrackerFactoryInterface $tracker_factory
   *   The AD tracker factory.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    BucketFactoryInterface $bucket_factory,
    TrackerFactoryInterface $tracker_factory
  ) {
    parent::__construct($config_factory);
    $this->bucketFactory = $bucket_factory;
    $this->trackerFactory = $tracker_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ad.bucket_factory'),
      $container->get('ad.tracker_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ad_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ad.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ad.settings');
    $settings = $config->get('trackers');
    $trackers = $this->trackerFactory->getList();
    $buckets = $this->bucketFactory->getList();

    $form['trackers'] = [
      '#type' => 'fieldset',
      '#title' => new TranslatableMarkup('Tracker configuration'),
      '#description' => new TranslatableMarkup('Assign an AD statistics tracker for each AD source.'),
      '#tree' => TRUE,
    ];

    if (!$buckets || !array_diff_key($trackers, [NullTracker::TRACKER_ID => TRUE])) {
      $args = [
        '@url' => Url::fromRoute('system.modules_list')
          ->toString(TRUE)
          ->getGeneratedUrl(),
      ];
      $form['trackers']['#description'] = new TranslatableMarkup('You need to <a href="@url">enable</a> at least one AD source and one AD statistics tracker engine.', $args);

      return $form;
    }

    foreach ($buckets as $id => $label) {
      $form['trackers'][$id] = [
        '#type' => 'select',
        '#title' => $label,
        '#required' => TRUE,
        '#options' => $trackers,
        '#default_value' => $settings[$id] ?? key($trackers),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ad.settings')
      ->set('trackers', $form_state->getValue('trackers'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
