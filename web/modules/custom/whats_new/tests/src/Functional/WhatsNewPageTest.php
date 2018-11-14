<?php

namespace Drupal\Tests\whats_new\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test basic functionality of the What's New module.
 *
 * @group whats_new
 */
class WhatsNewPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'whats_new',
  ];

  /**
   * Admin user account.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Make sure to complete the normal setup steps first.
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['access whats_new page']);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Check the whats-new page works.
   */
  public function testTheWhatsNewPageWorks() {
    // Load the whats-new page.
    $this->drupalGet('admin/whats-new');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the whats-new page contains the title text.
    $this->assertText(t("What's New"));
  }

}
