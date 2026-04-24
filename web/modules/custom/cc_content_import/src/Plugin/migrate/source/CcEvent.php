<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for Event content.
 *
 * @MigrateSource(
 *   id = "cc_event"
 * )
 */
class CcEvent extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node_field_data', 'n')
      ->fields('n', [
        'nid',
        'vid',
        'type',
        'langcode',
        'status',
        'uid',
        'title',
        'created',
        'changed',
        'promote',
        'sticky',
      ]);

    $query->leftJoin('node__body', 'b', 'n.nid = b.entity_id');
    $query->addField('b', 'body_value', 'body_value');
    $query->addField('b', 'body_summary', 'body_summary');
    $query->addField('b', 'body_format', 'body_format');

    $query->leftJoin('node__field_event_date', 'ed', 'n.nid = ed.entity_id');
    $query->addField('ed', 'field_event_date_value', 'field_event_date_value');
    $query->addField('ed', 'field_event_date_end_value', 'field_event_date_end_value');

    $query->leftJoin('node__field_event_organiser', 'eo', 'n.nid = eo.entity_id');
    $query->addField('eo', 'field_event_organiser_value', 'field_event_organiser_value');

    $query->leftJoin('node__field_event_reference', 'er', 'n.nid = er.entity_id');
    $query->addField('er', 'field_event_reference_value', 'field_event_reference_value');

    $query->leftJoin('node__field_event_type', 'et', 'n.nid = et.entity_id');
    $query->addField('et', 'field_event_type_target_id', 'field_event_type_target_id');

    $query->leftJoin('node__field_logo', 'l', 'n.nid = l.entity_id');
    $query->addField('l', 'field_logo_target_id', 'field_logo_target_id');
    $query->addField('l', 'field_logo_alt', 'field_logo_alt');
    $query->addField('l', 'field_logo_title', 'field_logo_title');

    $query->leftJoin('node__field_event_email', 'ee', 'n.nid = ee.entity_id');
    $query->addField('ee', 'field_event_email_value', 'field_event_email_value');

    $query->leftJoin('node__field_teaser_media', 'tm', 'n.nid = tm.entity_id');
    $query->addField('tm', 'field_teaser_media_target_id', 'field_teaser_media_target_id');

    $query->leftJoin('node__field_teaser_text', 'tt', 'n.nid = tt.entity_id');
    $query->addField('tt', 'field_teaser_text_value', 'field_teaser_text_value');

    $query->leftJoin('node__field_event_website', 'ew', 'n.nid = ew.entity_id');
    $query->addField('ew', 'field_event_website_uri', 'field_event_website_uri');
    $query->addField('ew', 'field_event_website_title', 'field_event_website_title');

    $query->leftJoin('node__field_event_venue', 'ev', 'n.nid = ev.entity_id');
    $query->addField('ev', 'field_event_venue_value', 'field_event_venue_value');

    $query->leftJoin('node__field_event_town_city', 'tc', 'n.nid = tc.entity_id');
    $query->addField('tc', 'field_event_town_city_value', 'field_event_town_city_value');

    $query->leftJoin('node__field_event_postcode', 'pc', 'n.nid = pc.entity_id');
    $query->addField('pc', 'field_event_postcode_value', 'field_event_postcode_value');

    $query->leftJoin('node__field_event_address_1', 'a1', 'n.nid = a1.entity_id');
    $query->addField('a1', 'field_event_address_1_value', 'field_event_address_1_value');

    $query->leftJoin('node__field_event_address_2', 'a2', 'n.nid = a2.entity_id');
    $query->addField('a2', 'field_event_address_2_value', 'field_event_address_2_value');

    $query->leftJoin('node__field_event_country', 'co', 'n.nid = co.entity_id');
    $query->addField('co', 'field_event_country_value', 'field_event_country_value');

    $query->leftJoin('node__field_county', 'cy', 'n.nid = cy.entity_id');
    $query->addField('cy', 'field_county_value', 'field_county_value');

    $query->leftJoin('node__field_event_speakers_topic', 'st', 'n.nid = st.entity_id');
    $query->addField('st', 'field_event_speakers_topic_value', 'field_event_speakers_topic_value');

    $query->condition('n.type', 'event');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Revision ID'),
      'type' => $this->t('Content type'),
      'langcode' => $this->t('Language'),
      'status' => $this->t('Published status'),
      'uid' => $this->t('Author user ID'),
      'title' => $this->t('Title'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Changed timestamp'),
      'promote' => $this->t('Promoted'),
      'sticky' => $this->t('Sticky'),
      'body_value' => $this->t('Body value'),
      'body_summary' => $this->t('Body summary'),
      'body_format' => $this->t('Body format'),
      'field_event_date_value' => $this->t('Event start date'),
      'field_event_date_end_value' => $this->t('Event end date'),
      'field_event_organiser_value' => $this->t('Event organiser'),
      'field_event_reference_value' => $this->t('Event reference'),
      'field_event_type_target_id' => $this->t('Event type'),
      'field_logo_target_id' => $this->t('Logo file'),
      'field_logo_alt' => $this->t('Logo alt'),
      'field_logo_title' => $this->t('Logo title'),
      'field_event_email_value' => $this->t('Event email'),
      'field_teaser_media_target_id' => $this->t('Teaser media'),
      'field_teaser_text_value' => $this->t('Teaser text'),
      'field_event_website_uri' => $this->t('Website URL'),
      'field_event_website_title' => $this->t('Website title'),
      'field_event_venue_value' => $this->t('Venue'),
      'field_event_town_city_value' => $this->t('Town / City'),
      'field_event_postcode_value' => $this->t('Postcode'),
      'field_event_address_1_value' => $this->t('Address 1'),
      'field_event_address_2_value' => $this->t('Address 2'),
      'field_event_country_value' => $this->t('Country'),
      'field_county_value' => $this->t('County'),
      'field_event_speakers_topic_value' => $this->t('Speakers and topic'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

}
