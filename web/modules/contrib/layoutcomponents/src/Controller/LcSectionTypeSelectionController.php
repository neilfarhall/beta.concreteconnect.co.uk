<?php

namespace Drupal\layoutcomponents\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a form for the selection of sections.
 *
 * @internal
 *   Form classes are internal.
 */
class LcSectionTypeSelectionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * ChooseBlockController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(SectionStorageInterface $section_storage, $delta, $update_layout = 0, $sub_section = []) {
    $storage_type = $section_storage->getStorageType();
    $storage_id = $section_storage->getStorageId();
    $sub_section = $this->request->getCurrentRequest()->query->get('sub_section');

    return [
      'layouts' => [
        '#type' => 'link',
        '#url' => Url::fromRoute('layout_builder.choose_section',
          [
            'section_storage_type' => $storage_type,
            'section_storage' => $storage_id,
            'delta' => $delta,
            'update_layout' => $update_layout,
            'sub_section' => $sub_section,
            'is_tabs' => 0,
          ],
          [
            'attributes' => [
              'class' => [
                'use-ajax',
                'layout-builder__link',
                'layout-builder__link--add',
              ],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
            ],
          ]
        ),
        '#title' => 'Columns',
      ],
      'tabs' => [
        '#type' => 'link',
        '#url' => Url::fromRoute('layout_builder.choose_section', [
          'section_storage_type' => $storage_type,
          'section_storage' => $storage_id,
          'delta' => $delta,
          'update_layout' => $update_layout,
          'sub_section' => $sub_section,
          'is_tabs' => 1,
        ]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'layout-builder__link',
            'layout-builder__link--add',
          ],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
        ],
        '#title' => 'Tabs',

      ],
    ];
  }

}
