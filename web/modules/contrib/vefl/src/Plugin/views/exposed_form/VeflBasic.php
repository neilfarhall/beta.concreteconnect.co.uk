<?php

namespace Drupal\vefl\Plugin\views\exposed_form;

use Drupal\vefl\Vefl;
use Drupal\views\Plugin\views\exposed_form\Basic;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposed form plugin that provides a basic exposed form with layout.
 *
 * @ingroup views_exposed_form_plugins
 *
 * @ViewsExposedForm(
 *   id = "vefl_basic",
 *   title = @Translation("Basic (with layout)"),
 *   help = @Translation("Adds layout settings for Exposed form")
 * )
 */
class VeflBasic extends Basic {
  use VeflTrait;

  /**
   * The vefl layout helper.
   *
   * @var \Drupal\vefl\Vefl
   */
  protected $vefl;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vefl\Vefl $vefl
   *   The vefl layout helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Vefl $vefl) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vefl = $vefl;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vefl.layout')
    );
  }

}
