<?php

namespace Drupal\prelinker\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The form to add/edit preconnect domains.
 */
class PreconnectForm extends EntityForm
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
     * PreconnectForm constructor.
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
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state)
    {
        $form = parent::form($form, $form_state);

        $preconnect = $this->entity;

        if ($this->operation == 'edit') {
            $form['#title'] = $this->t('Edit Preconnect Domain: @name', ['@name' => $preconnect->label()]);
        } else {
            $form['#title'] = $this->t('Add Preconnect Domain');
        }

        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#maxlength' => 255,
            '#default_value' => $preconnect->get('label'),
            '#placeholder' => $this->t("Description"),
            '#required' => true,
        ];

        $form['domain'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Domain'),
            '#description' => $this->t('Please enter the domain without http:// or https://'),
            '#maxlength' => 255,
            '#default_value' => $preconnect->get('domain'),
            '#placeholder' => $this->t("Domain without http:// or https://"),
            '#required' => true,
        ];

        $form['pages'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Page Restrictions'),
            '#description' => $this->t('Restrict the pages this domain gets preconnected to for'),
            '#maxlength' => 255,
            '#default_value' => $preconnect->get('pages'),
        ];

        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $preconnect->id(),
            '#machine_name' => [
                'exists' => [$this, 'exist'],
                'source' => ['label'],
            ],
            '#disabled' => !$preconnect->isNew(),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        try {
            $preconnect = $this->entity;
            $status = $preconnect->save();

            if ($status) {
                $this->messenger()->addMessage(
                    $this->t(
                        'Saved the %label Preconnect Domain.',
                        [
                            '%label' => $preconnect->label(),
                        ]
                    )
                );
            } else {
                $this->messenger()->addMessage(
                    $this->t(
                        'The %label Preconnect Domain was not saved.',
                        [
                            '%label' => $preconnect->label(),
                        ]
                    )
                );
            }
            $this->cache->delete('prelinker_config');
            $form_state->setRedirect('entity.preconnect.collection');
            return SAVED_UPDATED;
        } catch (EntityStorageException $ex) {
            $this->messenger()->addMessage(
                $this->t(
                    'The %label Preconnect Domain Already Exist.',
                    [
                        '%label' => $preconnect->label(),
                    ]
                )
            );

            $form_state->setRedirect('entity.preconnect.collection');
            return -1;
        }
    }

    /**
     * Helper function to check whether an entity exists.
     *
     * @param string $id
     *   The Preconnect entity machine name.
     *
     * @return bool
     *   Returns true if machine name already exist.
     *
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function exist($id)
    {
        $entity = $this->entityTypeManager->getStorage('preconnect')->getQuery()
            ->condition('id', $id)
            ->accessCheck(FALSE)
            ->execute();
        return (bool) $entity;
    }
}
