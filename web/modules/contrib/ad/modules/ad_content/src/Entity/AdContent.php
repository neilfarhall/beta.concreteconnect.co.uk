<?php

namespace Drupal\ad_content\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the AD content entity class.
 *
 * @ContentEntityType(
 *   id = "ad_content",
 *   label = @Translation("AD Content"),
 *   label_collection = @Translation("Advertisement"),
 *   label_singular = @Translation("content AD"),
 *   label_plural = @Translation("content ADs"),
 *   label_count = @PluralTranslation(
 *     singular = "@count content AD",
 *     plural = "@count content ADs",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "list_builder" = "Drupal\ad_content\Entity\AdContentListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "default" = "Drupal\Core\Entity\ContentEntityForm"
 *     },
 *    "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "ad_content",
 *   revision_table = "ad_content_revision",
 *   admin_permission="administer ads",
 *   field_ui_base_route = "entity.ad_content.collection",
 *   links = {
 *     "add-form" = "/ad/content",
 *     "create" = "/ad/content",
 *     "canonical" = "/ad/content/{ad_content}",
 *     "collection" = "/admin/ad",
 *     "delete-form" = "/ad/content/{ad_content}/delete",
 *     "edit-form" = "/ad/content/{ad_content}/edit",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "published" = "status",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log"
 *   },
 *   render_cache = TRUE,
 * )
 */
class AdContent extends EditorialContentEntityBase implements AdContentInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getAdIdentifier(): string {
    return $this->uuid();
  }

  /**
   * {@inheritdoc}
   */
  public function getBucketId(): string {
    return static::BUCKET_ID;
  }

  /**
   * Returns the AD size.
   *
   * @return string
   *   The AD size ID.
   */
  public function getSizeId(): string {
    return $this->get('size')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetUrl(): ?Url {
    $value = current($this->get('target_url')->getValue());
    $value['options']['attributes']['rel'] = 'nofollow';
    return $value ? Url::fromUri($value['uri'], $value['options']) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    // @todo Implement AD bundles.
    // $fields['type']->setDescription(t('The AD Content type.'));

    $fields['status']
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setDescription(new TranslatableMarkup('A brief description of the AD.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->addConstraint('UniqueField', [])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid']
      ->setLabel(new TranslatableMarkup('Authored by'))
      ->setDescription(t('The user ID of the author.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['size'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Size'))
      ->setDescription(new TranslatableMarkup('The AD size.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'allowed_values_function' => 'ad_get_sizes',
      ])
      ->setDisplayOptions('view', [
        'type' => 'list_default',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['target_url'] = BaseFieldDefinition::create('link')
      ->setLabel(new TranslatableMarkup('Target URL'))
      ->setDescription(new TranslatableMarkup('The URL to be taken to when clicking on the AD.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'link',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Authored on'))
      ->setDescription(t('The time that the AD was created.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the AD was last edited.'));

    return $fields;
  }

}
