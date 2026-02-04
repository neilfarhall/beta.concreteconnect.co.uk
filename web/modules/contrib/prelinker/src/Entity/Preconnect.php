<?php

namespace Drupal\prelinker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Preconnect entity.
 *
 * @ConfigEntityType(
 *   id = "preconnect",
 *   label = @Translation("Preconnect"),
 *   handlers = {
 *     "list_builder" = "Drupal\prelinker\Controller\PreconnectListBuilder",
 *     "form" = {
 *       "add" = "Drupal\prelinker\Form\PreconnectForm",
 *       "edit" = "Drupal\prelinker\Form\PreconnectForm",
 *       "delete" = "Drupal\prelinker\Form\PreconnectDeleteForm",
 *     }
 *   },
 *   config_prefix = "preconnect",
 *   admin_permission = "administer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/prelinker/preconnect/{preconnect}",
 *     "delete-form" = "/admin/config/system/prelinker/preconnect/{preconnect}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "domain",
 *     "pages",
 *     "weight",
 *   }
 * )
 */
class Preconnect extends ConfigEntityBase implements ConfigEntityInterface
{
    /**
     * The preconnect ID.
     *
     * @var string
     */
    public $id;

    /**
     * Label
     *
     * @var string
     */
    public $label;

    /**
     * The domain.
     *
     * @var string
     */
    public $domain;

    /**
     * Page restrictions
     *
     * @var string
     */
    public $pages;

    /**
     * Weight
     *
     * @var integer
     */
    public $weight = 0;
}
