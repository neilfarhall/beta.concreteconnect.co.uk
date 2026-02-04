<?php

namespace Drupal\prelinker\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Preload entity.
 *
 * @ConfigEntityType(
 *   id = "preload",
 *   label = @Translation("Preload"),
 *   handlers = {
 *     "list_builder" = "Drupal\prelinker\Controller\PreloadListBuilder",
 *     "form" = {
 *       "add" = "Drupal\prelinker\Form\PreloadForm",
 *       "edit" = "Drupal\prelinker\Form\PreloadForm",
 *       "delete" = "Drupal\prelinker\Form\PreloadDeleteForm",
 *     }
 *   },
 *   config_prefix = "preload",
 *   admin_permission = "administer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/prelinker/preload/{preload}",
 *     "delete-form" = "/admin/config/system/prelinker/preload/{preload}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "file",
 *     "as",
 *     "pages",
 *     "weight",
 *   }
 * )
 */
class Preload extends ConfigEntityBase implements ConfigEntityInterface
{
    /**
     * The preload ID.
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
     * The file.
     *
     * @var string
     */
    public $file;

    /**
     * File type as
     *
     * @var string
     */
    public $as;

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
