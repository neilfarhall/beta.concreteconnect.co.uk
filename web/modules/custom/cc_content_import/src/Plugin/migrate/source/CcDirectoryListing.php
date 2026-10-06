<?php

namespace Drupal\cc_content_import\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for directory listings.
 *
 * The base query deliberately contains no field-table joins. Directory
 * listings have several multi-value fields, and joining them in the base query
 * creates a Cartesian product. Field values are loaded per node in prepareRow()
 * instead, keeping the source count to one row per listing.
 *
 * @MigrateSource(
 *   id = "cc_directory_listing"
 * )
 */
class CcDirectoryListing extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('node_field_data', 'n')
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
      ])
      ->condition('n.type', 'directory_listing')
      ->condition('n.default_langcode', 1);
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
      'field_address_country_code' => $this->t('Address country code'),
      'field_address_administrative_area' => $this->t('Address administrative area'),
      'field_address_locality' => $this->t('Address locality'),
      'field_address_dependent_locality' => $this->t('Address dependent locality'),
      'field_address_postal_code' => $this->t('Address postal code'),
      'field_address_sorting_code' => $this->t('Address sorting code'),
      'field_address_address_line1' => $this->t('Address line 1'),
      'field_address_address_line2' => $this->t('Address line 2'),
      'field_address_organization' => $this->t('Address organisation'),
      'field_address_given_name' => $this->t('Address given name'),
      'field_address_additional_name' => $this->t('Address additional name'),
      'field_address_family_name' => $this->t('Address family name'),
      'field_directory_brands_value' => $this->t('Brands'),
      'field_company_slogan_value' => $this->t('Company slogan'),
      'field_directory_page_type' => $this->t('Directory page type terms'),
      'field_directory_products' => $this->t('Directory product terms'),
      'field_display_email' => $this->t('Display email addresses'),
      'field_downloads_tab_value' => $this->t('Downloads tab flag'),
      'field_directory_contact_email' => $this->t('Contact email addresses'),
      'field_file_attachments' => $this->t('File attachments'),
      'field_home_tab_value' => $this->t('Home tab flag'),
      'field_directory_key_value' => $this->t('Directory key'),
      'field_directory_logo_target_id' => $this->t('Logo file ID'),
      'field_media_gallery' => $this->t('Media gallery references'),
      'field_media_gallery_tab_value' => $this->t('Media gallery tab flag'),
      'field_news_tab_value' => $this->t('News tab flag'),
      'field_directory_opco_key_value' => $this->t('Operating company key'),
      'field_directory_organisations_value' => $this->t('Organisations'),
      'field_directory_contact_phone' => $this->t('Contact phone numbers'),
      'field_directory_address_postcode_value' => $this->t('Postcode'),
      'field_directory_product_type' => $this->t('Product type terms'),
      'field_teaser_media_target_id' => $this->t('Profile media ID'),
      'field_directory_regional_offices_value' => $this->t('Regional offices'),
      'field_related_articles' => $this->t('Related article references'),
      'field_social_media' => $this->t('Social media links'),
      'field_directory_organisation_sec' => $this->t('Trade body sector terms'),
      'field_directory_web_address' => $this->t('Web addresses'),
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

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = (int) $row->getSourceProperty('nid');
    $langcode = (string) $row->getSourceProperty('langcode');

    $single_value_fields = [
      'node__body' => [
        'body_value' => 'body_value',
        'body_summary' => 'body_summary',
        'body_format' => 'body_format',
      ],
      'node__field_address' => [
        'field_address_country_code' => 'field_address_country_code',
        'field_address_administrative_area' => 'field_address_administrative_area',
        'field_address_locality' => 'field_address_locality',
        'field_address_dependent_locality' => 'field_address_dependent_locality',
        'field_address_postal_code' => 'field_address_postal_code',
        'field_address_sorting_code' => 'field_address_sorting_code',
        'field_address_address_line1' => 'field_address_address_line1',
        'field_address_address_line2' => 'field_address_address_line2',
        'field_address_organization' => 'field_address_organization',
        'field_address_given_name' => 'field_address_given_name',
        'field_address_additional_name' => 'field_address_additional_name',
        'field_address_family_name' => 'field_address_family_name',
      ],
      'node__field_directory_brands' => [
        'field_directory_brands_value' => 'field_directory_brands_value',
      ],
      'node__field_company_slogan' => [
        'field_company_slogan_value' => 'field_company_slogan_value',
      ],
      'node__field_downloads_tab' => [
        'field_downloads_tab_value' => 'field_downloads_tab_value',
      ],
      'node__field_home_tab' => [
        'field_home_tab_value' => 'field_home_tab_value',
      ],
      'node__field_directory_key' => [
        'field_directory_key_value' => 'field_directory_key_value',
      ],
      'node__field_directory_logo' => [
        'field_directory_logo_target_id' => 'field_directory_logo_target_id',
        'field_directory_logo_alt' => 'field_directory_logo_alt',
        'field_directory_logo_title' => 'field_directory_logo_title',
        'field_directory_logo_width' => 'field_directory_logo_width',
        'field_directory_logo_height' => 'field_directory_logo_height',
      ],
      'node__field_media_gallery_tab' => [
        'field_media_gallery_tab_value' => 'field_media_gallery_tab_value',
      ],
      'node__field_news_tab' => [
        'field_news_tab_value' => 'field_news_tab_value',
      ],
      'node__field_directory_opco_key' => [
        'field_directory_opco_key_value' => 'field_directory_opco_key_value',
      ],
      'node__field_directory_organisations' => [
        'field_directory_organisations_value' => 'field_directory_organisations_value',
      ],
      'node__field_directory_address_postcode' => [
        'field_directory_address_postcode_value' => 'field_directory_address_postcode_value',
      ],
      'node__field_teaser_media' => [
        'field_teaser_media_target_id' => 'field_teaser_media_target_id',
      ],
      'node__field_directory_regional_offices' => [
        'field_directory_regional_offices_value' => 'field_directory_regional_offices_value',
      ],
    ];

    foreach ($single_value_fields as $table => $columns) {
      foreach ($this->loadFieldItem($table, $columns, $nid, $langcode) as $property => $value) {
        $row->setSourceProperty($property, $value);
      }
    }

    $row->setSourceProperty('field_directory_page_type', $this->loadTaxonomyItems(
      'node__field_directory_page_type',
      'field_directory_page_type_target_id',
      $nid,
      $langcode,
    ));
    $row->setSourceProperty('field_directory_products', $this->loadTaxonomyItems(
      'node__field_directory_products',
      'field_directory_products_target_id',
      $nid,
      $langcode,
    ));
    $row->setSourceProperty('field_directory_product_type', $this->loadTaxonomyItems(
      'node__field_directory_product_type',
      'field_directory_product_type_target_id',
      $nid,
      $langcode,
    ));
    $row->setSourceProperty('field_directory_organisation_sec', $this->loadTaxonomyItems(
      'node__field_directory_organisation_sec',
      'field_directory_organisation_sec_target_id',
      $nid,
      $langcode,
    ));

    $multi_value_fields = [
      'field_display_email' => [
        'node__field_display_email',
        ['value' => 'field_display_email_value'],
      ],
      'field_directory_contact_email' => [
        'node__field_directory_contact_email',
        ['value' => 'field_directory_contact_email_value'],
      ],
      'field_file_attachments' => [
        'node__field_file_attachments',
        [
          'target_id' => 'field_file_attachments_target_id',
          'display' => 'field_file_attachments_display',
          'description' => 'field_file_attachments_description',
        ],
      ],
      'field_media_gallery' => [
        'node__field_media_gallery',
        ['target_id' => 'field_media_gallery_target_id'],
      ],
      'field_directory_contact_phone' => [
        'node__field_directory_contact_phone',
        ['value' => 'field_directory_contact_phone_value'],
      ],
      'field_related_articles' => [
        'node__field_related_articles',
        ['target_id' => 'field_related_articles_target_id'],
      ],
      'field_social_media' => [
        'node__field_social_media',
        [
          'social' => 'field_social_media_social',
          'link' => 'field_social_media_link',
        ],
      ],
      'field_directory_web_address' => [
        'node__field_directory_web_address',
        [
          'uri' => 'field_directory_web_address_uri',
          'title' => 'field_directory_web_address_title',
        ],
      ],
    ];

    foreach ($multi_value_fields as $property => [$table, $columns]) {
      $row->setSourceProperty(
        $property,
        $this->loadFieldItems($table, $columns, $nid, $langcode),
      );
    }

    return parent::prepareRow($row);
  }

  /**
   * Loads the first item for a single-value node field.
   */
  private function loadFieldItem(string $table, array $columns, int $nid, string $langcode): array {
    return $this->loadFieldItems($table, $columns, $nid, $langcode)[0] ?? [];
  }

  /**
   * Loads all items for a node field while preserving their delta order.
   */
  private function loadFieldItems(string $table, array $columns, int $nid, string $langcode): array {
    $query = $this->select($table, 'f')
      ->condition('f.bundle', 'directory_listing')
      ->condition('f.entity_id', $nid)
      ->condition('f.deleted', 0)
      ->condition('f.langcode', $langcode)
      ->orderBy('f.delta', 'ASC');

    foreach ($columns as $property => $column) {
      $query->addField('f', $column, $property);
    }

    return array_map(
      static fn($record): array => (array) $record,
      $query->execute()->fetchAll(),
    );
  }

  /**
   * Loads taxonomy references with names for destination entity lookups.
   */
  private function loadTaxonomyItems(string $table, string $target_column, int $nid, string $langcode): array {
    $items = $this->loadFieldItems(
      $table,
      ['source_target_id' => $target_column],
      $nid,
      $langcode,
    );

    foreach ($items as &$item) {
      $term = $this->select('taxonomy_term_field_data', 't')
        ->fields('t', ['name'])
        ->condition('t.tid', $item['source_target_id'])
        ->condition('t.default_langcode', 1)
        ->execute()
        ->fetchAssoc();

      $item['name'] = $term['name'] ?? NULL;
    }
    unset($item);

    return $items;
  }

}
