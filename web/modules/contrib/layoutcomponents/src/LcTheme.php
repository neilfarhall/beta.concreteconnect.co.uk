<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\node\Entity\Node;
use Drupal\commerce_store\Entity\Store;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Layout\LayoutPluginManager;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * General class for Theme hooks.
 */
class LcTheme implements ContainerInjectionInterface {

  /**
   * The Layout Plugin Manager object.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * The Request object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager, RouteMatchInterface $route_match) {
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('current_route_match')
    );
  }

  /**
   * Implements hook_theme() for LC pages.
   *
   * @see \hook_theme()
   */
  public function theme() {
    return [
      'layoutcomponents_block_content' => [
        'render element' => 'elements',
      ],
      'layoutcomponents__slick_region' => [
        'variables' => [
          'content' => NULL,
        ],
        'render element' => 'elements',
      ],
      'layoutcomponents__region' => [
        'variables' => [
          'region' => NULL,
          'key' => NULL,
        ],
      ],
      'layoutcomponents__subregion' => [
        'variables' => [
          'subregion' => NULL,
          'content' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for LC sections.
   *
   * @see \hook_theme_suggestions_HOOK()
   */
  public function themeSuggestionsLayoutLayoutcomponents(array &$suggestions, array $variables, $hook) {
    if ($hook == 'layout__layoutcomponents_base' ) {
      $classes = $variables['content']['#settings']['section']['styles']['misc']['extra_class'];
      $class = explode(',', $classes);
      if (is_array($class)) {
        $class = $class[0];
      }

      /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
      $layout = $variables['content']['#layout'];

      $suggestions[] = 'layout__layoutcomponents_base__' . $layout->id();

      $entity = $this->getNodeFromSectionContent($variables);
      $entity_data = $this->getEntityData($entity);

      if (isset($entity_data) && !empty($entity_data)) {
        $suggestions[] = 'layout__layoutcomponents_base__' . ((!empty($class)) ? ($class . '_') : '') . $layout->id() . '_' . $entity_data['type'];
        $suggestions[] = 'layout__layoutcomponents_base__' . ((!empty($class)) ? ($class . '_') : '') . $layout->id() . '_' . $entity_data['id'] . '_' . $entity_data['type'];
      }
    }

    if ($hook == 'layoutcomponents__region') {
      $classes = $variables['region']['styles']['misc']['extra_class'];
      $class = explode(',', $classes);
      if (is_array($class)) {
        $class = $class[0];
      }
      $suggestions[] = 'layoutcomponents__region__' . (!empty($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'];
      $entity = $this->getNodeFromRegionContent($variables);
      $entity_data = $this->getEntityData($entity);

      if (isset($entity_data) && !empty($entity_data)) {
        $suggestions[] = 'layoutcomponents__region__' . (!empty($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'] . '_' . (str_replace('-', '_', $entity_data['type']));
        $suggestions[] = 'layoutcomponents__region__' . (!empty($class) ? (str_replace('-', '_', $class) . '_') : '') . $variables['key'] . '_' . $entity_data['id'] . '_' . (str_replace('-', '_', $entity_data['type']));
      }
    }

    if ($hook == 'layoutcomponents__subregion') {
      /** @var \Drupal\Core\Template\Attribute $attributes */
      $attributes = $variables['subregion']['attributes'];
      $attributes_classes  =$attributes->getClass()->value();

      $entity = $this->getNodeFromRegionContent($variables, TRUE);
      $entity_data = $this->getEntityData($entity);

      $class = '';
      if (count($attributes_classes) > 2) {
        $class = $attributes_classes[0];
      }

      if (isset($entity_data) && !empty($entity_data)) {
        $suggestions[] = 'layoutcomponents__subregion__' . $entity_data['type'];
        $suggestions[] = 'layoutcomponents__subregion__' . $entity_data['id'];
      }

      if (!empty($class)) {
        $suggestions[] = 'layoutcomponents__subregion__' . $class;
      }
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK() for LC blocks.
   *
   * @see \hook_theme_suggestions_HOOK()
   */
  public function themeSuggestionsLayoutcomponentsBlockContent(array $variables) {
    $suggestions = [];
    $block_content = $variables['elements']['#block_content'];
    $suggestions[] = 'layoutcomponents_block_content__' . $block_content->bundle();
    $suggestions[] = 'layoutcomponents_block_content__' . $block_content->id();

    return $suggestions;
  }

  public function getEntityData($entity) {
    $entity_data = [];
    if ($entity instanceof Node) {
      $entity_data = [
        'id' => $entity->id(),
        'type' => $entity->getType(),
      ];
    }
    elseif ($entity instanceof Store) {
      $entity_data = [
        'id' => $entity->id(),
        'type' => $entity->bundle(),
      ];
    }

    return $entity_data;
  }

  /**
   * Method to determine the current node type of section.
   *
   * @param array $variables
   *   The complete array.
   *
   * @return Node|Store|NULL
   *   The type of node.
   */
  public function getNodeFromSectionContent(array $variables) {
    /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
    $layout = $variables['content']['#layout'];

    $res = NULL;
    foreach ($layout->getRegionNames() as $delta => $region_name) {
      if (array_key_exists($region_name, $variables['content'])) {
        foreach ($variables['content'][$region_name] as $block) {
          if (!array_key_exists('#base_plugin_id', $block) || $block['#base_plugin_id'] !== 'field_block') {
            continue;
          }
          if (array_key_exists('#object', $block['content']) && $block['content']['#object'] instanceof Node) {
            $res = $block['content']['#object'];
          }
        }
      }
    }

    if (!isset($res)) {
      $res = $this->routeMatch->getParameter('node');
      if (!isset($res)) {
        /** @var \Drupal\commerce_store\Entity\Store $res */
        $res = $this->routeMatch->getParameter('commerce_store');
      }
    }

    return $res;
  }

  /**
   * Method to determine the current node type of region.
   *
   * @param array $variables
   *   The complete array.
   *
   * @return Node|Store|NULL
   *   The type of node.
   */
  public function getNodeFromRegionContent(array $variables, $isSubregion = FALSE) {
    $res = NULL;
    if ($isSubregion) {
      $content = $variables['content'];
    }
    else {
      $content = $variables['region']['content'];
    }
    foreach ($content as $block) {
      if (is_array($block)) {
        if (array_key_exists('content', $block)) {
          if (!array_key_exists('#base_plugin_id', $block) || $block['#base_plugin_id'] !== 'field_block') {
            continue;
          }
          if (array_key_exists('#object', $block['content']) && $block['content']['#object'] instanceof Node) {
            $res = $block['content']['#object'];
          }
        }
      }
    }

    if (!isset($res)) {
      $res = $this->routeMatch->getParameter('node');
      if (!isset($res)) {
        /** @var \Drupal\commerce_store\Entity\Store $res */
        $res = $this->routeMatch->getParameter('commerce_store');
      }
    }

    return $res;
  }

  /**
   * Preprocess function for block content template.
   */
  public function preprocessLayoutcomponentsBlockContent(&$variables) {
    $variables['content'] = $variables['elements'];
    // Set configurations.
    $block_content = $variables['elements']['#block_content'];
    $variables['plugin_id'] = 'inline-block' . $block_content->bundle();
    $variables['configuration'] = [
      'provider' => 'layout-builder',
    ];
  }

  /**
   * Implements hook_theme_registry_alter() for LC pages.
   *
   * @see \hook_theme_registry_alter()
   */
  public function themeRegistryAlter(&$theme_registry) {
    if (!\Drupal::hasService('plugin.manager.core.layout')) {
      return;
    }

    // Find all Layoutcomponents Layouts.
    $layouts = $this->layoutPluginManager->getDefinitions();
    $layout_theme_hooks = [];

    foreach ($layouts as $info) {
      if ($info->getClass() === 'Drupal\layoutcomponents\Plugin\Layout\LcBase') {
        $layout_theme_hooks[] = $info->getThemeHook();
      }
    }

    foreach ($theme_registry as $theme_hook => $info) {
      if ((in_array($theme_hook, $layout_theme_hooks) || (!empty($info['base hook']) && in_array($info['base hook'], $layout_theme_hooks))) ||
        str_contains($theme_registry[$theme_hook]['template'], 'layout--layoutcomponents-base--')
      ) {
        // Include file.
        $theme_registry[$theme_hook]['includes'][] = drupal_get_path('module', 'layoutcomponents') . '/layoutcomponents.theme.inc';
        // Set new preprocess function.
        $theme_registry[$theme_hook]['preprocess functions'][] = '_layoutcomponents_preprocess_layout';
        $theme_registry[$theme_hook]['base hook'] = 'layout__layoutcomponents_base';
      }
    }

    // Remove each default template_preprocess_layout.
    // Regions does not contain 'content' array.
    // If not, layout discovery return an error.
    foreach ($theme_registry['layoutcomponents__region']['preprocess functions'] as $key => $value){
      if ($value == 'template_preprocess_layout') {
        unset($theme_registry['layoutcomponents__region']['preprocess functions'][$key]);
      }
    }

    $theme_registry['layoutcomponents__region']['base hook'] = 'layoutcomponents__region';
    $theme_registry['layoutcomponents__subregion']['base hook'] = 'layoutcomponents__subregion';
  }

  /**
   * Implements hook_help() for LC pages.
   *
   * @see \hook_help()
   */
  public function help($route_name, RouteMatchInterface $route_match) {
    if ($route_match->getRouteObject()->getOption('_layout_builder')) {
      return '';
    }

    switch ($route_name) {
      // Main module help for the layoutcomponents module.
      case 'help.page.layoutcomponents':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('Block type creation') . '</p>';
        return $output;

      default:
    }
  }

}
