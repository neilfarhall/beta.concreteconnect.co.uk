<?php

namespace Drupal\Tests\taxonomy_delete\Functional;

use Drupal\Tests\taxonomy\Functional\TaxonomyTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Class TaxonomyDeleteTest. The base class for testing the drush command.
 */
class TaxonomyDeleteTest extends TaxonomyTestBase {

  use DrushTestTrait;

  /**
   * The default count of taxonomy terms to create.
   */
  const TAXONOMY_TERM_COUNT = 5;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['taxonomy', 'taxonomy_delete'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test taxonomy delete drush command.
   */
  public function testDrushCommand() {
    // Create a new vocabulary.
    $vocabulary = $this->createVocabulary();

    // Create some taxonomy terms.
    for ($i = 0; $i < self::TAXONOMY_TERM_COUNT; $i++) {
      $this->createTerm($vocabulary);
    }

    $taxonomy_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    $query = $taxonomy_storage->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('vid', $vocabulary->id());
    $this->assertEquals(self::TAXONOMY_TERM_COUNT, $query->count()->execute());

    // Execute drush command to delete all terms.
    $this->drush('tdel', [$vocabulary->id()]);

    $query = $taxonomy_storage->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('vid', $vocabulary->id());
    $this->assertEquals(0, $query->count()->execute());
  }

}
