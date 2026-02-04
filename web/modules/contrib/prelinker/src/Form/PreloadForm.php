<?php

namespace Drupal\prelinker\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The form to add/edit preload files.
 */
class PreloadForm extends EntityForm
{
    /**
     * Cache object from dependency injection.
     *
     * @var \Drupal\Core\Cache\CacheBackendInterface
     */
    protected $cache;

    /**
     * Entity type manager object from dependency injection.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * PreloadForm constructor.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     *   The entityTypeManager.
     * @param \Drupal\Core\Cache\CacheBackendInterface $cache
     *   The cache backend.
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager, CacheBackendInterface $cache)
    {
        $this->entityTypeManager = $entityTypeManager;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager'),
            $container->get('cache.data')
        );
    }

    /**
     * Allowed list of file types
     */
    private function _as_types()
    {
        $list = [
            'audio', 'document', 'embed', 'fetch', 'font', 'image', 'object', 'script', 'style', 'track', 'worker', 'video'
        ];

        return array_combine($list, $list);
    }

    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $preload = $this->entity;

        if ($this->operation == 'edit') {
            $form['#title'] = $this->t('Edit Preload File: @name', ['@name' => $preload->label()]);
        } else {
            $form['#title'] = $this->t('Add Preload File');
        }

        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#maxlength' => 255,
            '#default_value' => $preload->get('label'),
            '#placeholder' => $this->t("Description"),
            '#required' => true,
        ];

        $form['file'] = [
            '#type' => 'textfield',
            '#title' => $this->t('File'),
            '#description' => $this->t('Specify the full URL of the file you want to preload'),
            '#maxlength' => 255,
            '#default_value' => $preload->get('file'),
            '#placeholder' => $this->t("Full URL to the file required"),
            '#required' => true,
        ];

        $form['as'] = [
            '#type' => 'select',
            '#options' => $this->_as_types(),
            '#title' => $this->t('As'),
            '#maxlength' => 255,
            '#default_value' => $preload->get('as'),
            '#placeholder' => $this->t("File type the url above is"),
            '#required' => true,
        ];
        $form['pages'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Page Restrictions'),
            '#description' => $this->t('Restrict the pages this domain gets preloaded to for'),
            '#maxlength' => 255,
            '#default_value' => $preload->get('pages'),
        ];

        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $preload->id(),
            '#machine_name' => [
                'exists' => [$this, 'exist'],
                'source' => ['label'],
            ],
            '#disabled' => !$preload->isNew(),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (!in_array($form_state->getValue('as'), $this->_as_types())) {
            $form_state->setErrorByName('as', $this->t('This file type is not allowed.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        try {
            $preload = $this->entity;
            $status = $preload->save();

            if ($status) {
                $this->messenger()->addMessage(
                    $this->t(
                        'Saved the %label Preload File.',
                        [
                            '%label' => $preload->label(),
                        ]
                    )
                );
            } else {
                $this->messenger()->addMessage(
                    $this->t(
                        'The %label Preload File was not saved.',
                        [
                            '%label' => $preload->label(),
                        ]
                    )
                );
            }
            $this->cache->delete('prelinker_config');
            $form_state->setRedirect('entity.preload.collection');
            return SAVED_UPDATED;
        } catch (EntityStorageException $ex) {
            $this->messenger()->addMessage(
                $this->t(
                    'The %label Preload File Already Exists.',
                    [
                        '%label' => $preload->label(),
                    ]
                )
            );

            $form_state->setRedirect('entity.preload.collection');
            return -1;
        }
    }

    /**
     * Helper function to check whether an entity exists.
     *
     * @param string $id
     *   The Preload entity machine name.
     *
     * @return bool
     *   Returns true if machine name already exist.
     *
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function exist($id)
    {
        $entity = $this->entityTypeManager->getStorage('preload')->getQuery()
            ->condition('id', $id)
            ->accessCheck(FALSE)
            ->execute();
        return (bool) $entity;
    }
}
