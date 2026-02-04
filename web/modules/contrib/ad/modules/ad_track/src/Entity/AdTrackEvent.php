<?php

namespace Drupal\ad_track\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the AD track event entity class.
 *
 * @ContentEntityType(
 *   id = "ad_track_event",
 *   label = @Translation("AD Track Event"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "storage_schema" = "Drupal\ad_track\Entity\AdTrackEventStorageSchema",
 *     "views_data" = "Drupal\ad_track\Entity\AdTrackEventViewsData",
 *   },
 *   base_table = "ad_track_event",
 *   admin_permission="administer ads",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class AdTrackEvent extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['id']->setSetting('size', 'big');

    $fields['type']->setLabel(new TranslatableMarkup('Type'));

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('User'))
      ->setDescription(t('The user who triggered the event.'))
      ->setSetting('target_type', 'user');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Creation date'))
      ->setDescription(t('The event timestamp.'));

    $fields['ad_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('AD'))
      ->setDescription(t('The identifier of the AD the events refers to.'));

    $fields['ip_address'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('IP address'))
      ->setDescription(t('The IP address of the user who triggered the event.'));

    $fields['user_agent'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('User agent'))
      ->setDescription(t('The user agent of the user who triggered the event.'));

    $fields['url'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('URL'))
      ->setDescription(t('The URL of the page where the event was triggered.'));

    $fields['page_title'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Page title'))
      ->setDescription(t('The URL of the page where the event was triggered.'));

    $fields['referrer'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Referrer'))
      ->setDescription(t('The HTTP referrer of the page where the event was triggered.'));

    $fields['page_view_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Page view ID'))
      ->setDescription(t('The unique ID of the page view where the event was triggered.'));

    $fields['session'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Session'))
      ->setDescription(t('The ID of the user session.'));

    $fields['parent_id'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Parent ID'))
      ->setDescription(t('The UUID of the parent event.'));

    return $fields;
  }

}
