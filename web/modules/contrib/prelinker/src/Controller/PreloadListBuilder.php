<?php

namespace Drupal\prelinker\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of preload files
 */
class PreloadListBuilder extends DraggableListBuilder
{
    /**
     * {@inheritdoc}
     */
    protected $weightKey = 'weight';

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'preload_list';
    }

    /**
     * {@inheritdoc}
     */
    public function buildHeader()
    {
        $header['label'] = $this->t('Name');
        $header['file'] = $this->t('File');
        $header['as'] = $this->t('As');
        $header['pages'] = $this->t('Page Restrictions');

        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     *
     * ! Having to use ['#markup'] may change in a future version
     *
     * @see https://www.drupal.org/node/2514970
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['label'] = $entity->get('label');
        $row['file']['#markup'] = $entity->get('file');
        $row['as']['#markup'] = $entity->get('as');
        $row['pages']['#markup'] = str_replace(['<', '>', "\r\n"], ['&lt;', '&gt;', '<br />'], $entity->get('pages'));

        return $row + parent::buildRow($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperations(EntityInterface $entity)
    {
        $operations = parent::getDefaultOperations($entity);
        $operations['edit']['attributes'] = [
            'class' => ['use-ajax'],
            'data-accepts' => 'application/vnd.drupal-modal',
            'data-dialog-type' => ['dialog'],
            'data-dialog-options' => [Json::encode(['width' => 700])],
        ];
        $operations['delete']['attributes'] = [
            'class' => ['use-ajax'],
            'data-accepts' => 'application/vnd.drupal-modal',
            'data-dialog-type' => ['dialog'],
            'data-dialog-options' => [Json::encode(['width' => 700])],
        ];

        return $operations;
    }
}
