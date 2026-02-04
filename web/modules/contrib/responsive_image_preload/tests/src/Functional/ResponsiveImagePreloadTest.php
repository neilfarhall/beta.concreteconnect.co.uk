<?php

namespace Drupal\Tests\responsive_image_preload\Functional;

/**
 * Contains test cases for complex responsive image style configurations.
 *
 * @group responsive_image_preload
 */
class ResponsiveImagePreloadTest extends ResponsiveImagePreloadBrowserTestBase {

  /**
   * Tests that correct preloads are generated in a variety of configurations.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   This should never happen under normal circumstances.
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *   This should never happen under normal circumstances.
   */
  public function testResponsiveImagePreload() {
    $this->drupalLogin($this->createUser([], NULL, TRUE));

    // Test case with no expected preloads.
    $this->setFormatterSettings('test1', FALSE);
    $this->drupalGet($this->entity->toUrl());

    $page = $this->getSession()->getPage();
    $preloads = $page->findAll('css', 'link[rel="preload"]');
    static::assertEmpty($preloads);

    // Test case for an element with a single image style per breakpoint.
    $this->setFormatterSettings('test1', TRUE);
    $this->drupalGet($this->entity->toUrl());
    $this->assertPreloadsPresent([
      [
        'image_style' => 'xl',
        'media' => '(min-width: 1280px)',
      ],
      [
        'image_style' => 'l',
        'media' => '(min-width: 1024px) and (max-width: 1279px)',
      ],
      [
        'image_style' => 'm',
        'media' => '(min-width: 950px) and (max-width: 1023px)',
      ],
      [
        'image_style' => 's',
        'media' => '(min-width: 800px) and (max-width: 949px)',
      ],
      [
        'image_style' => 'xs',
        'media' => '(min-width: 550px) and (max-width: 799px)',
      ],
      [
        'image_style' => 'xxs',
        'media' => '(min-width: 320px) and (max-width: 549px)',
      ],
      [
        'image_style' => 'default',
        'media' => '(min-width: 1px) and (max-width: 319px)',
      ],
    ]);

    // Test case for an element with multiple image sizes per breakpoint.
    $this->setFormatterSettings('test2', TRUE);
    $this->drupalGet($this->entity->toUrl());

    $this->assertPreloadsPresent([
      [
        'media' => '(min-width: 1px) and (max-width: 319px)',
        'imagesizes' => '100vw',
        'imagesrcset' => [
          'xxs' => '200w',
          'xs' => '300w',
          's' => '400w',
          'm' => '500w',
          'l' => '600w',
          'xl' => '700w',
        ],
      ],
    ]);
  }

}
