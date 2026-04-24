<?php

namespace Drupal\paragraph_blocks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for Paragraph Blocks settings.
 */
class ParagraphBlocksSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraph_blocks_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['paragraph_blocks.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('paragraph_blocks.settings');
    $form['max_cardinality'] = [
      '#type' => 'number',
      '#title' => $this->t('Max cardinality for unlimited paragraph fields.'),
      '#default_value' => $config->get('max_cardinality'),
      '#description' => $this->t('Limits the number of paragraph items available for placement in Layout Builder for fields with unlimited cardinality. Fields with a defined cardinality use their configured limit. Leave empty or set to 0 for no limit (defaults to 10).'),
    ];
    $form['individual_block_ui'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display individual paragraph blocks in Layout Builder Restrictions.'),
      '#default_value' => $config->get('individual_block_ui'),
      '#description' => $this->t('When configuring Layout Builder Restrictions on an entity display, show individual checkboxes for each paragraph item instead of one checkbox per field. Only enable if you need granular control over which paragraph items are allowed.'),
    ];
    $form['suppress_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Suppress label field on layout manager block placement.'),
      '#default_value' => $config->get('suppress_label'),
      '#description' => $this->t("Hides the block label field when placing paragraph blocks in Layout Builder. The paragraph's admin title is already used as the block label, making the additional label field redundant."),
    ];
    $form['library_items_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only show paragraph items from the paragraphs library.'),
      '#default_value' => $config->get('library_items_only'),
      '#description' => $this->t('When enabled, only paragraphs that reference items from the paragraphs library will be available for placement in the block layout UI. This significantly reduces UI clutter on sites with many paragraph entities.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('paragraph_blocks.settings');
    $config->set('max_cardinality', $form_state->getValue('max_cardinality'));
    $config->set('individual_block_ui', $form_state->getValue('individual_block_ui'));
    $config->set('suppress_label', $form_state->getValue('suppress_label'));
    $config->set('library_items_only', $form_state->getValue('library_items_only'));
    $config->save();
  }

}
