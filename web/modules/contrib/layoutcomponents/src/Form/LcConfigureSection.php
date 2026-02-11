<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\Form\ConfigureSectionForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionComponent;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\Section;
use Drupal\layoutcomponents\LcSectionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Provides a form for configuring a layout section.
 *
 * @internal
 *   Form classes are internal.
 */
class LcConfigureSection extends ConfigureSectionForm {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The LC manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $lcLayoutManager;

  /**
   * Drupal\layoutcomponents\LcSectionManager definition.
   *
   * @var \Drupal\layoutcomponents\LcSectionManager
   */
  protected $lcSectionManager;

  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * If the section must be update.
   *
   * @var bool
   */
  protected bool $updateLayout = FALSE;

  /**
   * Autosave.
   *
   * @var bool
   */
  protected bool $autosave = FALSE;

  /**
   * If the sections are tabs.
   *
   * @var bool
   */
  protected bool $isTabs = FALSE;

  protected $plugins = [
    'layoutcomponents_one_column' => 1,
    'layoutcomponents_two_column' => 2,
    'layoutcomponents_three_column' => 3,
    'layoutcomponents_four_column' => 4,
    'layoutcomponents_five_column' => 5,
    'layoutcomponents_six_column' => 6,
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PluginFormFactoryInterface $plugin_form_manager, RequestStack $request, LcLayoutsManager $layout_manager, LcSectionManager $lc_section_manager) {
    parent::__construct($layout_tempstore_repository, $plugin_form_manager);
    $this->request = $request;
    $this->lcLayoutManager = $layout_manager;
    $this->lcSectionManager = $lc_section_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin_form.factory'),
      $container->get('request_stack'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('layoutcomponents.section')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $plugin_id = NULL) {
    $this->isDefault = 0;
    // Check section type.
    try {
      $section = $section_storage->getSection($delta)->getLayoutSettings();
      if (array_key_exists('section', $section)) {
        $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
        $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;
      }
    }
    catch (\Exception $e) {
      $this->isDefault = 0;
    }

    // Get custom params.
    $this->updateLayout = $this->request->getCurrentRequest()->query->get('update_layout') ?? FALSE;
    $this->autosave = $this->request->getCurrentRequest()->query->get('autosave') ?? FALSE;
    $sub_section = $this->request->getCurrentRequest()->query->get('sub_section');
    $tabs = $this->request->getCurrentRequest()->query->get('is_tabs');
    $this->isTabs = isset($tabs) ? $tabs : 0;

    $form_state->setValue('sub_section', $sub_section);

    // Do we need update the layout?
    if (boolval($this->updateLayout)) {
      $this->updateLayoutSettings($section_storage, $delta, $plugin_id);

      // Remove plugin id to parent form detect new section as old section.
      $plugin_id = NULL;
    }

    $build = parent::buildForm($form, $form_state, $section_storage, $delta, $plugin_id);

    if ($this->isDefault && !boolval($this->autosave)) {
      // This section cannot be configured.
      $message = 'This section cannot be configured because is configurated as default';
      $build = $this->lcLayoutManager->getDefaultCancel($message);
    }
    else {
      // Add new step if is new section or is a update.
      if (boolval($this->autosave)) {
        $build['new_section'] = [
          '#type' => 'help',
          '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t("Are you sure to add a new section?") . '</div>',
          '#weight' => -1,
        ];

        if (boolval($this->updateLayout)) {
          $build['new_section']['#markup'] = '<div class="layout_builder__add-section-confirm">' . $this->t("Are you sure to change layout?") . '</div>';
        }

        $build['layout_settings']['container']['#prefix'] = '<div class="lc-lateral-container hidden">';
        $build['layout_settings']['container']['#suffix'] = '</div>';
      }
    }

    // Hidde other configurations.
    $build['layout_settings']['container']['regions']['#access'] = FALSE;
    $build['layout_settings']['container']['section']['#open'] = TRUE;

    if (isset($sub_section) && !empty($sub_section)) {
      //$build['layout_settings']['container']['section']['sub_section'] = $sub_section;

      if (isset($sub_section['parent_section'])) {
        $build['sub_section_parent_section'] = [
          '#type' => 'hidden',
          '#default_value' => $sub_section['parent_section'],
        ];
      }

      if (isset($sub_section['parent_region'])) {
        $build['sub_section_parent_region'] = [
          '#type' => 'hidden',
          '#default_value' => $sub_section['parent_region'],
        ];
      }
    }

    return $build;
  }

  public function updateLayoutSettings($section_storage, $delta, $plugin_id) {
    /** @var \Drupal\layout_builder\Section $section */
    $section = $section_storage->getSection($delta);

    // Old section settings.
    $layoutSettings = $section->getLayoutSettings();

    // Get the old regions.
    $regions = $layoutSettings['regions'];

    // Generate the new section.
    $newSection = new Section($plugin_id);

    // The new layout settings.
    $newLayoutSettings = $newSection->getLayoutSettings();
    $n_regions = $newLayoutSettings['regions'];
    $newLayoutSettings = $layoutSettings;
    $newLayoutSettings['regions'] = $n_regions;

    // Preprocess to determine the region of each component.
    foreach ($regions as $plugin => $region) {
      $region_components = $section->getComponentsByRegion($plugin);

      /** @var \Drupal\layout_builder\SectionComponent  $component */
      foreach ($region_components as $key => $component) {
        $component = $component->toArray();

        // If the componente region exists in the new section or if.
        // The regions doesnt exists in the new section.
        if (array_key_exists($plugin, $newLayoutSettings['regions'])) {
          $n_plugin = $component['region'];
        }
        else {
          $n_plugin = 'first';
        }

        // Anyway, include the component in the new section.
        $newSection->appendComponent(new SectionComponent($component['uuid'], $n_plugin, $component['configuration'], $component['additional']));
      }
    }

    // Restore previous each region configuration.
    foreach ($newLayoutSettings['regions'] as $key => $region) {
      if (array_key_exists($key, $regions)) {
        $newLayoutSettings['regions'][$key] = $regions[$key];
      }
    }

    // Check if the new section is tabs.
    $newLayoutSettings = $this->setSectionAsTabs($newLayoutSettings);

    // Set the new layout settings.
    $newSection->setLayoutSettings($newLayoutSettings);



    // Remove old section to not get conflicts.
    $section_storage->removeSection($delta);

    // Register the new section.
    $section_storage->insertSection($delta, $newSection);

    // If the new section doesnt contains the same number of colums.
    // We have to check if the deleted column contained any section.
    // If yes, we have to move the section to the first column.
    $lcId = $newLayoutSettings['lc_id'];
    foreach ($section_storage->getSections() as $dd => $section) {
      $settings = $section->getLayoutSettings();

      // Looking for the sections of deleted columns.
      if (array_key_exists('sub_section', $settings) && $settings['sub_section']['lc_id'] == $lcId) {
        if (!array_key_exists($settings['sub_section']['parent_region'], $newLayoutSettings['regions'])) {
          // Set as fisrt column the parent of sections.
          $settings['sub_section']['parent_region'] = 'first';
          $section->setLayoutSettings($settings);
          $section_storage->removeSection($dd);
          $section_storage->insertSection($dd, $section);
        }
      }
    }

    return $section_storage;
  }

  public function setSectionAsTabs(array $layoutSettings) {
    // Check if the new section is tabs.
    if (boolval($this->isTabs)) {
      $layoutSettings['section']['general']['structure']['section_tabs'] = 1;
    }
    else {
      $layoutSettings['section']['general']['structure']['section_tabs'] = 0;
    }

    return $layoutSettings;
  }

  /**
   * Custom submit form to include sub section configuration.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The Form state object.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call the plugin submit handler.
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);

    $plugin_id = $this->layout->getPluginId();
    $configuration = $this->layout->getConfiguration();

    // Tabs proccess.
    if (boolval($this->isTabs)) {
      $configuration = $this->setSectionAsTabs($configuration);
    }

    if ($this->isUpdate) {
      $this->sectionStorage->getSection($this->delta)->setLayoutSettings($configuration);
    }
    else {
      // Include the new sub section.
      $parent_section = $form_state->getValue('sub_section_parent_section');
      $parent_region = $form_state->getValue('sub_section_parent_region');

      if (is_numeric($parent_section) && !empty($parent_region)) {
        $dd_settings = $this->lcSectionManager->getLayoutSettings($this->sectionStorage, $this->delta);
        $new_uuid = \Drupal::service('uuid')->generate();
        if (!array_key_exists('lc_id', $dd_settings)) {
          $dd_settings['lc_id'] = $new_uuid;

          // If current parent section hasn't id, add new.
          $this->sectionStorage->getSection($this->delta)->setLayoutSettings($dd_settings);
        }
        else {
          $new_uuid = $dd_settings['lc_id'];
        }

        // Provide the sub section configuration.
        $configuration['sub_section'] = [
          'lc_id' => $new_uuid,
          'parent_section' => $parent_section,
          'parent_region' => $parent_region,
        ];
      }

      // Register the sub section.
      $this->sectionStorage->insertSection($this->delta, new Section($plugin_id, $configuration));
    }

    $this->layoutTempstoreRepository->set($this->sectionStorage);
    $form_state->setRedirectUrl($this->sectionStorage->getLayoutBuilderUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutSettings() {
    return $this->layout;
  }

}
