<?php

namespace Drupal\prelinker\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * The form to delete preconnect entry.
 */
class PreloadDeleteForm extends EntityConfirmFormBase
{
  /**
   * {@inheritdoc}
   */
    public function getQuestion()
    {
        return $this->t('Are you sure you want to delete preload file %name?', ['%name' => $this->entity->label()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl()
    {
        return new Url('entity.preload.collection');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText()
    {
        return $this->t('Delete Preload File');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Drupal\Core\Entity\EntityStorageException
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->entity->delete();
        \Drupal::messenger()->addMessage($this->t('Preload file %label has been deleted.', ['%label' => $this->entity->label()]));
        \Drupal::cache()->delete('prelinker_config');
        $form_state->setRedirectUrl($this->getCancelUrl());
    }
}
