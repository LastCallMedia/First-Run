<?php

namespace Drupal\Tests\first_run_tours\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * First Run Tours Functional tests.
 */
class FirstRunToursFunctionalTest extends BrowserTestBase {

  public static $modules = [
    'system',
    'node',
    'tour',
    'tour_ui',
    'first_run_tours',
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
   * TestFirstRunForm.
   */
  public function testFirstRunForm() {
    $path = 'admin/structure/types/manage/' . $this->machineName . '/tours';
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tours Form');

    $edit = [
      'tour_enabled' => TRUE,
    ];

    $this->drupalPostForm($path, $edit, 'Save configuration', [], 'tours-form');

    $this->drupalGet('admin/config/user-interface/tour');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tours');
    $this->assertSession()->pageTextContains('node-add-' . $this->machineName);
  }

}
