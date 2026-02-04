<?php

namespace Drupal\Tests\views_rss\Functional;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the rss fields style display plugin.
 *
 * @group views_rss
 */
class DisplayFeedTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'image',
    'node',
    'taxonomy',
    'views',
    'views_rss',
    'views_rss_core',
    'views_rss_test_config',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test start timestamp used for time comparisons.
   *
   * @var int
   */
  protected $testStartTime;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->testStartTime = \Drupal::time()->getCurrentTime();

    // Create a demo content type called "page".
    $this->drupalCreateContentType(['type' => 'page']);
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_image',
      'bundle' => 'page',
    ])->save();

    // Create a file object to use in nodes, etc.
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = File::create([
      'uri' => 'public://example.jpg',
    ]);
    $this->image->save();
  }

  /**
   * Tests the rendered output.
   *
   * @todo Rework so that it starts with zero items and then as each node is
   * added the feed changes.
   */
  public function testFeedOutput() {
    // Create a demo node of type "page" for use in the feed.
    $node_title = 'This "cool" & "neat" article\'s title';
    $node = $this->drupalCreateNode([
      'title' => $node_title,
      'body' => [
        0 => [
          'value' => 'A paragraph',
          'format' => filter_default_format(),
        ],
      ],
      'field_image' => $this->image,
    ]);
    $node_link = $node->toUrl()->setAbsolute()->toString();

    // Create a demo node of type "page" for use in the feed.
    $node2 = $this->drupalCreateNode();
    $node2->setCreatedTime(strtotime(('-1 day')))->save();

    $this->drupalGet('views-rss.xml');
    $this->assertSession()->responseHeaderEquals('Content-Type', 'application/rss+xml; charset=utf-8');
    $this->assertEquals($node_title, $this->getSession()->getDriver()->getText('//item/title'));
    $this->assertEquals($node_link, $this->getSession()->getDriver()->getText('//item/link'));
    $this->assertEquals($node_link, $this->getSession()->getDriver()->getText('//item/comments'));
    // Verify HTML is properly escaped in the description field.
    $this->assertSession()->responseContains('&lt;p&gt;A paragraph&lt;/p&gt;');
    $selector = sprintf(
      'enclosure[@url="%s"][@length="%s"][@type="%s"]',
      \Drupal::service('file_url_generator')->generateAbsoluteString('public://example.jpg'),
      $this->image->getSize(),
      $this->image->getMimeType()
    );
    $this->assertNotNull($this->getSession()->getDriver()->find($selector));

    // Verify query parameters are included in the output.
    $this->drupalGet('views-rss.xml', ['query' => ['field_tags_target_id' => 1]]);
    $this->assertStringContainsString('views-rss.xml?field_tags_target_id=1', $this->getSession()->getDriver()->getText('//item/source/@url'));

    // Verify the channel pubDate matches the highest node pubDate.
    $this->assertEquals(date('r', $node->getCreatedTime()), $this->getSession()->getDriver()->getText('//channel/pubDate'));
    $this->assertGreaterThanOrEqual($this->testStartTime, strtotime($this->getSession()->getDriver()->getText('//channel/lastBuildDate')));
  }

  /**
   * Test the channel options.
   *
   * @todo Consider moving this into views_rss_core.
   */
  public function testChannelOutput() {
    $front_page = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();

    // Create a demo node of type "page" for use in the feed.
    $node_title = 'This "cool" & "neat" article\'s title';
    $node = $this->drupalCreateNode([
      'title' => $node_title,
      'body' => [
        0 => [
          'value' => 'A paragraph',
          'format' => filter_default_format(),
        ],
      ],
      'field_image' => $this->image,
    ]);
    // In case that you want to use node link declare variable below $node_link.
    $node->toUrl()->setAbsolute()->toString();
    // Verify the channel has one item of each possible tag.
    $this->drupalGet('views-rss.xml');
    $this->assertSession()->statusCodeEquals(200);
    $driver = $this->getSession()->getDriver();
    // dump($this->getSession()->getDriver()->getContent());
    //
    // Verify the basic structure.
    // In order to select the root element you have to specify "any element at
    // the root", which grabs the first "rss" element.
    $this->assertEquals(1, count($driver->find('//rss')));
    $this->assertEquals(1, count($driver->find('//rss/channel')));
    $this->assertEquals(1, count($driver->find('//rss/channel/item')));

    // @todo The title is empty by default, but is present.
    $this->assertEquals(1, count($driver->find('//rss/channel/title')));

    // Expected values from the included view before anything is modified.
    $this->assertEquals(1, count($driver->find('//rss/channel/description')));
    $this->assertEquals(1, count($driver->find('//rss/channel/language')));
    $this->assertEquals(1, count($driver->find('//rss/channel/category')));
    $this->assertEquals(1, count($driver->find('//rss/channel/image')));
    $this->assertEquals(1, count($driver->find('//rss/channel/copyright')));
    $this->assertEquals(1, count($driver->find('//rss/channel/managingEditor')));
    $this->assertEquals(1, count($driver->find('//rss/channel/webMaster')));
    $this->assertEquals(1, count($driver->find('//rss/channel/generator')));
    $this->assertEquals(1, count($driver->find('//rss/channel/docs')));
    $this->assertEquals(1, count($driver->find('//rss/channel/cloud')));
    $this->assertEquals(1, count($driver->find('//rss/channel/ttl')));
    // @todo Properly handle these.
    $this->assertEquals(0, count($driver->find('//rss/channel/skipDays')));
    $this->assertEquals(0, count($driver->find('//rss/channel/skipHours')));

    // Check the default output from the included view.
    $this->assertEquals('Test feed', $driver->getText('//rss/channel/title'));
    $this->assertEquals('Test description', $driver->getText('//rss/channel/description'));
    $this->assertEquals($front_page, $driver->getText('//rss/channel/link'));
    $this->assertEquals('en', $driver->getText('//rss/channel/language'));
    $this->assertEquals('Test category', $driver->getText('//rss/channel/category'));
    $this->assertEquals('Test copyright', $driver->getText('//rss/channel/copyright'));
    $this->assertEquals('Test managingEditor', $driver->getText('//rss/channel/managingEditor'));
    $this->assertEquals('Test webMaster', $driver->getText('//rss/channel/webMaster'));
    $this->assertEquals('Test generator', $driver->getText('//rss/channel/generator'));
    $this->assertEquals('https://www.example.com/something.html', $driver->getText('//rss/channel/docs'));
    // @todo How about this one?
    // <cloud domain="www.example.com" path="/viewsrsscloud.html"
    // protocol="https"/>\n
    // $this->assertEquals('', $driver->getText('//rss/channel/cloud'));
    $this->assertEquals(600, $driver->getText('//rss/channel/ttl'));

    // Test the channel image URL. This also confirms the absolute URL handling.
    // $site_image_url = $this->getAbsoluteUrl('misc/druplicon.png');
    // $this->assertEquals($site_image_url, $driver->getText('//rss/channel/image/url'));
    $this->assertEquals('https://www.drupal.org/misc/druplicon.png', $driver->getText('//rss/channel/image/url'));
    $this->assertEquals('Test feed', $driver->getText('//rss/channel/image/title'));
    $this->assertEquals($front_page, $driver->getText('//rss/channel/image/link'));
    // @todo Work out a better approach for this.
    // $this->assertEquals('', $driver->getText('//rss/channel/image/width'));
    // $this->assertEquals('', $driver->getText('//rss/channel/image/height'));
    //
    // Change the channel description.
    $description = 'Test channel description';
    $config = $this->config('views.view.test_views_rss_feed');
    $config->set('display.feed_1.display_options.style.options.channel.core.views_rss_core.description', $description);
    $config->save();
    drupal_flush_all_caches();

    // Verify the channel description was changed as expected.
    $this->drupalGet('views-rss.xml');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertEquals($description, $this->getSession()->getDriver()->getText('//rss/channel/description'));

    // Verify that the channel description uses the site slogan when the
    // description is empty.
    $slogan = 'Our awesome site!';
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('slogan', $slogan)
      ->save(TRUE);
    $config->set('display.feed_1.display_options.style.options.channel.core.views_rss_core.description', '');
    $config->save();
    drupal_flush_all_caches();
    $this->drupalGet('views-rss.xml');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertEquals($slogan, $this->getSession()->getDriver()->getText('//rss/channel/description'));
  }

}
