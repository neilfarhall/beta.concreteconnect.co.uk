<?php

namespace Drupal\token_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Cache\Cache;

/**
 * Provides a block with token value.
 *
 * @Block(
 *   id = "token_block",
 *   admin_label = @Translation("Token Block"),
 *   category = @Translation("Token Block"),
 * )
 */
class TokenBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Token service container.
   *
   * @var Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param Drupal\Core\Utility\Token $token
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Token $token
                             ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->token = $token;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token'),      
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'body' => [
        'value' => $this->t(''),
      ]
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function build() {    
    $description = $this->token->replace($this->configuration['body']['value']);
    return [
      '#markup' => $description
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => $this->configuration['body']['value'],
      '#format' => 'full_html',
    ];
    $form['token_label']['#markup'] = $this->t('This field supports tokens.');
    $form['token_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('body', $form_state->getValue('body'));
  }
  
  public function getCacheTags() {    
    if ($node = \Drupal::routeMatch()->getParameter('node')) {      
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } 
    else {      
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {    
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path', 'url.query_args', 'languages', 'route']);
  }

}