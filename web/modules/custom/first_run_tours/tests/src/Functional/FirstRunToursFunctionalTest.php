<?php

namespace Drupal\Tests\first_run_tours\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * First Run Tours Functional tests.
 *
 */
class FirstRunToursFunctionalTest extends BrowserTestBase {

  public static $modules = ['system', 'node', 'tour', 'tour_ui', 'first_run_tours'];

  /**
   * Admin user account.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Machine name.
   */
  protected $machine_name;

  /**
   * @inheritdoc
   */
  protected function setUp() {
    $this->strictConfigSchema = FALSE;
    parent::setUp();

    $this->machine_name = 'article';

    $this->adminUser = $this->drupalCreateUser([
      'administer content types',
      'administer tour',
      'access content'
    ], NULL, TRUE);
    $this->drupalLogin($this->adminUser);

    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => $this->machine_name,
        'name' => 'Smarticle',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testFirstRunForm() {
    $path = 'admin/structure/types/manage/' . $this->machine_name . '/tours';
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tours Form');

    $edit = [
      'tour_enabled' => TRUE,
    ];

    $this->drupalPostForm($path, $edit, 'Save configuration', [],'tours-form');

    $this->drupalGet('admin/config/user-interface/tour');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Tours');
    $this->assertSession()->pageTextContains('node-add-' . $this->machine_name);
  }

}
