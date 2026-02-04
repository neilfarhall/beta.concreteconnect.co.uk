<?php

namespace Drupal\taxonomy_delete\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaxonomyDeleteUIForm. The base form for taxonomy_delete module.
 *
 * @package Drupal\taxonomy_delete\Form
 */
class TaxonomyDeleteUIForm extends FormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a TaxonomyDeleteUIForm form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_delete_ui_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $vocabularies = [];
    foreach ($this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadMultiple() as $vocabulary) {
      $vocabularies[$vocabulary->id()] = $vocabulary->label();
    }

    if (empty($vocabularies)) {
      $this->messenger()->addWarning($this->t('No taxonomy vocabularies found. @link', [
        '@link' => Link::createFromRoute($this->t('+ Create a new vocabulary'), 'entity.taxonomy_vocabulary.add_form')->toString(),
      ]));
      return [];
    }

    $form['vocabularies'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Vocabularies'),
      '#options' => $vocabularies,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Terms'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $vocabularies = array_filter($form_state->getValue('vocabularies'));

    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', $vocabularies, 'IN');
    $query->accessCheck(FALSE);
    $query->sort('tid');

    if ($tids = $query->execute()) {
      $batch = new BatchBuilder();
      $batch->setTitle($this->t('Removing taxonomy terms.'));

      foreach ($tids as $tid) {
        $batch->addOperation([$this, 'deleteTerm'], [$tid]);
      }

      batch_set($batch->toArray());
      $this->messenger()->addStatus($this->t('All selected taxonomy terms have been removed.'));
      $this->logger('taxonomy_delete')->info('All selected taxonomy terms have been removed from @vocabularies.', [
        '@vocabularies' => implode(', ', $vocabularies),
      ]);
      return;
    }

    $this->messenger()->addWarning($this->t('No taxonomy terms found.'));
  }

  /**
   * Batch callback for removing taxonomy term.
   */
  public function deleteTerm($tid, &$context) {
    if ($term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid)) {
      try {
        $term->delete();
      }
      catch (EntityStorageException $e) {
        $this->logger('taxonomy_delete')->error($e->getMessage());
      }
    }
  }

}
