<?php

/**
 * @file
 * Contains \Drupal\body_inject\Form\Profile\FormBase.
 */

namespace Drupal\body_inject\Form\Profile;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for profile add and edit forms.
 */
abstract class FormBase extends EntityForm {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\body_inject\ProfileInterface
   */
  protected $entity;

  /**
   * FormBase constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
    );
  }

  /**
   * {@inheritdoc}
   */

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Profile Name'),
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The human-readable name of this  profile. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => ['\Drupal\body_inject\Entity\Profile', 'load'],
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('The text will be displayed on the <em>profile collection</em> page.'),
    ];

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    ];

    // Block we want to inject.
    $block_ref = $this->entity->getBlock();
    if (!empty($block_ref)) {
      $entity_block = $block_ref;
    }
    else {
      $entity_block = '';
    }

    // Only add blocks which work without any context.
    $definitions = $this->blockManager->getFilteredDefinitions('block_ui', [], []);
    // Order by category, and then by admin label.
    $definitions = $this->blockManager->getSortedDefinitions($definitions);
    // Filter out definitions that are not intended to be placed by the UI.
    $definitions = array_filter($definitions, function (array $definition) {
      return empty($definition['_block_ui_hidden']);
    });
    $options = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = $this->blockManager->createInstance($plugin_id, [])->label() . " - " . $plugin_id;
    }
    $form['block_reference'] = [
      '#type' => 'select',
      '#title' => $this->t('Block to inject'),
      '#required' => 'true',
      '#options' => $options,
      '#default_value' => $entity_block,
    ];

    $node_types = node_type_get_names();
    $form['node_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Node Type to use on'),
      '#required' => 'true',
      '#default_value' => $this->entity->getNodeType(),
      '#options' => $node_types,
    ];

    // Paragraph condition.
    $form['inject_condition'] = [
      '#title' => t('The condition if the block is placed'),
      '#type' => 'fieldset',
      '#description' => t('Leave blank to always try to insert the block.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['inject_condition']['paragraphs'] = [
      '#title' => t('Paragraphs'),
      '#type' => 'fieldset',
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    ];

    $form['inject_condition']['paragraphs']['cond_text'] = [
      '#type' => 'markup',
      '#markup' => t('The Body Text needs to have ')
    ];
    $form['inject_condition']['paragraphs']['paragraph_operator'] = [
      '#type' => 'select',
      '#options' => array(
        '<' => 'less than',
        '=' => 'exactly',
        '>' => 'more than'
      ),
      '#multiple' => FALSE,
      '#default_value' => $this->entity->getParagraphOperator(),
    ];
    $form['inject_condition']['paragraphs']['paragraph_number'] = [
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#size' => 5,
      '#default_value' => $this->entity->getParagraphNumber(),
    ];
    $form['inject_condition']['paragraphs']['cond_text_2'] = [
      '#type' => 'markup',
      '#markup' => t('paragraphs in order to fire one of the the actions below.')
    ];

    //and or
    $form['inject_condition']['and_cond'] = array(
      '#title' => t(''),
      '#type' => 'fieldset',
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    );

    $form['inject_condition']['and_cond'] ['and_or'] = array(
      '#type' => 'select',
      '#title' => t('AND/OR?'),
      '#options' => array(
        'and' => t('AND'),
        'or' => t('OR'),
      ),
      '#default_value' => $this->entity->getAndOr(),
      '#description' => t('Set this to <em>AND</em> if you would like both to apply. Set it to OR if only one has to be valid for the insert to happen.'),
    );

    // Characters.
    $form['inject_condition']['char_cond'] = array(
      '#title' => t('Characters'),
      '#type' => 'fieldset',
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    );

    $form['inject_condition']['char_cond']['explanation_char'] = array(
      '#type' => 'markup',
      '#markup' => t('The Body Text needs to have ')
    );
    $form['inject_condition']['char_cond']['char_operator'] = array(
      '#type' => 'select',
      '#options' => array(
        '<' => 'less than',
        '=' => 'exactly',
        '>' => 'more than'
      ),
      '#multiple' => FALSE,
      '#default_value' => $this->entity->getCharOperator(),
    );
    $form['inject_condition']['char_cond']['char_number'] = array(
      '#type' => 'textfield',
      '#maxlength' => 10,
      '#size' => 5,
      '#default_value' => $this->entity->getCharNumber(),
    );
    $form['inject_condition']['char_cond']['explanation_char_1'] = array(
      '#type' => 'markup',
      '#markup' => t('charcters in order to fire one of the the actions below.')
    );

    //Placement Trigger
    //info
    $form['body_inject_information'] = array(
      '#markup' => t('<h2>Block placement Settings</h2><p><b>Only use/fill in ONE condition above, do not use multiple. If a field is left empty it ist not used/disabled.</b></p>'),
    );

    //condition 1, middle injection
    //injekt paragraph

    $form['body_inject_paragraph_offset_action'] = array(
      '#title' => t('Paragraph Offset Placement from the Middle of the Body Text'),
      '#type' => 'fieldset',
      '#description' => t('Insert the block by applying the above offset value. The offset will be added to the middle
    position. If you chose 0 the block is inserted in the middle of the body text.'),
    );
    $form['body_inject_paragraph_offset_action']['paragraph_offset'] = array(
      '#title' => t('Paragraph offset'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->getParagraphOffset(),
      '#description' => t('Please specify a positive or negative number to
      offset the injection (e.g. 1 to move down by one paragraph /
      -1 to move up by one paragraph).'),
    );

    //condition 2, injection after paragraph, length
    $form['body_inject_paragraph_action'] = array(
      '#title' => t('Insert after Paragraph ...'),
      '#type' => 'fieldset',
      '#description' => t('Insert the block after a number of paragraphs. Insert the Number above.'),
    );
    $form['body_inject_paragraph_action']['paragraph_position'] = array(
      '#title' => t('Insert Paragraph'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->getParagraphPosition(),
      '#description' => t('Please specify a positive number to the injection (e.g. 1, 2, 3, 4). If that node doesnt
    have that amount of paragraphs. Nothing will be injected.'),
    );

    //condition 3, after amount of characters
    $form['body_inject_char_action'] = array(
      '#title' => t('Insert after amount of characters ...'),
      '#type' => 'fieldset',
      '#description' => t('Insert the block after a number of charcters. Insert the Number above.'),
    );
    $form['body_inject_char_action']['char_position'] = array(
      '#title' => t('Insert Characters'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->getCharPosition(),
      '#description' => t('Please specify a positive number to the injection (e.g. 1, 2, 3, 4). If that node doesnt
      have that amount of characters. Nothing will be injected.'),
    );

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $body_inject_profile = $this->entity;

    // Prevent leading and trailing spaces in body_inject profile labels.
    $body_inject_profile->set('label', trim($body_inject_profile->label()));

    $status = $body_inject_profile->save();
    //$edit_link = $this->entity->link($this->t('Edit'));

    //redirect to overview page
    $dest_url = "/admin/config/content/body_inject";
    $url = Url::fromUri('internal:' . $dest_url);
    $form_state->setRedirectUrl($url);
  }

}
