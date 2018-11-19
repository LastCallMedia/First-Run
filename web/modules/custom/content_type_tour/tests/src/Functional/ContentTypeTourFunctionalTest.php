<?php

namespace Drupal\Tests\content_type_tour\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Content Type Tour Functional tests.
 */
class ContentTypeTourFunctionalTest extends BrowserTestBase {

  public static $modules = [
    'system',
    'node',
    'tour',
    'tour_ui',
    'content_type_tour',
  ];

  /**
   * Admin user account.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Machine name.
   *
   * @var string
   */
  protected $machineName;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->strictConfigSchema = FALSE;
    parent::setUp();

    $this->machineName = 'article';

    $this->adminUser = $this->drupalCreateUser([
      'administer content types',
      'administer tour',
      'access content',
    ], NULL, TRUE);
    $this->drupalLogin($this->adminUser);

    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => $this->machineName,
        'name' => 'Smarticle',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * TestContentTypeTourForm.
   */
  public function testContentTypeTourForm() {
    $path = 'admin/structure/types/manage/' . $this->machineName . '/tour';
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tour Form');

    $edit = [
      'tour_enabled' => TRUE,
    ];

    $this->drupalPostForm($path, $edit, 'Save configuration', [], 'content-type-tour-form');

    $this->drupalGet('admin/config/user-interface/tour');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tour');
    $this->assertSession()->pageTextContains('node-add-' . $this->machineName);
  }

}
