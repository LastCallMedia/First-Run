<?php

namespace Drupal\Tests\first_run_tours\Unit;

use Drupal\first_run_tours\Form\ToursForm;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Simple test to ensure that asserts pass.
 *
 * @group phpunit_example
 */
class FirstRunToursUnitTest extends UnitTestCase {

  protected $tours_form;
  protected $container;
  protected $entity_manager;
  protected $entity_type_manager;
  protected $entity_field_manager;

  /**
   * Before a test method is run, setUp() is invoked.
   * Create new object.
   */
  public function setUp() {
    parent::setUp();
    $this->container = $this->prophesize(ConfigFactoryInterface::class);
    $this->entity_manager = $this->prophesize(EntityManager::class);
    $this->entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entity_field_manager = $this->prophesize(EntityFieldManagerInterface::class);

    $this->tours_form = new ToursForm(
      $this->container->reveal(),
      $this->entity_manager->reveal(),
      $this->entity_type_manager->reveal(),
      $this->entity_field_manager->reveal()
    );
  }

  /**
   * testHyphenate.
   */
  public function testHyphenate() {
    $actual = 'a_test_string';
    $expected = 'a-test-string';

    $result = $this->tours_form->hyphenate($actual);
    $this->assertEquals($expected, $result);
  }

  /**
   * testCreateWelcomeTip.
   */
  public function testCreateWelcomeTip() {
    $result = $this->tours_form->createWelcomeTip('name', 'machine_name');
    $this->assertArrayHasKey('machine-name-welcome', $result);
  }

}
