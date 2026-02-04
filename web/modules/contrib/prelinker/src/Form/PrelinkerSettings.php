<?php

namespace Drupal\prelinker\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Prelinker settings
 */
class PrelinkerSettings extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'prelinker_config_form';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ['prelinker.settings'];
    }

    /**
     * Translate and convert html characters
     */
    private function _t($text)
    {
        return htmlentities($this->t($text));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('prelinker.settings');

        $form['head'] = [
            '#type' => 'fieldset',
            '#title' => $this->_t('<head> settings'),
            'preconnect_head' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Include preconnect domain in the <head> section as <link> elements'),
                '#description' => $this->_t('When enabled, each preconnect domains will be added as a <link rel="preconnect" href="..."> element to the <head> section of page.'),
                '#default_value' => !empty($config->get('preconnect_head'))
            ],
            'preload_head' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Include preload files in the <head> section as <link> elements'),
                '#description' => $this->_t('When enabled, each preload file will be added as a <link rel="preload" href="..."> element to the <head> section of page.'),
                '#default_value' => !empty($config->get('preload_head')),
                '#suffix' => '<hr />'
            ],
            'preload_head_image' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Scan the document for data-preload-image="..." and add to the <head> section as <link> elements'),
                '#description' => $this->_t('When enabled, the HTML is scanned for elements with data-preload-image="" attributes and these will be added as a <link rel="preload" href="..." as="image"> element to the <head> section of the page.'),
                '#default_value' => !empty($config->get('preload_head_image'))
            ],
        ];

        $form['http2'] = [
            '#type' => 'fieldset',
            '#title' => $this->_t('HTTP/2 push settings'),
            'preconnect_push' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Include preconnect domains in the response headers'),
                '#description' => $this->_t('When enabled, each preconnect domain will be added to the response headers.'),
                '#default_value' => !empty($config->get('preconnect_push'))
            ],
            'preload_push' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Include preload files in the response headers'),
                '#description' => $this->_t('When enabled, each preload file will be added to the response headers.'),
                '#default_value' => !empty($config->get('preload_push')),
                '#suffix' => '<hr />',
            ],
            'preload_push_image' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Scan the document for data-preload-image="..." and add to the response headers'),
                '#description' => $this->_t('When enabled, the HTML is scanned for elements with data-preload-image="" attributes and these will be added to the response headers.'),
                '#default_value' => !empty($config->get('preload_push_image'))
            ],
            'preload_push_css' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Scan the document for all included CSS files and add to the response headers'),
                '#description' => $this->_t('When enabled, the HTML is scanned for <link rel="stylesheet" href="..."> and any found are added to the response headers.'),
                '#default_value' => !empty($config->get('preload_push_css'))
            ],
            'preload_push_preload' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Scan the document for existing preload links and add to the response headers'),
                '#description' => $this->_t('When enabled, the HTML is scanned for <link rel="preload" href="..."> and any found are added to the response headers.'),
                '#default_value' => !empty($config->get('preload_push_preload'))
            ],
            'preload_push_preconnect' => [
                '#type' => 'checkbox',
                '#required' => false,
                '#title' => $this->_t('Scan the document for existing preconnect links and add to the response headers'),
                '#description' => $this->_t('When enabled, the HTML is scanned for <link rel="preconnect" href="..."> and any found are added to the response headers'),
                '#default_value' => !empty($config->get('preload_push_preconnect'))
            ],
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $this->config('prelinker.settings')->set(
            'preconnect_head',
            $form_state->getValue('preconnect_head')
        )->set(
            'preconnect_push',
            $form_state->getValue('preconnect_push')
        )->set(
            'preload_head',
            $form_state->getValue('preload_head')
        )->set(
            'preload_push',
            $form_state->getValue('preload_push')
        )->set(
            'preload_head_image',
            $form_state->getValue('preload_head_image')
        )->set(
            'preload_push_image',
            $form_state->getValue('preload_push_image')
        )->set(
            'preload_push_css',
            $form_state->getValue('preload_push_css')
        )->set(
            'preload_push_preload',
            $form_state->getValue('preload_push_preload')
        )->set(
            'preload_push_preconnect',
            $form_state->getValue('preload_push_preconnect')
        )->save();
    }
}
