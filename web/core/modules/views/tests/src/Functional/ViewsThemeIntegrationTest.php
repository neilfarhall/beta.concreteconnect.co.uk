<?php

namespace Drupal\Tests\views\Functional;

/**
 * Tests the Views theme integration.
 *
 * We test against test_basetheme and test_subtheme provided by theme_test
 *
 * @group views
 */
class ViewsThemeIntegrationTest extends ViewTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_page_display'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';


  /**
   * {@inheritdoc}
   *
   * We need theme_test for testing against test_basetheme and test_subtheme.
   *
   * @var array
   *
   * {@inheritdoc}
   */
  protected static $modules = ['views', 'theme_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['views_test_config']): void {
    parent::setUp($import_test_views, $modules);

    $this->enableViewsTestModule();
  }

  /**
   * Tests pre_render and post_render hooks in a theme and sub-theme.
   */
  public function testThemedViewPage() {

    \Drupal::service('theme_installer')->install(['test_basetheme', 'test_subtheme']);

    // Make base theme default then test for hook invocations.
    $this->config('system.theme')
      ->set('default', 'test_basetheme')
      ->save();
    $this->assertEquals('test_basetheme', $this->config('system.theme')->get('default'));

    // Make sure a views rendered page is touched.
    $this->drupalGet('test_page_display_200');

    $this->assertSession()->responseContains("test_basetheme_views_pre_render");
    $this->assertSession()->responseContains("test_basetheme_views_post_render");

    // Make sub theme default to test for hook invocation
    // from both sub and base theme.
    $this->config('system.theme')
      ->set('default', 'test_subtheme')
      ->save();
    $this->assertEquals('test_subtheme', $this->config('system.theme')->get('default'));

    // Make sure a views rendered page is touched.
    $this->drupalGet('test_page_display_200');

    $this->assertSession()->responseContains("test_subtheme_views_pre_render");
    $this->assertSession()->responseContains("test_subtheme_views_post_render");

    $this->assertSession()->responseContains("test_basetheme_views_pre_render");
    $this->assertSession()->responseContains("test_basetheme_views_post_render");

    // Verify that the views group title is added.
    $this->assertSession()->responseContains('<em class="placeholder">' . count($this->dataSet()) . '</em> items found.');
  }

  /**
   * Tests the views theme suggestions in debug mode.
   */
  public function testThemeSuggestionsInDebug() {
    $parameters = $this->container->getParameter('twig.config');
    $parameters['debug'] = TRUE;
    $this->setContainerParameter('twig.config', $parameters);
    $this->rebuildContainer();
    $this->resetAll();

    $build = [
      '#type' => 'view',
      '#name' => 'test_page_display',
      '#display_id' => 'default',
      '#arguments' => [],
    ];

    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');

    $output = $renderer->renderRoot($build);
    $expected = '   * views-view--test-page-display--default.html.twig' . PHP_EOL
      . '   * views-view--default.html.twig' . PHP_EOL
      . '   * views-view--test-page-display.html.twig' . PHP_EOL
      . '   x views-view.html.twig' . PHP_EOL;
    $this->assertTrue(strpos($output, $expected) !== FALSE);
  }

}
